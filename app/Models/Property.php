<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Property
{
    public static function all(array $filters = [], int $limit = 12, int $offset = 0): array
    {
        $where = ["p.status = 'published'", "p.deleted_at IS NULL"];
        $params = [];

        self::applyFilters($where, $params, $filters);

        $whereClause = implode(" AND ", $where);
        $orderBy = self::determineOrderBy($filters['sort'] ?? 'newest');

        $sql = "SELECT p.*, 
                       c.name AS category_name, c.slug AS category_slug,
                       pt.name AS property_type_name, pt.slug AS property_type_slug,
                       s.name AS state_name,
                       ci.name AS city_name,
                       n.name AS neighbourhood_name,
                       u.full_name AS agent_name, u.phone AS agent_phone, u.avatar_url AS agent_avatar,
                       ag.name AS agency_name,
                       (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_cover DESC, pi.display_order ASC LIMIT 1) AS cover_image
                FROM properties p
                JOIN property_categories c ON c.id = p.category_id
                JOIN property_types pt ON pt.id = p.property_type_id
                JOIN states s ON s.id = p.state_id
                JOIN cities ci ON ci.id = p.city_id
                LEFT JOIN neighbourhoods n ON n.id = p.neighbourhood_id
                JOIN users u ON u.id = p.user_id
                LEFT JOIN agencies ag ON ag.id = p.agency_id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$limit} OFFSET {$offset}";

        return Database::query($sql, $params);
    }

    public static function count(array $filters = []): int
    {
        $where = ["p.status = 'published'", "p.deleted_at IS NULL"];
        $params = [];

        self::applyFilters($where, $params, $filters);

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT COUNT(*) FROM properties p
                JOIN property_categories c ON c.id = p.category_id
                JOIN property_types pt ON pt.id = p.property_type_id
                JOIN states s ON s.id = p.state_id
                JOIN cities ci ON ci.id = p.city_id
                LEFT JOIN neighbourhoods n ON n.id = p.neighbourhood_id
                WHERE {$whereClause}";

        return (int)Database::fetchColumn($sql, $params);
    }

    public static function findBySlug(string $slug): ?array
    {
        $sql = "SELECT p.*, 
                       c.name AS category_name, c.slug AS category_slug,
                       pt.name AS property_type_name, pt.slug AS property_type_slug,
                       s.name AS state_name,
                       ci.name AS city_name,
                       n.name AS neighbourhood_name,
                       u.full_name AS agent_name, u.email AS agent_email, u.phone AS agent_phone, u.avatar_url AS agent_avatar, u.bio AS agent_bio,
                       ag.name AS agency_name, ag.phone AS agency_phone, ag.email AS agency_email, ag.logo_url AS agency_logo,
                       ap.whatsapp_number, ap.rating AS agent_rating, ap.total_reviews AS agent_reviews, ap.license_no AS agent_license
                FROM properties p
                JOIN property_categories c ON c.id = p.category_id
                JOIN property_types pt ON pt.id = p.property_type_id
                JOIN states s ON s.id = p.state_id
                JOIN cities ci ON ci.id = p.city_id
                LEFT JOIN neighbourhoods n ON n.id = p.neighbourhood_id
                JOIN users u ON u.id = p.user_id
                LEFT JOIN agencies ag ON ag.id = p.agency_id
                LEFT JOIN agent_profiles ap ON ap.user_id = u.id
                WHERE p.slug = ? AND p.deleted_at IS NULL
                LIMIT 1";

        $property = Database::fetch($sql, [$slug]);
        if (!$property) return null;

        // Fetch images
        $property['images'] = Database::query(
            "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_cover DESC, display_order ASC",
            [$property['id']]
        );

        // Fetch amenities
        $property['amenities'] = Database::query(
            "SELECT a.* FROM amenities a
             JOIN property_amenities pa ON pa.amenity_id = a.id
             WHERE pa.property_id = ?",
            [$property['id']]
        );

        return $property;
    }

    public static function findById(int $id): ?array
    {
        $sql = "SELECT p.*, 
                       c.name AS category_name,
                       pt.name AS property_type_name,
                       s.name AS state_name,
                       ci.name AS city_name,
                       n.name AS neighbourhood_name
                FROM properties p
                JOIN property_categories c ON c.id = p.category_id
                JOIN property_types pt ON pt.id = p.property_type_id
                JOIN states s ON s.id = p.state_id
                JOIN cities ci ON ci.id = p.city_id
                LEFT JOIN neighbourhoods n ON n.id = p.neighbourhood_id
                WHERE p.id = ? AND p.deleted_at IS NULL
                LIMIT 1";

        $prop = Database::fetch($sql, [$id]);
        if (!$prop) return null;

        $prop['images'] = Database::query(
            "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_cover DESC, display_order ASC",
            [$id]
        );
        return $prop;
    }

    public static function getFeatured(int $limit = 6): array
    {
        return self::all(['featured' => 1], $limit);
    }

    public static function getRecent(int $limit = 6): array
    {
        return self::all(['sort' => 'newest'], $limit);
    }

    public static function getLuxury(int $limit = 6): array
    {
        return self::all(['luxury' => 1], $limit);
    }

    public static function getMapCoordinates(array $filters = []): array
    {
        $where = ["p.status = 'published'", "p.deleted_at IS NULL", "p.latitude IS NOT NULL", "p.longitude IS NOT NULL"];
        $params = [];

        self::applyFilters($where, $params, $filters);
        $whereClause = implode(" AND ", $where);

        $sql = "SELECT p.id, p.title, p.slug, p.price, p.currency, p.transaction_type,
                       p.bedrooms, p.bathrooms, p.latitude, p.longitude, p.address,
                       (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_cover DESC, pi.display_order ASC LIMIT 1) AS cover_image
                FROM properties p
                JOIN property_categories c ON c.id = p.category_id
                JOIN property_types pt ON pt.id = p.property_type_id
                JOIN states s ON s.id = p.state_id
                JOIN cities ci ON ci.id = p.city_id
                LEFT JOIN neighbourhoods n ON n.id = p.neighbourhood_id
                WHERE {$whereClause}
                LIMIT 100";

        return Database::query($sql, $params);
    }

    public static function incrementViews(int $id): void
    {
        Database::execute("UPDATE properties SET view_count = view_count + 1 WHERE id = ?", [$id]);
    }

    private static function applyFilters(array &$where, array &$params, array $filters): void
    {
        if (!empty($filters['keyword'])) {
            $keyword = '%' . trim($filters['keyword']) . '%';
            $where[] = "(p.title LIKE ? OR p.description LIKE ? OR p.address LIKE ? OR s.name LIKE ? OR ci.name LIKE ? OR n.name LIKE ?)";
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($filters['transaction_type'])) {
            $where[] = "p.transaction_type = ?";
            $params[] = $filters['transaction_type'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        if (!empty($filters['property_type_id'])) {
            $where[] = "p.property_type_id = ?";
            $params[] = (int)$filters['property_type_id'];
        }

        if (!empty($filters['state_id'])) {
            $where[] = "p.state_id = ?";
            $params[] = (int)$filters['state_id'];
        }

        if (!empty($filters['city_id'])) {
            $where[] = "p.city_id = ?";
            $params[] = (int)$filters['city_id'];
        }

        if (!empty($filters['neighbourhood_id'])) {
            $where[] = "p.neighbourhood_id = ?";
            $params[] = (int)$filters['neighbourhood_id'];
        }

        if (!empty($filters['bedrooms'])) {
            $where[] = "p.bedrooms >= ?";
            $params[] = (int)$filters['bedrooms'];
        }

        if (!empty($filters['bathrooms'])) {
            $where[] = "p.bathrooms >= ?";
            $params[] = (int)$filters['bathrooms'];
        }

        if (!empty($filters['toilets'])) {
            $where[] = "p.toilets >= ?";
            $params[] = (int)$filters['toilets'];
        }

        if (!empty($filters['parking_spaces'])) {
            $where[] = "p.parking_spaces >= ?";
            $params[] = (int)$filters['parking_spaces'];
        }

        if (!empty($filters['property_condition'])) {
            $where[] = "p.property_condition = ?";
            $params[] = $filters['property_condition'];
        }

        if (!empty($filters['min_price'])) {
            $where[] = "p.price >= ?";
            $params[] = (float)$filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = "p.price <= ?";
            $params[] = (float)$filters['max_price'];
        }

        if (!empty($filters['furnishing_status'])) {
            $where[] = "p.furnishing_status = ?";
            $params[] = $filters['furnishing_status'];
        }

        if (!empty($filters['title_document'])) {
            $where[] = "p.title_document LIKE ?";
            $params[] = '%' . $filters['title_document'] . '%';
        }

        if (!empty($filters['featured'])) {
            $where[] = "p.is_featured = 1";
        }

        if (!empty($filters['luxury'])) {
            $where[] = "p.is_luxury = 1";
        }

        if (!empty($filters['recently_reduced'])) {
            $where[] = "(p.price_prefix LIKE '%Reduced%' OR p.price_prefix LIKE '%Discount%')";
        }

        if (!empty($filters['affordable'])) {
            $where[] = "p.price <= 60000000.00";
        }

        if (!empty($filters['verified'])) {
            $where[] = "p.verification_status = 'verified'";
        }

        if (!empty($filters['user_id'])) {
            $where[] = "p.user_id = ?";
            $params[] = (int)$filters['user_id'];
        }
    }

    private static function determineOrderBy(string $sort): string
    {
        return match ($sort) {
            'newest' => 'p.created_at DESC',
            'price_asc' => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'views' => 'p.view_count DESC',
            'bedrooms' => 'p.bedrooms DESC',
            'oldest' => 'p.created_at ASC',
            default => 'p.is_featured DESC, p.created_at DESC'
        };
    }

    public static function getNewlyListed(int $limit = 6): array
    {
        return self::all(['sort' => 'newest'], $limit);
    }

    public static function getRecentlyReduced(int $limit = 4): array
    {
        return self::all(['recently_reduced' => 1], $limit);
    }

    public static function getAffordable(int $limit = 4): array
    {
        return self::all(['affordable' => 1], $limit);
    }

    public static function getSimilar(int $propertyId, int $categoryId, int $stateId, int $limit = 3): array
    {
        $sql = "SELECT p.*, 
                       c.name AS category_name,
                       s.name AS state_name,
                       ci.name AS city_name,
                       u.full_name AS agent_name,
                       (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_cover DESC, pi.display_order ASC LIMIT 1) AS cover_image
                FROM properties p
                JOIN property_categories c ON c.id = p.category_id
                JOIN states s ON s.id = p.state_id
                JOIN cities ci ON ci.id = p.city_id
                JOIN users u ON u.id = p.user_id
                WHERE p.id != ? AND (p.category_id = ? OR p.state_id = ?) AND p.status = 'published' AND p.deleted_at IS NULL
                ORDER BY p.is_featured DESC, p.created_at DESC
                LIMIT {$limit}";

        return Database::query($sql, [$propertyId, $categoryId, $stateId]);
    }

    public static function getRecentlyViewed(array $ids): array
    {
        if (empty($ids)) return [];
        $ids = array_map('intval', array_slice($ids, 0, 6));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT p.*, 
                       s.name AS state_name, ci.name AS city_name,
                       (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_cover DESC, pi.display_order ASC LIMIT 1) AS cover_image
                FROM properties p
                JOIN states s ON s.id = p.state_id
                JOIN cities ci ON ci.id = p.city_id
                WHERE p.id IN ({$placeholders}) AND p.status = 'published' AND p.deleted_at IS NULL";

        return Database::query($sql, $ids);
    }

    public static function detectDuplicates(string $title, string $address, float $price, int $stateId, ?int $excludeId = null): array
    {
        $minPrice = $price * 0.85;
        $maxPrice = $price * 1.15;

        $excludeClause = $excludeId ? "AND p.id != " . (int)$excludeId : "";

        $sql = "SELECT p.id, p.title, p.reference_code, p.address, p.price, p.status, p.verification_status,
                       u.full_name AS agent_name, u.email AS agent_email
                FROM properties p
                JOIN users u ON u.id = p.user_id
                WHERE p.state_id = ? 
                  AND p.deleted_at IS NULL
                  {$excludeClause}
                LIMIT 25";

        $candidates = Database::query($sql, [$stateId]);
        $flagged = [];

        foreach ($candidates as $cand) {
            similar_text(strtolower($title), strtolower($cand['title']), $titleSim);
            similar_text(strtolower($address), strtolower($cand['address']), $addrSim);

            $candPrice = (float)$cand['price'];
            $priceMatch = ($price > 0 && $candPrice >= $minPrice && $candPrice <= $maxPrice);

            if (($titleSim >= 45 && $priceMatch) || $addrSim >= 50 || $titleSim >= 65) {
                $cand['title_similarity_percent'] = round($titleSim, 1);
                $cand['address_similarity_percent'] = round($addrSim, 1);
                $flagged[] = $cand;
            }
        }

        return $flagged;
    }

    public static function updateStatus(int $id, string $status, ?string $reason = null, ?string $feedback = null): bool
    {
        $sql = "UPDATE properties SET status = ?, rejection_reason = ?, reviewer_feedback = ?, updated_at = NOW() WHERE id = ?";
        Database::execute($sql, [$status, $reason, $feedback, $id]);
        return true;
    }

    public static function updateVerificationStatus(int $id, string $verificationStatus, int $reviewerId, ?string $reason = null, ?string $notes = null): bool
    {
        $current = Database::fetch("SELECT status, verification_status FROM properties WHERE id = ?", [$id]);
        if (!$current) return false;

        $prevStatus = $current['verification_status'];

        $sql = "UPDATE properties SET verification_status = ?, rejection_reason = ?, reviewer_feedback = ?, assigned_reviewer_id = ?, updated_at = NOW() WHERE id = ?";
        Database::execute($sql, [$verificationStatus, $reason, $notes, $reviewerId, $id]);

        // If verified, ensure status is published
        if ($verificationStatus === 'verified') {
            Database::execute("UPDATE properties SET status = 'published' WHERE id = ? AND status = 'pending_review'", [$id]);
        } elseif (in_array($verificationStatus, ['rejected', 'expired_or_revoked'], true)) {
            Database::execute("UPDATE properties SET status = 'rejected' WHERE id = ?", [$id]);
        }

        // Record verification audit record in property_verifications table
        Database::execute(
            "INSERT INTO property_verifications (property_id, reviewer_id, previous_status, new_status, rejection_reason, notes, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$id, $reviewerId, $prevStatus, $verificationStatus, $reason, $notes]
        );

        \App\Helpers\AuditLogger::log('property_verification_updated', 'property', $id, ['from' => $prevStatus], [
            'to' => $verificationStatus,
            'reviewer' => $reviewerId,
            'reason' => $reason
        ]);

        return true;
    }

    public static function assignReviewer(int $id, int $reviewerId): bool
    {
        Database::execute("UPDATE properties SET assigned_reviewer_id = ?, verification_status = 'under_review' WHERE id = ?", [$reviewerId, $id]);
        \App\Helpers\AuditLogger::log('reviewer_assigned', 'property', $id, null, ['reviewer_id' => $reviewerId]);
        return true;
    }
}

