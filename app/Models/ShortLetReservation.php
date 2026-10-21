<?php

namespace App\Models;

use App\Core\Database;
use DateTime;
use Exception;

class ShortLetReservation
{
    public static function isAvailable(int $propertyId, string $checkIn, string $checkOut): bool
    {
        $sql = "SELECT COUNT(*) FROM short_let_reservations
                WHERE property_id = ?
                  AND status IN ('confirmed', 'checked_in')
                  AND (
                      (check_in_date <= ? AND check_out_date > ?) OR
                      (check_in_date < ? AND check_out_date >= ?) OR
                      (check_in_date >= ? AND check_out_date <= ?)
                  )";

        $count = (int)Database::fetchColumn($sql, [
            $propertyId,
            $checkIn, $checkIn,
            $checkOut, $checkOut,
            $checkIn, $checkOut
        ]);

        return $count === 0;
    }

    public static function create(array $data): int
    {
        $checkIn = new DateTime($data['check_in_date']);
        $checkOut = new DateTime($data['check_out_date']);
        $diff = $checkIn->diff($checkOut);
        $totalNights = max(1, $diff->days);

        if (!self::isAvailable((int)$data['property_id'], $data['check_in_date'], $data['check_out_date'])) {
            throw new Exception("The selected dates are already booked. Please choose another date range.");
        }

        $code = 'RES-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        $nightlyRate = (float)$data['nightly_rate'];
        $subtotal = $nightlyRate * $totalNights;
        $securityDeposit = (float)($data['security_deposit'] ?? 50000.00);
        $totalAmount = $subtotal + $securityDeposit;

        $sql = "INSERT INTO short_let_reservations (
                    reservation_code, property_id, user_id, check_in_date, check_out_date,
                    guests_count, total_nights, nightly_rate, subtotal, security_deposit,
                    total_amount, currency, status, guest_name, guest_email, guest_phone,
                    special_requests, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, 'NGN', 'confirmed', ?, ?, ?,
                    ?, NOW()
                )";

        Database::execute($sql, [
            $code,
            (int)$data['property_id'],
            (int)$data['user_id'],
            $data['check_in_date'],
            $data['check_out_date'],
            (int)($data['guests_count'] ?? 1),
            $totalNights,
            $nightlyRate,
            $subtotal,
            $securityDeposit,
            $totalAmount,
            trim($data['guest_name']),
            trim($data['guest_email']),
            trim($data['guest_phone']),
            trim($data['special_requests'] ?? '')
        ]);

        return (int)Database::lastInsertId();
    }

    public static function getByUser(int $userId): array
    {
        $sql = "SELECT r.*, p.title AS property_title, p.slug AS property_slug, p.address
                FROM short_let_reservations r
                JOIN properties p ON p.id = r.property_id
                WHERE r.user_id = ?
                ORDER BY r.check_in_date DESC";

        return Database::query($sql, [$userId]);
    }
}
