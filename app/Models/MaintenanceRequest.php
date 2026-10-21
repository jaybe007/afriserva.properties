<?php

namespace App\Models;

use App\Core\Database;

class MaintenanceRequest
{
    public static function create(array $data): int
    {
        $sql = "INSERT INTO maintenance_requests 
                (property_id, tenant_id, landlord_id, title, description, priority, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'submitted', NOW())";

        Database::execute($sql, [
            (int)$data['property_id'],
            (int)$data['tenant_id'],
            (int)$data['landlord_id'],
            trim($data['title']),
            trim($data['description']),
            $data['priority'] ?? 'medium'
        ]);

        return (int)Database::lastInsertId();
    }

    public static function getForLandlord(int $landlordId): array
    {
        $sql = "SELECT mr.*, 
                       p.title AS property_title, p.address AS property_address,
                       t.full_name AS tenant_name, t.phone AS tenant_phone, t.email AS tenant_email
                FROM maintenance_requests mr
                JOIN properties p ON p.id = mr.property_id
                JOIN users t ON t.id = mr.tenant_id
                WHERE mr.landlord_id = ?
                ORDER BY FIELD(mr.status, 'submitted', 'in_progress', 'resolved', 'closed'), mr.created_at DESC";

        return Database::query($sql, [$landlordId]);
    }

    public static function getForTenant(int $tenantId): array
    {
        $sql = "SELECT mr.*, 
                       p.title AS property_title, p.address AS property_address,
                       ll.full_name AS landlord_name, ll.phone AS landlord_phone
                FROM maintenance_requests mr
                JOIN properties p ON p.id = mr.property_id
                JOIN users ll ON ll.id = mr.landlord_id
                WHERE mr.tenant_id = ?
                ORDER BY mr.created_at DESC";

        return Database::query($sql, [$tenantId]);
    }

    public static function updateAssignment(int $id, array $data): bool
    {
        $sql = "UPDATE maintenance_requests 
                SET assigned_contractor_name = ?,
                    assigned_contractor_phone = ?,
                    cost = ?,
                    management_fee = ?,
                    scheduled_date = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?";

        return Database::execute($sql, [
            trim($data['assigned_contractor_name'] ?? ''),
            trim($data['assigned_contractor_phone'] ?? ''),
            !empty($data['cost']) ? (float)$data['cost'] : 0.00,
            !empty($data['management_fee']) ? (float)$data['management_fee'] : 0.00,
            $data['scheduled_date'] ?: null,
            $data['status'] ?? 'in_progress',
            $id
        ]) > 0;
    }

    public static function resolve(int $id): bool
    {
        return Database::execute(
            "UPDATE maintenance_requests SET status = 'resolved', resolved_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$id]
        ) > 0;
    }
}
