<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Services\PricingEngine;
use App\Helpers\AuditLogger;

class PropertyTransaction
{
    /**
     * Generate unique non-sensitive transaction reference code.
     */
    public static function generateReference(): string
    {
        $prefix = 'AFR-TXN-' . date('Ym') . '-';
        do {
            $ref = $prefix . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $exists = Database::fetchColumn("SELECT COUNT(*) FROM property_transactions WHERE transaction_reference = ?", [$ref]);
        } while ((int)$exists > 0);

        return $ref;
    }

    /**
     * Initiate a new property transaction from an enquiry, offer, or purchase intent.
     */
    public static function create(array $data): int
    {
        $propertyId = (int)$data['property_id'];
        $buyerId = (int)$data['buyer_id'];

        $property = Database::fetch("SELECT * FROM properties WHERE id = ?", [$propertyId]);
        if (!$property) {
            throw new \Exception("Property not found.");
        }

        $sellerId = (int)$property['user_id'];
        $agentId = !empty($property['agency_id']) ? (int)$property['agency_id'] : null;

        // Calculate pricing snapshot
        $askingPrice = (float)($property['asking_price'] ?? $property['price']);
        $pricing = PricingEngine::calculate($askingPrice, [
            'commission_rate' => (float)($property['commission_rate'] ?? 5.00),
            'fixed_markup' => (float)($property['fixed_markup'] ?? 0.00),
            'commission_mode' => $property['commission_mode'] ?? 'percentage',
            'commission_basis' => $property['commission_calculation_basis'] ?? 'added_to_asking'
        ]);

        $reference = self::generateReference();
        $initialStage = $data['stage'] ?? 'enquiry_received';
        $paymentStatus = $data['payment_status'] ?? 'not_payable';

        $sql = "INSERT INTO property_transactions (
            transaction_reference, property_id, buyer_id, seller_id, agent_id,
            enquiry_id, stage, payment_status, settlement_status, currency,
            asking_price, buyer_price, commission_rate, commission_amount, fixed_markup,
            seller_expected_net, amount_paid, pricing_snapshot, admin_notes, buyer_notes,
            created_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, 'not_applicable', 'NGN',
            ?, ?, ?, ?, ?,
            ?, 0.00, ?, ?, ?,
            NOW()
        )";

        Database::execute($sql, [
            $reference,
            $propertyId,
            $buyerId,
            $sellerId,
            $agentId,
            $data['enquiry_id'] ?? null,
            $initialStage,
            $paymentStatus,
            $pricing['asking_price'],
            $pricing['buyer_price'],
            $pricing['commission_rate'],
            $pricing['commission_amount'],
            $pricing['fixed_markup'],
            $pricing['seller_expected_net'],
            $pricing['snapshot_json'],
            trim($data['admin_notes'] ?? ''),
            trim($data['buyer_notes'] ?? '')
        ]);

        $transactionId = (int)Database::lastInsertId();

        // Record initial timeline event
        self::addTimelineEvent(
            $transactionId,
            $buyerId,
            'buyer',
            'transaction_initiated',
            null,
            $initialStage,
            'Transaction Record Initialized',
            "Transaction reference {$reference} registered for '{$property['title']}'."
        );

        AuditLogger::log('property_transaction_created', 'property_transaction', $transactionId, null, [
            'reference' => $reference,
            'property_id' => $propertyId,
            'buyer_price' => $pricing['buyer_price']
        ]);

