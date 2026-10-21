<?php

namespace App\Models;

use App\Core\Database;

class Message
{
    public static function create(int $conversationId, int $senderId, string $message): int
    {
        $sql = "INSERT INTO messages (conversation_id, sender_id, message, is_read, created_at)
                VALUES (?, ?, ?, 0, NOW())";

        Database::execute($sql, [$conversationId, $senderId, trim($message)]);
        $messageId = (int)Database::lastInsertId();

        // Update conversation last_message_at
        Database::execute(
            "UPDATE conversations SET last_message_at = NOW() WHERE id = ?",
            [$conversationId]
        );

        return $messageId;
    }

    public static function getByConversation(int $conversationId, int $limit = 100): array
    {
        $sql = "SELECT m.*, u.full_name AS sender_name, u.role AS sender_role
                FROM messages m
                JOIN users u ON u.id = m.sender_id
                WHERE m.conversation_id = ?
                ORDER BY m.created_at ASC
                LIMIT {$limit}";

        return Database::query($sql, [$conversationId]);
    }

    public static function markAsRead(int $conversationId, int $readerId): bool
    {
        return Database::execute(
            "UPDATE messages SET is_read = 1 
             WHERE conversation_id = ? AND sender_id != ? AND is_read = 0",
            [$conversationId, $readerId]
        ) > 0;
    }

    public static function getUnreadCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM messages m
                JOIN conversations c ON c.id = m.conversation_id
                WHERE (c.user_one_id = ? OR c.user_two_id = ?)
                  AND m.sender_id != ?
                  AND m.is_read = 0";

        $res = Database::fetch($sql, [$userId, $userId, $userId]);
        return (int)($res['total'] ?? 0);
    }
}
