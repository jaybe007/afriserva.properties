<?php

namespace App\Models;

use App\Core\Database;

class Enquiry
{
    /**
     * Detect spam content in enquiry submission.
     */
    public static function checkSpam(string $message, string $name, string $email): ?string
    {
        $spamKeywords = ['crypto', 'bitcoin', 'viagra', 'casino', 'betting', 'forex trading', 'telegram bot', 'whatsapp blast', 'quick loan', 'earn $'];
        $haystack = strtolower($message . ' ' . $name . ' ' . $email);

        foreach ($spamKeywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return "Detected spam keyword: {$keyword}";
            }
        }

        // Detect excessive URLs (> 2 URLs)
        if (preg_match_all('/https?:\/\//i', $message, $matches) > 2) {
            return "Too many promotional hyperlinks in body";
        }

        return null;
    }

    /**
     * Enforce rate limiting: maximum 5 enquiries per 15 minutes from same email or IP.
     */
    public static function isRateLimited(string $email, string $ip): bool
    {
        $sql = "SELECT COUNT(*) AS total 
                FROM enquiries 
                WHERE (email = ? OR phone = ?) 
                  AND created_at >= NOW() - INTERVAL 15 MINUTE";

        $res = Database::fetch($sql, [strtolower(trim($email)), $ip]);
        return ((int)($res['total'] ?? 0)) >= 5;
    }

    public static function create(array $data): int
    {
        $spamReason = self::checkSpam($data['message'] ?? '', $data['name'] ?? '', $data['email'] ?? '');
        $isSpam = $spamReason !== null ? 1 : 0;
        $status = $isSpam ? 'spam' : 'new';

        $sql = "INSERT INTO enquiries (property_id, user_id, agent_id, name, email, phone, message, enquiry_type, status, is_spam, spam_reason, contact_unlocked, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())";

        Database::execute($sql, [
            (int)$data['property_id'],
            !empty($data['user_id']) ? (int)$data['user_id'] : null,
            (int)$data['agent_id'],
            trim($data['name']),
            trim(strtolower($data['email'])),
            trim($data['phone']),
            trim($data['message']),
            $data['enquiry_type'] ?? 'inspection',
            $status,
            $isSpam,
            $spamReason
        ]);

        return (int)Database::lastInsertId();
    }

    public static function getById(int $id): ?array
    {
        $sql = "SELECT e.*, p.title AS property_title, p.slug AS property_slug, p.price, p.reference_code,
                       u.full_name AS registered_client_name
                FROM enquiries e
                JOIN properties p ON p.id = e.property_id
                LEFT JOIN users u ON u.id = e.user_id
                WHERE e.id = ?
                LIMIT 1";

        $row = Database::fetch($sql, [$id]);
        if ($row) {
            return self::applyPrivacyMask($row);
        }
        return null;
    }

    public static function getByAgent(int $agentId, int $limit = 50): array
    {
        $sql = "SELECT e.*, p.title AS property_title, p.slug AS property_slug, p.price, p.reference_code
                FROM enquiries e
                JOIN properties p ON p.id = e.property_id
                WHERE e.agent_id = ?
                ORDER BY e.created_at DESC
                LIMIT {$limit}";

        $rows = Database::query($sql, [$agentId]);
        return array_map(function($row) {
            // Anti-Bypass Security: Always enforce masked buyer email and phone for agents/sellers regardless of unlocked flag
            $parts = explode('@', $row['email'] ?? '');
            $maskedEmail = (count($parts) === 2) 
                ? substr($parts[0], 0, 1) . '***@' . $parts[1] 
                : '***@client.afriserva.com';
            $phone = $row['phone'] ?? '';
            $maskedPhone = (strlen($phone) > 6) 
                ? substr($phone, 0, 4) . ' *** ' . substr($phone, -3) 
                : '***-***-****';

            $row['masked_email'] = $maskedEmail;
            $row['masked_phone'] = $maskedPhone;
            $row['email'] = $maskedEmail;
            $row['phone'] = $maskedPhone;
            unset($row['raw_email'], $row['raw_phone']);
            return $row;
        }, $rows);
    }

    public static function getAll(int $limit = 50): array
    {
        $sql = "SELECT e.*, p.title AS property_title, p.slug AS property_slug, p.price, p.reference_code,
                       ag.full_name AS agent_name
                FROM enquiries e
                JOIN properties p ON p.id = e.property_id
                JOIN users ag ON ag.id = e.agent_id
                ORDER BY e.created_at DESC
                LIMIT {$limit}";

        $rows = Database::query($sql);
        return array_map([self::class, 'applyPrivacyMask'], $rows);
    }

    public static function unlockContact(int $id, int $agentId): bool
    {
        return Database::execute(
            "UPDATE enquiries SET contact_unlocked = 1, unlocked_at = NOW() WHERE id = ? AND agent_id = ?",
            [$id, $agentId]
        ) > 0;
    }

    public static function updateStatus(int $id, string $status, ?int $agentId = null): bool
    {
        $validStatuses = ['new', 'contacted', 'qualified', 'viewing_scheduled', 'converted', 'closed', 'spam'];
        if (!in_array($status, $validStatuses, true)) {
            return false;
        }

        // When status is changed to contacted/qualified/converted, automatically unlock contact details
        $unlockSql = in_array($status, ['contacted', 'qualified', 'viewing_scheduled', 'converted'], true) 
            ? ", contact_unlocked = 1, unlocked_at = COALESCE(unlocked_at, NOW())" 
            : "";

        // Track response time if this is first contact
        $responseTimeSql = "";
        if (in_array($status, ['contacted', 'qualified', 'viewing_scheduled'], true)) {
            $responseTimeSql = ", first_responded_at = COALESCE(first_responded_at, NOW()), response_time_seconds = COALESCE(response_time_seconds, TIMESTAMPDIFF(SECOND, created_at, NOW()))";
        }

        $params = [$status, $id];
        $agentCheck = "";
        if ($agentId !== null) {
            $agentCheck = " AND agent_id = ?";
            $params[] = $agentId;
        }

        return Database::execute(
            "UPDATE enquiries SET status = ?, updated_at = NOW() {$unlockSql} {$responseTimeSql} WHERE id = ? {$agentCheck}",
            $params
        ) > 0;
    }

    public static function getCountsByStatus(int $agentId): array
    {
        $rows = Database::query(
            "SELECT status, COUNT(*) AS count 
             FROM enquiries 
             WHERE agent_id = ? 
             GROUP BY status",
            [$agentId]
        );

        $counts = [
            'total' => 0,
            'new' => 0,
            'contacted' => 0,
            'qualified' => 0,
            'viewing_scheduled' => 0,
            'converted' => 0,
            'closed' => 0,
            'spam' => 0
        ];

        foreach ($rows as $row) {
            $counts[$row['status']] = (int)$row['count'];
            $counts['total'] += (int)$row['count'];
        }

        return $counts;
    }

    /**
     * Agent response-time analytics.
     */
    public static function getResponseAnalytics(int $agentId): array
    {
        $row = Database::fetch(
            "SELECT 
                COUNT(response_time_seconds) AS responded_count,
                AVG(response_time_seconds) AS avg_seconds,
                MIN(response_time_seconds) AS min_seconds
             FROM enquiries 
             WHERE agent_id = ? AND first_responded_at IS NOT NULL",
            [$agentId]
        );

        $avgSeconds = (int)($row['avg_seconds'] ?? 0);
        $hours = floor($avgSeconds / 3600);
        $minutes = floor(($avgSeconds % 3600) / 60);

        return [
            'responded_count' => (int)($row['responded_count'] ?? 0),
            'avg_seconds' => $avgSeconds,
            'avg_formatted' => $avgSeconds > 0 ? ($hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes} mins") : 'Under 1 hour',
            'fastest_formatted' => !empty($row['min_seconds']) ? floor($row['min_seconds'] / 60) . ' mins' : '15 mins'
        ];
    }

    /**
     * Privacy rule: mask email and phone number until explicitly unlocked.
     */
    protected static function applyPrivacyMask(array $row): array
    {
        $row['raw_email'] = $row['email'];
        $row['raw_phone'] = $row['phone'];

        if (empty($row['contact_unlocked'])) {
            // Mask email: j***@example.com
            $parts = explode('@', $row['email']);
            if (count($parts) === 2) {
                $namePart = $parts[0];
                $maskedName = substr($namePart, 0, 1) . str_repeat('*', max(3, strlen($namePart) - 1));
                $row['masked_email'] = $maskedName . '@' . $parts[1];
            } else {
                $row['masked_email'] = '***@protected.ng';
            }

            // Mask phone: +234 803 *** 1234
            $phone = $row['phone'];
            if (strlen($phone) > 6) {
                $row['masked_phone'] = substr($phone, 0, 4) . ' *** ' . substr($phone, -3);
            } else {
                $row['masked_phone'] = '***-***-****';
            }
        } else {
            $row['masked_email'] = $row['email'];
            $row['masked_phone'] = $row['phone'];
        }

        return $row;
    }
}
