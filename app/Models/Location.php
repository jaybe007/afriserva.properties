<?php

namespace App\Models;

use App\Core\Database;

class Location
{
    public static function getCountries(): array
    {
        return Database::query("SELECT * FROM countries WHERE is_active = 1 ORDER BY name ASC");
    }

    public static function getStates(int $countryId = 0): array
    {
        if ($countryId > 0) {
            return Database::query("SELECT * FROM states WHERE country_id = ? AND is_active = 1 ORDER BY name ASC", [$countryId]);
        }
        return Database::query("SELECT * FROM states WHERE is_active = 1 ORDER BY name ASC");
    }

    public static function getCitiesByState(int $stateId): array
    {
        return Database::query("SELECT * FROM cities WHERE state_id = ? AND is_active = 1 ORDER BY name ASC", [$stateId]);
    }

    public static function getLocalGovernmentsByState(int $stateId): array
    {
        return Database::query("SELECT * FROM local_governments WHERE state_id = ? ORDER BY name ASC", [$stateId]);
    }

    public static function getNeighbourhoods(int $cityId): array
    {
        return Database::query("SELECT * FROM neighbourhoods WHERE city_id = ? ORDER BY name ASC", [$cityId]);
    }

    public static function getPopularLocations(): array
    {
        $sql = "SELECT n.id, n.name, n.slug, ci.name AS city_name, s.name AS state_name,
                       COUNT(p.id) AS total_properties,
                       MIN(p.price) AS min_price,
                       MAX(p.price) AS max_price
                FROM neighbourhoods n
                JOIN cities ci ON ci.id = n.city_id
                JOIN states s ON s.id = ci.state_id
                LEFT JOIN properties p ON p.neighbourhood_id = n.id AND p.status = 'published'
                GROUP BY n.id
                HAVING total_properties > 0
                ORDER BY total_properties DESC
                LIMIT 8";

        return Database::query($sql);
    }

    public static function createState(array $data): int
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name']), '-'));
        $sql = "INSERT INTO states (country_id, name, slug, code, is_active)
                VALUES (?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE name = VALUES(name), code = VALUES(code)";
        Database::execute($sql, [
            (int)($data['country_id'] ?? 1),
            trim($data['name']),
            $slug,
            !empty($data['code']) ? strtoupper(trim($data['code'])) : null
        ]);
        return (int)Database::lastInsertId();
    }

    public static function createCity(array $data): int
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name']), '-'));
        $sql = "INSERT INTO cities (state_id, name, slug, is_active)
                VALUES (?, ?, ?, 1)";
        Database::execute($sql, [
            (int)$data['state_id'],
            trim($data['name']),
            $slug
        ]);
        return (int)Database::lastInsertId();
    }

    public static function createLga(array $data): int
    {
        $sql = "INSERT INTO local_governments (state_id, name)
                VALUES (?, ?)";
        Database::execute($sql, [
            (int)$data['state_id'],
            trim($data['name'])
        ]);
        return (int)Database::lastInsertId();
    }

    public static function createNeighbourhood(array $data): int
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name']), '-'));
        $sql = "INSERT INTO neighbourhoods (city_id, lga_id, name, slug, latitude, longitude)
                VALUES (?, ?, ?, ?, ?, ?)";
        Database::execute($sql, [
            (int)$data['city_id'],
            !empty($data['lga_id']) ? (int)$data['lga_id'] : null,
            trim($data['name']),
            $slug,
            !empty($data['latitude']) ? (float)$data['latitude'] : null,
            !empty($data['longitude']) ? (float)$data['longitude'] : null
        ]);
        return (int)Database::lastInsertId();
    }
}
