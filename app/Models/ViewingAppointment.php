<?php

namespace App\Models;

use App\Core\Database;

class ViewingAppointment
{
    public static function create(array $data): int
    {
        $sql = "INSERT INTO viewing_appointments (property_id, user_id, agent_id, appointment_date, appointment_time, viewing_type, status, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())";

        Database::execute($sql, [
            (int)$data['property_id'],
            (int)$data['user_id'],
            (int)$data['agent_id'],
            $data['appointment_date'],
            $data['appointment_time'],
            $data['viewing_type'] ?? 'physical',
            $data['notes'] ?? null
        ]);

        return (int)Database::lastInsertId();
    }

    public static function getByAgent(int $agentId): array
    {
        $sql = "SELECT va.*, p.title AS property_title, p.slug AS property_slug, p.address,
                       u.full_name AS client_name, u.email AS client_email, u.phone AS client_phone
                FROM viewing_appointments va
                JOIN properties p ON p.id = va.property_id
                JOIN users u ON u.id = va.user_id
                WHERE va.agent_id = ?
                ORDER BY va.appointment_date ASC, va.appointment_time ASC";

        $rows = Database::query($sql, [$agentId]);
        return array_map(function($row) {
            // Anti-Bypass Security: Mask buyer contact info on viewing schedules
            $email = $row['client_email'] ?? '';
            $phone = $row['client_phone'] ?? '';
            $parts = explode('@', $email);
            $row['client_masked_email'] = (count($parts) === 2) 
                ? substr($parts[0], 0, 1) . '***@' . $parts[1] 
                : '***@client.afriserva.com';
            $row['client_masked_phone'] = (strlen($phone) > 6) 
                ? substr($phone, 0, 4) . ' *** ' . substr($phone, -3) 
                : '***-***-****';
            // Redact raw contact info
            $row['client_email'] = $row['client_masked_email'];
            $row['client_phone'] = $row['client_masked_phone'];
            return $row;
        }, $rows);
    }

    public static function getByUser(int $userId): array
    {
        $sql = "SELECT va.*, p.title AS property_title, p.slug AS property_slug, p.address,
                       u.full_name AS agent_name, u.phone AS agent_phone
                FROM viewing_appointments va
                JOIN properties p ON p.id = va.property_id
                JOIN users u ON u.id = va.agent_id
                WHERE va.user_id = ?
                ORDER BY va.appointment_date ASC";

        return Database::query($sql, [$userId]);
    }
}
