<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Helpers\AuditLogger;

class AntiBypassFilter
{
    /**
     * Regex patterns for off-platform communication and direct contact info.
     */
    private static array $patterns = [
        'email' => [
            // Standard emails and obfuscated (name at domain dot com)
            '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/i',
            '/[a-zA-Z0-9._%+-]+\s*(?:@|\[at\]|\(at\)|\bat\b)\s*[a-zA-Z0-9.-]+\s*(?:\.|\(dot\)|\[dot\]|\bdot\b)\s*[a-zA-Z]{2,}/i',
        ],
        'phone' => [
            // Standard Nigerian and international phone formats (+234, 080, 070, 081, 090, 091)
            '/(?:(?:\+?234)|0)[789][01]\d{8}/',
            // Spaced, dotted, or dashed numbers: e.g. 080 1234 5678, 080-123-4567, +234 80 1234 5678
            '/(?:\+?234|0)[-.\s]*[789][-.\s]*[01](?:[-.\s]*\d){8}/',
            // Any sequence of 8 to 15 digits (to catch raw international and local numbers)
            '/(?<!\d)(?:\+?\d{1,4}[-.\s]*)?(?:\(?\d{2,4}\)?[-.\s]*)?\d{3,4}[-.\s]*\d{3,4}(?!\d)/',
            // Spelled out phone digits: e.g. "zero eight zero", "0 8 0"
            '/(?:zero|one|two|three|four|five|six|seven|eight|nine)[-\s]+(?:zero|one|two|three|four|five|six|seven|eight|nine)[-\s]+(?:zero|one|two|three|four|five|six|seven|eight|nine)/i',
        ],
        'bank_account' => [
            // Nigerian 10-digit NUBAN numbers when accompanied by banking terms
            '/(?:\bacct|\baccount|\bnuban|\btransfer|\bpay into|\bdeposit|\bgtb|\bzenith|\baccess|\bkuda|\bopay|\bpalmpay|\bfirstbank|\buba|\bstanbic)\b[^\d]{0,25}\b\d{10}\b/i',
            '/\b\d{10}\b[^\d]{0,25}(?:\bacct|\baccount|\bbank|\bsavings|\bcurrent)/i',
        ],
        'messaging_handles' => [
            // WhatsApp links, telegram, social handles, external chat channels
            '/(?:wa\.me|api\.whatsapp\.com|whatsapp|chat\.whatsapp\.com)[^\s]*/i',
            '/(?:t\.me|telegram)[^\s]*/i',
            '/(?:call|reach|chat|contact|text|message)\s+(?:me|us|agent)\s+(?:on|at|via)\s+[^\s]+/i',
            '/(?:instagram|twitter|facebook|snapchat|linkedin|tiktok)\.com[^\s]*/i',
            '/(?:ig|handle|dm me at|snap)\s*[:@]\s*[a-zA-Z0-9_.-]+/i',
        ],
        'url' => [
            // Direct external URLs and domain names
            '/https?:\/\/[^\s]+/i',
            '/www\.[a-zA-Z0-9-]+\.[a-zA-Z]{2,}[^\s]*/i',
            '/\b[a-zA-Z0-9-]+\.(?:com|ng|com\.ng|org|net|co|io|biz|xyz|site|online)\b[^\s]*/i',
        ]
    ];

    /**
     * Check if a given string contains any bypass attempt.
     */
    public static function containsBypassAttempt(string $text): bool
    {
        $violations = self::detectViolations($text);
        return !empty($violations);
    }

    /**
     * Detect specific violation categories present in the text.
     *
     * @return array<string, array<string>>
     */
    public static function detectViolations(string $text): array
    {
        $violations = [];

        foreach (self::$patterns as $category => $categoryPatterns) {
            foreach ($categoryPatterns as $pattern) {
                if (preg_match_all($pattern, $text, $matches)) {
                    if (!empty($matches[0])) {
                        if (!isset($violations[$category])) {
                            $violations[$category] = [];
                        }
                        foreach ($matches[0] as $match) {
                            $cleanMatch = trim($match);
                            if (!in_array($cleanMatch, $violations[$category], true)) {
                                $violations[$category][] = $cleanMatch;
                            }
                        }
                    }
                }
            }
        }

        return $violations;
    }

    /**
     * Redact detected contact details and URLs from text.
     */
    public static function sanitize(string $text): string
    {
        $clean = $text;

        foreach (self::$patterns as $category => $categoryPatterns) {
            foreach ($categoryPatterns as $pattern) {
                $clean = preg_replace($pattern, '[PROTECTED - CONTACT EXCHANGED PROHIBITED]', $clean);
            }
        }

        return $clean;
    }

    /**
     * Validate message text and return a structured decision result.
     *
     * @param string $text
     * @param string $channel e.g. 'direct_message', 'enquiry', 'lead_form'
     * @param int|null $actorUserId
     * @return array{is_blocked: bool, reason: string|null, violations: array, sanitized_text: string}
     */
    public static function evaluate(string $text, string $channel = 'direct_message', ?int $actorUserId = null): array
    {
        $violations = self::detectViolations($text);

        if (empty($violations)) {
            return [
                'is_blocked' => false,
                'reason' => null,
                'violations' => [],
                'sanitized_text' => $text
            ];
        }

        // Compile descriptive violation categories
        $categories = array_keys($violations);
        $categoryLabels = array_map(function ($cat) {
            return match ($cat) {
                'email' => 'Email Address',
                'phone' => 'Phone / WhatsApp Number',
                'bank_account' => 'Bank Account Details',
                'messaging_handles' => 'External Messaging Handle / Social Link',
                'url' => 'External Web Link',
                default => 'Contact Information'
            };
        }, $categories);

        $reason = "Direct exchange of " . implode(', ', $categoryLabels) . " is strictly prohibited to protect buyers and sellers from fraud and bypass. All communication and transactions must be conducted securely through the Afriserva Transaction Desk.";

        // Log the attempted bypass in the audit ledger
        self::logAttempt($actorUserId ?? Auth::id(), $channel, $text, $violations);

        return [
            'is_blocked' => true,
            'reason' => $reason,
            'violations' => $violations,
            'sanitized_text' => self::sanitize($text)
        ];
    }

    /**
     * Log the unauthorized bypass attempt to audit_logs.
     */
    public static function logAttempt(?int $userId, string $channel, string $rawText, array $violations): void
    {
        try {
            AuditLogger::log('contact_bypass_attempt_blocked', 'communication', null, null, [
                'actor_user_id' => $userId,
                'channel' => $channel,
                'snippet' => substr($rawText, 0, 150),
                'violations' => array_keys($violations)
            ]);
        } catch (\Throwable $e) {
            // Non-blocking log catch
        }
    }
}
