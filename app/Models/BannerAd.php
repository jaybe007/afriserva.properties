<?php

namespace App\Models;

use App\Core\Database;

class BannerAd
{
    public static function getActive(string $placement = 'homepage_top'): ?array
    {
        $sql = "SELECT * FROM banner_advertisements 
                WHERE placement = ? 
                  AND is_active = 1 
                  AND starts_at <= NOW() 
                  AND ends_at >= NOW()
                ORDER BY id DESC 
                LIMIT 1";

        return Database::fetch($sql, [$placement]);
    }

    public static function recordImpression(int $id): void
    {
        Database::execute("UPDATE banner_advertisements SET impressions_count = impressions_count + 1 WHERE id = ?", [$id]);
    }

    public static function recordClick(int $id): void
    {
        Database::execute("UPDATE banner_advertisements SET clicks_count = clicks_count + 1 WHERE id = ?", [$id]);
    }

    public static function getAll(): array
    {
        return Database::query("SELECT * FROM banner_advertisements ORDER BY created_at DESC");
    }

    public static function create(array $data): int
    {
        $sql = "INSERT INTO banner_advertisements (advertiser_name, title, placement, image_url, target_url, starts_at, ends_at, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())";

        Database::execute($sql, [
            trim($data['advertiser_name']),
            trim($data['title']),
            $data['placement'] ?? 'homepage_top',
            trim($data['image_url']),
            trim($data['target_url']),
            $data['starts_at'],
            $data['ends_at']
        ]);

        return (int)Database::lastInsertId();
    }
}
