<?php

namespace App\Models;

use App\Core\Database;

class Subscription
{
    public static function getPlans(): array
    {
        return Database::query("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY display_order ASC");
    }

    public static function getAllPlans(): array
    {
        return Database::query("SELECT * FROM subscription_plans ORDER BY display_order ASC");
    }

    public static function findPlanBySlug(string $slug): ?array
    {
        return Database::fetch("SELECT * FROM subscription_plans WHERE slug = ? LIMIT 1", [$slug]);
    }

    public static function getPlanById(int $id): ?array
    {
        return Database::fetch("SELECT * FROM subscription_plans WHERE id = ? LIMIT 1", [$id]);
    }

    public static function getDefaultPlan(): ?array
    {
        $plan = Database::fetch("SELECT * FROM subscription_plans WHERE is_default = 1 AND is_active = 1 LIMIT 1");
        if (!$plan) {
            $plan = Database::fetch("SELECT * FROM subscription_plans WHERE slug = 'afriserva-commission-plan' LIMIT 1");
        }
        if (!$plan) {
            $plan = Database::fetch("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY display_order ASC LIMIT 1");
        }
        return $plan;
    }

    public static function assignDefaultPlan(int $userId): bool
    {
        $defaultPlan = self::getDefaultPlan();
        if (!$defaultPlan) {
            return false;
        }

        // Check if user already has an active subscription
        $activeSub = self::getUserSubscription($userId);
        if ($activeSub) {
            return true; // Already subscribed
        }

        $now = date('Y-m-d H:i:s');
        $endsAt = date('Y-m-d H:i:s', strtotime('+1 year'));

        $sql = "INSERT INTO subscriptions (
            user_id, plan_id, status, starts_at, ends_at, auto_renew, created_at
        ) VALUES (?, ?, 'active', ?, ?, 1, NOW())";

