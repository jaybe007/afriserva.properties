<?php

namespace App\Models;

use App\Core\Database;

class Lease
{
    public static function create(array $data): int
    {
        $sql = "INSERT INTO leases (property_id, landlord_id, tenant_id, lease_start_date, lease_end_date, rent_amount, payment_frequency, deposit_amount, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";

        Database::execute($sql, [
            (int)$data['property_id'],
            (int)$data['landlord_id'],
            (int)$data['tenant_id'],
            $data['lease_start_date'],
            $data['lease_end_date'],
            (float)$data['rent_amount'],
            $data['payment_frequency'] ?? 'annually',
            (float)($data['deposit_amount'] ?? 0.00)
        ]);

        return (int)Database::lastInsertId();
    }

    public static function getForLandlord(int $landlordId): array
    {
        $sql = "SELECT l.*, 
                       p.title AS property_title, p.slug AS property_slug, p.address AS property_address,
                       t.full_name AS tenant_name, t.email AS tenant_email, t.phone AS tenant_phone,
                       DATEDIFF(l.lease_end_date, CURDATE()) AS days_until_expiry,
                       (SELECT COALESCE(SUM(amount), 0) FROM rent_payments rp WHERE rp.lease_id = l.id AND rp.status = 'overdue') AS total_arrears,
                       (SELECT COALESCE(SUM(amount), 0) FROM rent_payments rp WHERE rp.lease_id = l.id AND rp.status = 'paid') AS total_paid
                FROM leases l
                JOIN properties p ON p.id = l.property_id
                JOIN users t ON t.id = l.tenant_id
                WHERE l.landlord_id = ?
                ORDER BY l.status ASC, l.lease_end_date ASC";

        return Database::query($sql, [$landlordId]);
    }

    public static function getForTenant(int $tenantId): array
    {
        $sql = "SELECT l.*, 
                       p.title AS property_title, p.slug AS property_slug, p.address AS property_address,
                       ll.full_name AS landlord_name, ll.email AS landlord_email, ll.phone AS landlord_phone,
                       DATEDIFF(l.lease_end_date, CURDATE()) AS days_until_expiry
                FROM leases l
                JOIN properties p ON p.id = l.property_id
                JOIN users ll ON ll.id = l.landlord_id
                WHERE l.tenant_id = ?
                ORDER BY l.status ASC, l.lease_end_date ASC";

        return Database::query($sql, [$tenantId]);
    }

    public static function getById(int $leaseId): ?array
    {
        $sql = "SELECT l.*, 
                       p.title AS property_title, p.slug AS property_slug, p.address AS property_address,
                       t.full_name AS tenant_name, t.email AS tenant_email, t.phone AS tenant_phone,
                       ll.full_name AS landlord_name, ll.email AS landlord_email, ll.phone AS landlord_phone,
                       DATEDIFF(l.lease_end_date, CURDATE()) AS days_until_expiry
                FROM leases l
                JOIN properties p ON p.id = l.property_id
                JOIN users t ON t.id = l.tenant_id
                JOIN users ll ON ll.id = l.landlord_id
                WHERE l.id = ?
                LIMIT 1";

        return Database::fetch($sql, [$leaseId]);
    }

    public static function getPayments(int $leaseId): array
    {
        return Database::query(
            "SELECT * FROM rent_payments WHERE lease_id = ? ORDER BY due_date ASC",
            [$leaseId]
        );
    }

    public static function recordPayment(int $leaseId, float $amount, string $dueDate, ?string $paidDate = null, string $method = 'bank_transfer', string $status = 'paid'): int
    {
        $ref = 'RCP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        $sql = "INSERT INTO rent_payments (lease_id, amount, due_date, paid_date, payment_method, status, receipt_reference, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        Database::execute($sql, [
            $leaseId,
            $amount,
            $dueDate,
            $paidDate,
            $method,
            $status,
            $ref
        ]);

        return (int)Database::lastInsertId();
    }

    public static function getArrearsSummary(int $landlordId): array
    {
        $sql = "SELECT 
                    COUNT(DISTINCT l.id) AS overdue_leases_count,
                    COALESCE(SUM(rp.amount), 0) AS total_arrears_amount
                FROM leases l
                JOIN rent_payments rp ON rp.lease_id = l.id
                WHERE l.landlord_id = ? 
                  AND rp.status = 'overdue' 
                  AND l.status = 'active'";

        $res = Database::fetch($sql, [$landlordId]);
        return [
            'overdue_count' => (int)($res['overdue_leases_count'] ?? 0),
            'total_arrears' => (float)($res['total_arrears_amount'] ?? 0.00)
        ];
    }

    public static function getExpiringLeases(int $landlordId, int $daysThreshold = 60): array
    {
        $sql = "SELECT l.*, p.title AS property_title, t.full_name AS tenant_name,
                       DATEDIFF(l.lease_end_date, CURDATE()) AS days_remaining
                FROM leases l
                JOIN properties p ON p.id = l.property_id
                JOIN users t ON t.id = l.tenant_id
                WHERE l.landlord_id = ? 
                  AND l.status = 'active'
                  AND l.lease_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY l.lease_end_date ASC";

        return Database::query($sql, [$landlordId, $daysThreshold]);
    }
}
