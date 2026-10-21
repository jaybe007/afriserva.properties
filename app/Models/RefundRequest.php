<?php

namespace App\Models;

use App\Core\Database;

class RefundRequest
{
    public static function create(int $paymentId, int $userId, float $amount, string $reason, string $currency = 'NGN'): int
    {
        $sql = "INSERT INTO refund_requests (payment_id, user_id, amount, currency, reason, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())";

        Database::execute($sql, [$paymentId, $userId, $amount, $currency, trim($reason)]);
        return (int)Database::lastInsertId();
    }

    public static function getAll(): array
    {
        $sql = "SELECT rr.*, p.reference AS payment_reference, p.gateway, u.full_name AS user_name, u.email AS user_email
                FROM refund_requests rr
                JOIN payments p ON p.id = rr.payment_id
                JOIN users u ON u.id = rr.user_id
                ORDER BY FIELD(rr.status, 'pending', 'approved', 'processed', 'rejected'), rr.created_at DESC";

        return Database::query($sql);
    }

    public static function process(int $id, string $status, ?string $adminNotes = null, ?int $reviewerId = null): bool
    {
        $valid = ['approved', 'rejected', 'processed'];
        if (!in_array($status, $valid, true)) {
            return false;
        }

        return Database::execute(
            "UPDATE refund_requests 
             SET status = ?, admin_notes = ?, reviewed_by = ?, reviewed_at = NOW() 
             WHERE id = ?",
            [$status, $adminNotes, $reviewerId, $id]
        ) > 0;
    }
}
