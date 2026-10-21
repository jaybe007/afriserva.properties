<?php

declare(strict_types=1);

namespace App\Core;

class Logger
{
    private static string $logDir = __DIR__ . '/../../storage/logs';

    private static function init(): void
    {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0775, true);
        }
    }

    /**
     * Write structured JSON log entry
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        self::init();
        $date = date('Y-m-d');
        $logFile = self::$logDir . "/app-{$date}.log";

        $entry = [
            'timestamp' => date('c'),
            'level' => strtoupper($level),
            'message' => $message,
            'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? substr(hash('sha256', session_id() . microtime()), 0, 16),
            'client_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'console',
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'context' => $context
        ];

        file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    public static function performance(string $action, float $durationMs, array $metrics = []): void
    {
        self::log('perf', "Performance measurement: {$action}", array_merge(['duration_ms' => $durationMs], $metrics));
    }
}
