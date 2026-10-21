<?php

namespace App\Core;

use App\Core\Database;

class NotificationService
{
    /**
     * Send an in-app notification to a user.
     */
    public static function send(int $userId, string $title, string $message, string $type = 'system', ?string $linkUrl = null): int
    {
        $sql = "INSERT INTO in_app_notifications (user_id, title, message, type, link_url, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW())";

        Database::execute($sql, [$userId, $title, $message, $type, $linkUrl]);
        return (int)Database::lastInsertId();
    }

    /**
     * Get unread notifications count for a user.
     */
    public static function getUnreadCount(int $userId): int
    {
        $row = Database::fetch(
            "SELECT COUNT(*) AS total FROM in_app_notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        return (int)($row['total'] ?? 0);
    }

    /**
     * Get notifications for a user.
     */
    public static function getForUser(int $userId, int $limit = 20): array
    {
        return Database::query(
            "SELECT * FROM in_app_notifications 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT {$limit}",
            [$userId]
        );
    }

    /**
     * Mark a notification as read.
     */
    public static function markAsRead(int $id, int $userId): bool
    {
        return Database::execute(
            "UPDATE in_app_notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?",
            [$id, $userId]
        ) > 0;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public static function markAllAsRead(int $userId): bool
    {
        return Database::execute(
            "UPDATE in_app_notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0",
            [$userId]
        ) > 0;
    }
}