        return $transactionId;
    }

    /**
     * Find transaction by ID.
     */
    public static function findById(int $id): ?array
    {
        $sql = "SELECT pt.*, 
                       p.title AS property_title, p.slug AS property_slug, p.reference_code AS property_ref,
                       p.address AS property_address,
                       b.full_name AS buyer_name, b.email AS buyer_email, b.phone AS buyer_phone,
                       s.full_name AS seller_name, s.email AS seller_email, s.phone AS seller_phone,
                       off.full_name AS officer_name, off.email AS officer_email,
                       v.full_name AS verified_by_name,
                       set_user.full_name AS settled_by_name
                FROM property_transactions pt
                JOIN properties p ON p.id = pt.property_id
                JOIN users b ON b.id = pt.buyer_id
                JOIN users s ON s.id = pt.seller_id
                LEFT JOIN users off ON off.id = pt.assigned_officer_id
                LEFT JOIN users v ON v.id = pt.verified_by
                LEFT JOIN users set_user ON set_user.id = pt.settled_by
                WHERE pt.id = ? 
                LIMIT 1";

        return Database::fetch($sql, [$id]);
    }

    /**
     * Find transaction by reference code.
     */
    public static function findByReference(string $reference): ?array
    {
        $id = (int)Database::fetchColumn(
            "SELECT id FROM property_transactions WHERE transaction_reference = ? LIMIT 1",
            [trim($reference)]
        );
        return $id > 0 ? self::findById($id) : null;
    }

    /**
     * Query transactions for Admin / Staff Transaction Desk.
     */
    public static function getAllForAdmin(array $filters = []): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['stage'])) {
            $where[] = "pt.stage = ?";
            $params[] = $filters['stage'];
        }

        if (!empty($filters['payment_status'])) {
            $where[] = "pt.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['officer_id'])) {
            $where[] = "pt.assigned_officer_id = ?";
            $params[] = (int)$filters['officer_id'];
        }

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $where[] = "(pt.transaction_reference LIKE ? OR p.title LIKE ? OR b.full_name LIKE ? OR b.email LIKE ? OR s.full_name LIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT pt.*, 
                       p.title AS property_title, p.slug AS property_slug, p.reference_code AS property_ref,
                       b.full_name AS buyer_name, b.email AS buyer_email, b.phone AS buyer_phone,
                       s.full_name AS seller_name,
                       off.full_name AS officer_name
                FROM property_transactions pt
                JOIN properties p ON p.id = pt.property_id
                JOIN users b ON b.id = pt.buyer_id
                JOIN users s ON s.id = pt.seller_id
                LEFT JOIN users off ON off.id = pt.assigned_officer_id
                WHERE {$whereSql}
                ORDER BY pt.created_at DESC";

        return Database::query($sql, $params);
    }

    /**
     * Query transactions for a Buyer (accessible ONLY to that buyer).
     */
    public static function getByBuyer(int $buyerId): array
    {
        $sql = "SELECT pt.id, pt.transaction_reference, pt.property_id, pt.stage, pt.payment_status,
                       pt.buyer_price, pt.currency, pt.amount_paid, pt.bank_account_snapshot,
                       pt.payment_deadline, pt.buyer_notes, pt.created_at, pt.updated_at,
                       p.title AS property_title, p.slug AS property_slug, p.reference_code AS property_ref,
                       p.address AS property_address,
                       (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY is_cover DESC LIMIT 1) AS cover_image
                FROM property_transactions pt
                JOIN properties p ON p.id = pt.property_id
                WHERE pt.buyer_id = ?
                ORDER BY pt.created_at DESC";

        return Database::query($sql, [$buyerId]);
    }

    /**
     * Query transactions for a Seller (STRICT PRIVACY: zero buyer contact details).
     */
    public static function getBySeller(int $sellerId): array
    {
        $sql = "SELECT pt.id, pt.transaction_reference, pt.property_id, pt.stage, pt.payment_status,
                       pt.settlement_status, pt.asking_price, pt.buyer_price, pt.commission_rate,
                       pt.commission_amount, pt.fixed_markup, pt.seller_expected_net, pt.currency,
                       pt.created_at, pt.updated_at,
                       p.title AS property_title, p.slug AS property_slug, p.reference_code AS property_ref
                FROM property_transactions pt
                JOIN properties p ON p.id = pt.property_id
                WHERE pt.seller_id = ?
                ORDER BY pt.created_at DESC";

        return Database::query($sql, [$sellerId]);
    }

    /**
     * Query transactions for an Agent (STRICT PRIVACY: zero buyer contact details).
     */
    public static function getByAgent(int $agentId): array
    {
        $sql = "SELECT pt.id, pt.transaction_reference, pt.property_id, pt.stage, pt.payment_status,
                       pt.settlement_status, pt.asking_price, pt.buyer_price, pt.commission_rate,
                       pt.commission_amount, pt.fixed_markup, pt.seller_expected_net, pt.currency,
                       pt.created_at, pt.updated_at,
                       p.title AS property_title, p.slug AS property_slug, p.reference_code AS property_ref
                FROM property_transactions pt
                JOIN properties p ON p.id = pt.property_id
                WHERE pt.agent_id = ?
                ORDER BY pt.created_at DESC";

        return Database::query($sql, [$agentId]);
    }

    /**
     * Assign an authorized Transaction Officer to manage a transaction.
     */
    public static function assignOfficer(int $transactionId, int $officerId, int $actorId): bool
    {
        $officer = Database::fetch("SELECT full_name, email FROM users WHERE id = ?", [$officerId]);
        if (!$officer) return false;

        $txn = self::findById($transactionId);
        if (!$txn) return false;

        Database::execute(
            "UPDATE property_transactions SET assigned_officer_id = ?, updated_at = NOW() WHERE id = ?",
            [$officerId, $transactionId]
        );

        self::addTimelineEvent(
            $transactionId,
            $actorId,
            'admin',
            'officer_assigned',
            $txn['stage'],
            $txn['stage'],
            'Transaction Officer Assigned',
            "Assigned to {$officer['full_name']} for due diligence and buyer coordination.",
            null,
            true
        );

        AuditLogger::log('transaction_officer_assigned', 'property_transaction', $transactionId, null, [
            'officer_id' => $officerId,
            'officer_name' => $officer['full_name']
        ]);

        return true;
    }

    /**
     * Update the transaction lifecycle stage with validation and timeline logging.
     */
    public static function updateStage(int $transactionId, string $newStage, int $actorId, string $actorRole, ?string $notes = null): bool
    {
        $txn = self::findById($transactionId);
        if (!$txn) return false;

        $validStages = [
            'enquiry_received', 'under_review', 'viewing_requested', 'viewing_scheduled',
            'offer_submitted', 'offer_accepted', 'payment_instructions_issued',
            'payment_evidence_submitted', 'payment_verified', 'seller_settlement_pending',
            'seller_settled', 'completed', 'cancelled', 'disputed', 'refunded'
        ];

        if (!in_array($newStage, $validStages, true)) {
            return false;
        }

        $oldStage = $txn['stage'];

        Database::execute(
            "UPDATE property_transactions SET stage = ?, updated_at = NOW() WHERE id = ?",
            [$newStage, $transactionId]
        );

        $stageLabels = [
            'enquiry_received' => 'Enquiry Received',
            'under_review' => 'Under Review by Afriserva',
            'viewing_requested' => 'Viewing Tour Requested',
            'viewing_scheduled' => 'Viewing Tour Scheduled',
            'offer_submitted' => 'Offer Submitted',
            'offer_accepted' => 'Offer Accepted',
            'payment_instructions_issued' => 'Official Payment Instructions Issued',
            'payment_evidence_submitted' => 'Payment Evidence Submitted',
            'payment_verified' => 'Payment Verified & Confirmed',
            'seller_settlement_pending' => 'Seller Settlement Pending',
            'seller_settled' => 'Seller Settlement Dispatched',
            'completed' => 'Transaction Completed',
            'cancelled' => 'Transaction Cancelled',
            'disputed' => 'Transaction Disputed',
            'refunded' => 'Transaction Refunded'
        ];

        $title = "Stage Transition: " . ($stageLabels[$newStage] ?? ucfirst($newStage));
        $desc = $notes ?: "Transaction progressed from {$stageLabels[$oldStage]} to {$stageLabels[$newStage]}.";

        self::addTimelineEvent(
            $transactionId,
            $actorId,
            $actorRole,
            'stage_change',
            $oldStage,
            $newStage,
            $title,
            $desc
        );

        AuditLogger::log('transaction_stage_updated', 'property_transaction', $transactionId, null, [
            'from_stage' => $oldStage,
            'to_stage' => $newStage,
            'notes' => $notes
        ]);

        return true;
    }

    /**
     * Issue official Afriserva payment instructions to the buyer.
     * Captures an immutable snapshot of the active company receiving account.
     */
    public static function issuePaymentInstructions(int $transactionId, int $actorId, ?int $bankAccountId = null, ?string $deadline = null): bool
    {
        $txn = self::findById($transactionId);
        if (!$txn) return false;

        $bankAccount = $bankAccountId 
            ? CompanyBankAccount::findById($bankAccountId)
            : CompanyBankAccount::getActiveAccount($txn['currency'] ?? 'NGN');

        if (!$bankAccount) {
            throw new \Exception("No active approved Afriserva receiving account is configured.");
        }

        $bankSnapshot = json_encode([
            'account_id' => $bankAccount['id'],
            'account_name' => $bankAccount['account_name'],
            'bank_name' => $bankAccount['bank_name'],
            'account_number' => $bankAccount['account_number'],
            'account_type' => $bankAccount['account_type'],
            'currency' => $bankAccount['currency'],
            'payment_instructions' => $bankAccount['payment_instructions'],
            'issued_at' => date('Y-m-d H:i:s'),
            'transaction_reference' => $txn['transaction_reference'],
            'exact_amount_payable' => (float)$txn['buyer_price']
        ], JSON_UNESCAPED_SLASHES);

        Database::execute(
            "UPDATE property_transactions 
             SET stage = 'payment_instructions_issued',
                 payment_status = 'awaiting_transfer',
                 bank_account_snapshot = ?,
                 payment_deadline = ?,
                 updated_at = NOW()
             WHERE id = ?",
            [$bankSnapshot, $deadline, $transactionId]
        );

        self::addTimelineEvent(
            $transactionId,
            $actorId,
            'admin',
            'payment_instructions_issued',
            $txn['stage'],
            'payment_instructions_issued',
            'Official Payment Instructions Issued',
            "Approved company receiving account details issued to buyer. Amount payable: ₦" . number_format((float)$txn['buyer_price'], 2) . ". Reference: {$txn['transaction_reference']}."
        );

        AuditLogger::log('payment_instructions_issued', 'property_transaction', $transactionId, null, [
            'bank_name' => $bankAccount['bank_name'],
            'amount_payable' => $txn['buyer_price']
        ]);

        return true;
    }

    /**
     * Submit buyer payment transfer evidence.
     * Important: Uploading a receipt DOES NOT automatically confirm payment!
     */
    public static function submitPaymentEvidence(int $transactionId, int $buyerId, array $data): int
    {
        $txn = self::findById($transactionId);
        if (!$txn) {
            throw new \Exception("Transaction not found.");
        }

        if ((int)$txn['buyer_id'] !== $buyerId) {
            throw new \Exception("Unauthorized access to this transaction.");
        }

        $amount = (float)($data['amount_paid'] ?? $txn['buyer_price']);
        $paymentDate = $data['payment_date'] ?? date('Y-m-d');
        $channel = trim($data['payment_channel'] ?? 'bank_transfer');
        $sessionRef = trim($data['reference_or_session_id'] ?? '');
        $receiptUrl = trim($data['receipt_file_url'] ?? '');
        $originalName = trim($data['receipt_original_name'] ?? 'receipt');
        $buyerNotes = trim($data['buyer_notes'] ?? '');

        $sql = "INSERT INTO transaction_payment_evidence (
            transaction_id, buyer_id, amount_paid, payment_date, payment_channel,
            reference_or_session_id, receipt_file_url, receipt_original_name, buyer_notes,
            review_status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

        Database::execute($sql, [
            $transactionId,
            $buyerId,
            $amount,
            $paymentDate,
            $channel,
            $sessionRef,
            $receiptUrl,
            $originalName,
            $buyerNotes
        ]);

        $evidenceId = (int)Database::lastInsertId();

        // Update transaction status to under verification
        Database::execute(
            "UPDATE property_transactions 
             SET stage = 'payment_evidence_submitted',
                 payment_status = 'evidence_submitted',
                 updated_at = NOW()
             WHERE id = ?",
            [$transactionId]
        );

        self::addTimelineEvent(
            $transactionId,
            $buyerId,
            'buyer',
            'payment_evidence_submitted',
            $txn['stage'],
            'payment_evidence_submitted',
            'Bank Transfer Evidence Submitted',
            "Buyer uploaded payment confirmation for ₦" . number_format($amount, 2) . ". Status is currently Awaiting Administrative Verification."
        );

        AuditLogger::log('payment_evidence_submitted', 'property_transaction', $transactionId, null, [
            'buyer_id' => $buyerId,
            'evidence_id' => $evidenceId,
            'amount' => $amount,
            'session_ref' => $sessionRef
        ]);

        return $evidenceId;
    }

    /**
     * Admin payment verification decision.
     * Can confirm or reject payment evidence after checking bank records.
     */
    public static function verifyPayment(
        int $transactionId, 
        int $officerId, 
        string $decision, 
        ?string $reconciliationRef = null, 
        ?string $notes = null,
        ?float $verifiedAmount = null
    ): bool {
        $txn = self::findById($transactionId);
        if (!$txn) return false;

        if ($decision === 'confirm') {
            $amount = $verifiedAmount !== null ? $verifiedAmount : (float)$txn['buyer_price'];

            Database::execute(
                "UPDATE property_transactions 
                 SET stage = 'payment_verified',
                     payment_status = 'payment_confirmed',
                     settlement_status = 'pending',
                     amount_paid = ?,
                     verified_by = ?,
                     verified_at = NOW(),
                     reconciliation_reference = ?,
                     admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n[Payment Verified: ', NOW(), '] ', ?),
                     updated_at = NOW()
                 WHERE id = ?",
                [$amount, $officerId, $reconciliationRef, $notes, $transactionId]
            );

            // Mark pending evidence as verified
            Database::execute(
                "UPDATE transaction_payment_evidence 
                 SET review_status = 'verified', reviewed_by = ?, reviewed_at = NOW(), admin_feedback = ?
                 WHERE transaction_id = ? AND review_status = 'pending'",
                [$officerId, $notes, $transactionId]
            );

            self::addTimelineEvent(
                $transactionId,
                $officerId,
                'admin',
                'payment_confirmed',
                $txn['stage'],
                'payment_verified',
                'Payment Verified & Confirmed',
                "Authorized Officer confirmed receipt of ₦" . number_format($amount, 2) . " into company account. Reconciliation Reference: " . ($reconciliationRef ?: 'N/A') . "."
            );

            AuditLogger::log('payment_confirmed', 'property_transaction', $transactionId, null, [
                'officer_id' => $officerId,
                'verified_amount' => $amount,
                'reconciliation_ref' => $reconciliationRef
            ]);

            return true;
        } elseif ($decision === 'reject') {
            Database::execute(
                "UPDATE property_transactions 
                 SET payment_status = 'payment_rejected',
                     admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n[Payment Rejected: ', NOW(), '] ', ?),
                     updated_at = NOW()
                 WHERE id = ?",
                [$notes, $transactionId]
            );

            Database::execute(
                "UPDATE transaction_payment_evidence 
                 SET review_status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), admin_feedback = ?
                 WHERE transaction_id = ? AND review_status = 'pending'",
                [$officerId, $notes, $transactionId]
            );

            self::addTimelineEvent(
                $transactionId,
                $officerId,
                'admin',
                'payment_rejected',
                $txn['stage'],
                $txn['stage'],
                'Payment Evidence Rejected',
                "Payment evidence could not be reconciled. Reason: " . ($notes ?: 'Bank statement mismatch.')
            );

            AuditLogger::log('payment_rejected', 'property_transaction', $transactionId, null, [
                'officer_id' => $officerId,
                'rejection_reason' => $notes
            ]);

            return true;
        }

        return false;
    }

    /**
     * Record settlement payout to seller.
     */
    public static function recordSettlement(int $transactionId, int $officerId, array $data): bool
    {
        $txn = self::findById($transactionId);
        if (!$txn) return false;

        $settlementStatus = $data['settlement_status'] ?? 'settled';
        $settlementNotes = trim($data['settlement_notes'] ?? '');

        Database::execute(
            "UPDATE property_transactions 
             SET settlement_status = ?,
                 stage = CASE WHEN ? = 'settled' THEN 'seller_settled' ELSE stage END,
                 settled_by = ?,
                 settled_at = NOW(),
                 admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n[Seller Settlement: ', NOW(), '] ', ?),
                 updated_at = NOW()
             WHERE id = ?",
            [$settlementStatus, $settlementStatus, $officerId, $settlementNotes, $transactionId]
        );

        self::addTimelineEvent(
            $transactionId,
            $officerId,
            'admin',
            'seller_settled',
            $txn['stage'],
            'seller_settled',
            'Seller Proceeds Dispatched',
            "Expected net proceeds of ₦" . number_format((float)$txn['seller_expected_net'], 2) . " settled. Status: " . ucfirst($settlementStatus) . ". Reference / Notes: " . $settlementNotes
        );

        AuditLogger::log('seller_settlement_recorded', 'property_transaction', $transactionId, null, [
            'officer_id' => $officerId,
            'settlement_status' => $settlementStatus,
            'net_proceeds' => $txn['seller_expected_net']
        ]);

        return true;
    }

    /**
     * Add an immutable timeline audit event for a transaction.
     */
    public static function addTimelineEvent(
        int $transactionId,
        ?int $actorId,
        string $actorRole,
        string $action,
        ?string $fromStage,
        ?string $toStage,
        string $title,
        ?string $description = null,
        ?array $metadata = null,
        bool $isInternalOnly = false
    ): int {
        $sql = "INSERT INTO transaction_timeline (
            transaction_id, actor_id, actor_role, action, from_stage, to_stage,
            title, description, metadata, is_internal_only, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        Database::execute($sql, [
            $transactionId,
            $actorId,
            $actorRole,
            $action,
            $fromStage,
            $toStage,
            trim($title),
            trim($description ?? ''),
            $metadata ? json_encode($metadata, JSON_UNESCAPED_SLASHES) : null,
            $isInternalOnly ? 1 : 0
        ]);

        return (int)Database::lastInsertId();
    }

    /**
     * Retrieve chronological audit timeline for a transaction.
     */
    public static function getTimeline(int $transactionId, bool $includeInternal = false): array
    {
        $where = "tt.transaction_id = ?";
        if (!$includeInternal) {
            $where .= " AND tt.is_internal_only = 0";
        }

        $sql = "SELECT tt.*, u.full_name AS actor_name 
                FROM transaction_timeline tt
                LEFT JOIN users u ON u.id = tt.actor_id
                WHERE {$where}
                ORDER BY tt.created_at ASC";

        return Database::query($sql, [$transactionId]);
    }

    /**
     * Get all payment evidence submissions for a transaction.
     */
    public static function getEvidence(int $transactionId): array
    {
        $sql = "SELECT tpe.*, u.full_name AS buyer_name, rev.full_name AS reviewer_name
                FROM transaction_payment_evidence tpe
                JOIN users u ON u.id = tpe.buyer_id
                LEFT JOIN users rev ON rev.id = tpe.reviewed_by
                WHERE tpe.transaction_id = ?
                ORDER BY tpe.created_at DESC";

        return Database::query($sql, [$transactionId]);
    }
}
