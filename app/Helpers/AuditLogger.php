<?php

namespace App\Helpers;

use App\Core\Database;
use App\Core\Auth;

class AuditLogger
{
    public static function log(string $action, ?string $targetType = null, ?int $targetId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        $userId = Auth::id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'System';

        Database::execute(
            "INSERT INTO audit_logs (user_id, action, target_type, target_id, old_values, new_values, ip_address, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $userId,
                $action,
                $targetType,
                $targetId,
                $oldValues ? json_encode($oldValues, JSON_UNESCAPED_SLASHES) : null,
                $newValues ? json_encode($newValues, JSON_UNESCAPED_SLASHES) : null,
                $ip,
                substr($ua, 0, 255)
            ]
        );
    }
}