        return Database::execute($sql, [
            $userId,
            (int)$defaultPlan['id'],
            $now,
            $endsAt
        ]) > 0;
    }

    public static function migrateEligibleUsersToCommissionPlan(): int
    {
        $defaultPlan = self::getDefaultPlan();
        if (!$defaultPlan) return 0;

        // Find users with seller or agent roles who don't have an active paid subscription
        $eligibleUsers = Database::query(
            "SELECT DISTINCT u.id 
             FROM users u
             JOIN user_roles ur ON ur.user_id = u.id
             JOIN roles r ON r.id = ur.role_id
             WHERE r.name IN ('seller', 'agent', 'landlord', 'developer')
               AND u.id NOT IN (
                   SELECT s.user_id 
                   FROM subscriptions s 
                   JOIN subscription_plans sp ON sp.id = s.plan_id
                   WHERE s.status = 'active' AND sp.price > 0 AND s.ends_at > NOW()
               )"
        );

        $assignedCount = 0;
        foreach ($eligibleUsers as $u) {
            $userId = (int)$u['id'];
            // Check if already on active commission plan
            $existing = Database::fetch(
                "SELECT id FROM subscriptions WHERE user_id = ? AND plan_id = ? AND status = 'active' LIMIT 1",
                [$userId, (int)$defaultPlan['id']]
            );
            if (!$existing) {
                if (self::assignDefaultPlan($userId)) {
                    $assignedCount++;
                }
            }
        }

        return $assignedCount;
    }

    public static function updatePlan(int $id, array $data): bool
    {
        $isDefault = !empty($data['is_default']) ? 1 : 0;
        if ($isDefault) {
            Database::execute("UPDATE subscription_plans SET is_default = 0 WHERE id != ?", [$id]);
        }

        $sql = "UPDATE subscription_plans 
                SET name = ?, price = ?, currency = ?, billing_interval = ?, listing_allowance = ?,
                    featured_allowance = ?, team_members_limit = ?, listing_duration_days = ?,
                    grace_period_days = ?, analytics_level = ?, renewal_policy = ?, lead_price = ?,
                    is_active = ?, is_default = ?, commission_rate = ?, fixed_markup = ?,
                    commission_mode = ?, commission_basis = ?
                WHERE id = ?";

        return Database::execute($sql, [
            trim($data['name']),
            (float)$data['price'],
            $data['currency'] ?? 'NGN',
            $data['billing_interval'] ?? 'monthly',
            (int)$data['listing_allowance'],
            (int)$data['featured_allowance'],
            (int)$data['team_members_limit'],
            (int)($data['listing_duration_days'] ?? 30),
            (int)($data['grace_period_days'] ?? 7),
            $data['analytics_level'] ?? 'basic',
            $data['renewal_policy'] ?? 'flexible',
            (float)($data['lead_price'] ?? 0.00),
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $isDefault,
            (float)($data['commission_rate'] ?? 0.00),
            (float)($data['fixed_markup'] ?? 0.00),
            $data['commission_mode'] ?? 'percentage',
            $data['commission_basis'] ?? 'added_to_asking',
            $id
        ]) >= 0;
    }

    public static function getUserSubscription(int $userId): ?array
    {
        $sql = "SELECT s.*, sp.name AS plan_name, sp.slug AS plan_slug, sp.plan_type, sp.price AS plan_price,
                       sp.listing_allowance, sp.featured_allowance, sp.team_members_limit, sp.grace_period_days,
                       sp.analytics_level, sp.commission_rate, sp.fixed_markup, sp.commission_mode, sp.commission_basis
                FROM subscriptions s
                JOIN subscription_plans sp ON sp.id = s.plan_id
                WHERE s.user_id = ? AND s.status = 'active'
                ORDER BY s.ends_at DESC
                LIMIT 1";

        return Database::fetch($sql, [$userId]);
    }

    public static function canUserCreateListing(int $userId): bool
    {
        $sub = self::getUserSubscription($userId);

        // If user has no active subscription record, check default plan allowance
        if (!$sub) {
            $defaultPlan = self::getDefaultPlan();
            $limit = $defaultPlan ? (int)$defaultPlan['listing_allowance'] : 500;
        } else {
            $limit = (int)$sub['listing_allowance'];
        }

        $countRow = Database::fetch(
            "SELECT COUNT(*) AS active_count FROM properties WHERE user_id = ? AND status = 'published'",
            [$userId]
        );

        $activeCount = (int)($countRow['active_count'] ?? 0);
        return $activeCount < $limit;
    }

    public static function canUserFeatureListing(int $userId): bool
    {
        $sub = self::getUserSubscription($userId);
        $allowance = $sub ? (int)$sub['featured_allowance'] : 0;
        if ($allowance <= 0) {
            return false;
        }

        $countRow = Database::fetch(
            "SELECT COUNT(*) AS featured_count FROM properties WHERE user_id = ? AND is_featured = 1 AND status = 'published'",
            [$userId]
        );

        return ((int)($countRow['featured_count'] ?? 0)) < $allowance;
    }

    public static function applyCoupon(string $code, float $amount): array
    {
        $code = strtoupper(trim($code));
        $coupon = Database::fetch(
            "SELECT * FROM coupons 
             WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())
             LIMIT 1",
            [$code]
        );

        if (!$coupon) {
            return ['valid' => false, 'discount' => 0.00, 'final_amount' => $amount, 'message' => 'Invalid or expired promotional code.'];
        }

        if (!empty($coupon['max_uses']) && $coupon['uses_count'] >= $coupon['max_uses']) {
            return ['valid' => false, 'discount' => 0.00, 'final_amount' => $amount, 'message' => 'This coupon has reached its maximum usage limit.'];
        }

        $discount = 0.00;
        if ($coupon['discount_type'] === 'percentage') {
            $discount = round(($amount * (float)$coupon['discount_value']) / 100, 2);
        } else {
            $discount = min($amount, (float)$coupon['discount_value']);
        }

        $finalAmount = max(0.00, $amount - $discount);

        return [
            'valid' => true,
            'coupon_id' => (int)$coupon['id'],
            'code' => $coupon['code'],
            'discount' => $discount,
            'final_amount' => $finalAmount,
            'message' => "Coupon {$coupon['code']} applied successfully!"
        ];
    }
}
