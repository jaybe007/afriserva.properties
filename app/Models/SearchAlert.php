<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class SearchAlert
{
    /**
     * Create a new search alert
     */
    public static function create(int $userId, string $name, array $criteria, string $frequency = 'daily'): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO search_alerts (user_id, name, criteria_json, frequency, is_active, created_at)
             VALUES (:user_id, :name, :criteria_json, :frequency, 1, NOW())"
        );
        $stmt->execute([
            'user_id' => $userId,
            'name' => trim($name),
            'criteria_json' => json_encode($criteria, JSON_UNESCAPED_SLASHES),
            'frequency' => in_array($frequency, ['instant', 'daily', 'weekly'], true) ? $frequency : 'daily'
        ]);

        return (int) $db->lastInsertId();
    }

    /**
     * Get all search alerts for a specific user
     */
    public static function getByUser(int $userId): array
    {
        return Database::query(
            "SELECT * FROM search_alerts WHERE user_id = :user_id ORDER BY created_at DESC",
            ['user_id' => $userId]
        );
    }

    /**
     * Delete a search alert owned by user
     */
    public static function delete(int $id, int $userId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM search_alerts WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Dispatch search alerts matching recent properties
     */
    public static function dispatchAlerts(): array
    {
        $alerts = Database::query("SELECT * FROM search_alerts WHERE is_active = 1");
        $dispatchedCount = 0;
        $db = Database::getInstance();

        foreach ($alerts as $alert) {
            $criteria = json_decode($alert['criteria_json'] ?? '{}', true) ?: [];
            
            // Build query matching published properties
            $query = "SELECT id, title, price, currency, slug FROM properties WHERE status = 'published'";
            $params = [];

            $type = $criteria['transaction_type'] ?? $criteria['purpose'] ?? $criteria['type'] ?? null;
            if (!empty($type)) {
                $query .= " AND transaction_type = :transaction_type";
                $params['transaction_type'] = $type;
            }
            if (!empty($criteria['state_id'])) {
                $query .= " AND state_id = :state_id";
                $params['state_id'] = (int)$criteria['state_id'];
            }
            if (!empty($criteria['min_price'])) {
                $query .= " AND price >= :min_price";
                $params['min_price'] = (float)$criteria['min_price'];
            }
            if (!empty($criteria['max_price'])) {
                $query .= " AND price <= :max_price";
                $params['max_price'] = (float)$criteria['max_price'];
            }

            $query .= " ORDER BY created_at DESC LIMIT 5";
            $matches = Database::query($query, $params);

            if (!empty($matches)) {
                // Send in-app notification if matches found
                $matchCount = count($matches);
                $firstProp = $matches[0];
                $title = "Search Alert Match: {$alert['name']}";
                $message = "We found {$matchCount} matching properties for your alert '{$alert['name']}', including '{$firstProp['title']}'.";
                $linkUrl = url('/properties/' . $firstProp['slug']);

                $notifStmt = $db->prepare(
                    "INSERT INTO in_app_notifications (user_id, title, message, type, link_url, is_read, created_at)
                     VALUES (:user_id, :title, :message, 'search_alert', :link_url, 0, NOW())"
                );
                $notifStmt->execute([
                    'user_id' => $alert['user_id'],
                    'title' => $title,
                    'message' => $message,
                    'link_url' => $linkUrl
                ]);

                // Update last_notified_at
                $updateStmt = $db->prepare("UPDATE search_alerts SET last_notified_at = NOW() WHERE id = :id");
                $updateStmt->execute(['id' => $alert['id']]);

                $dispatchedCount++;
            }
        }

        return [
            'total_alerts_checked' => count($alerts),
            'notifications_dispatched' => $dispatchedCount
        ];
    }
}
