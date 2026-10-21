<?php

namespace App\Models;

use App\Core\Database;

class RentalApplication
{
    public static function create(array $data): int
    {
        $sql = "INSERT INTO rental_applications 
                (property_id, applicant_id, landlord_id, annual_income, employer_name, employment_status, 
                 guarantor_name, guarantor_phone, guarantor_email, guarantor_relationship, status, screening_status, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', 'pending', ?, NOW())";

        Database::execute($sql, [
            (int)$data['property_id'],
            (int)$data['applicant_id'],
            (int)$data['landlord_id'],
            (float)$data['annual_income'],
            trim($data['employer_name']),
            $data['employment_status'] ?? 'employed',
            trim($data['guarantor_name']),
            trim($data['guarantor_phone']),
            trim(strtolower($data['guarantor_email'])),
            trim($data['guarantor_relationship']),
            trim($data['notes'] ?? '')
        ]);

        return (int)Database::lastInsertId();
    }

    public static function getForLandlord(int $landlordId): array
    {
        $sql = "SELECT ra.*, p.title AS property_title, p.slug AS property_slug, p.price AS property_price,
                       u.full_name AS applicant_name, u.email AS applicant_email, u.phone AS applicant_phone
                FROM rental_applications ra
                JOIN properties p ON p.id = ra.property_id
                JOIN users u ON u.id = ra.applicant_id
                WHERE ra.landlord_id = ?
                ORDER BY ra.created_at DESC";

        return Database::query($sql, [$landlordId]);
    }

    public static function getForApplicant(int $applicantId): array
    {
        $sql = "SELECT ra.*, p.title AS property_title, p.slug AS property_slug, p.price AS property_price,
                       p.state_id, p.address
                FROM rental_applications ra
                JOIN properties p ON p.id = ra.property_id
                WHERE ra.applicant_id = ?
                ORDER BY ra.created_at DESC";

        return Database::query($sql, [$applicantId]);
    }

    public static function updateStatus(int $id, string $status, ?string $notes = null): bool
    {
        $validStatuses = ['submitted', 'screening', 'approved', 'rejected', 'withdrawn'];
        if (!in_array($status, $validStatuses, true)) {
            return false;
        }

        return Database::execute(
            "UPDATE rental_applications SET status = ?, notes = COALESCE(?, notes), updated_at = NOW() WHERE id = ?",
            [$status, $notes, $id]
        ) > 0;
    }

    public static function updateScreening(int $id, string $screeningStatus, string $screeningNotes): bool
    {
        $valid = ['pending', 'passed', 'flagged'];
        if (!in_array($screeningStatus, $valid, true)) {
            return false;
        }

        return Database::execute(
            "UPDATE rental_applications SET screening_status = ?, screening_notes = ?, updated_at = NOW() WHERE id = ?",
            [$screeningStatus, $screeningNotes, $id]
        ) > 0;
    }
}
