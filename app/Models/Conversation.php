<?php

namespace App\Models;

use App\Core\Database;

class Conversation
{
    public static function findOrCreate(int $userOneId, int $userTwoId, ?int $propertyId = null): int
    {
        // Canonical ordering of user IDs to prevent duplicate reverse rows
        $firstId = min($userOneId, $userTwoId);
        $secondId = max($userOneId, $userTwoId);

        $sql = "SELECT id FROM conversations 
                WHERE user_one_id = ? AND user_two_id = ? AND (property_id = ? OR (? IS NULL AND property_id IS NULL))
                LIMIT 1";

        $existing = Database::fetch($sql, [$firstId, $secondId, $propertyId, $propertyId]);
        if ($existing) {
            return (int)$existing['id'];
        }

        Database::execute(
            "INSERT INTO conversations (property_id, user_one_id, user_two_id, last_message_at, created_at)
             VALUES (?, ?, ?, NOW(), NOW())",
            [$propertyId, $firstId, $secondId]
        );

        return (int)Database::lastInsertId();
    }

    public static function getForUser(int $userId): array
    {
        $sql = "SELECT c.*, 
                       p.title AS property_title, p.slug AS property_slug, p.price AS property_price,
                       u1.full_name AS user_one_name, u1.role AS user_one_role,
                       u2.full_name AS user_two_name, u2.role AS user_two_role,
                       (SELECT m.message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message_snippet,
                       (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.is_read = 0 AND m.sender_id != ?) AS unread_count
                FROM conversations c
                LEFT JOIN properties p ON p.id = c.property_id
                JOIN users u1 ON u1.id = c.user_one_id
                JOIN users u2 ON u2.id = c.user_two_id
                WHERE c.user_one_id = ? OR c.user_two_id = ?
                ORDER BY c.last_message_at DESC";

        return Database::query($sql, [$userId, $userId, $userId]);
    }

    public static function getById(int $conversationId, int $userId): ?array
    {
        $sql = "SELECT c.*, 
                       p.title AS property_title, p.slug AS property_slug, p.price AS property_price, p.reference_code,
                       u1.full_name AS user_one_name, u1.email AS user_one_email,
                       u2.full_name AS user_two_name, u2.email AS user_two_email
                FROM conversations c
                LEFT JOIN properties p ON p.id = c.property_id
                JOIN users u1 ON u1.id = c.user_one_id
                JOIN users u2 ON u2.id = c.user_two_id
                WHERE c.id = ? AND (c.user_one_id = ? OR c.user_two_id = ?)
                LIMIT 1";

        return Database::fetch($sql, [$conversationId, $userId, $userId]);
    }
}
