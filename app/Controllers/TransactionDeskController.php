<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Database;
use App\Core\NotificationService;
use App\Models\PropertyTransaction;
use App\Models\CompanyBankAccount;
use App\Models\Subscription;
use App\Services\PricingEngine;
use App\Helpers\UploadHelper;
use App\Helpers\AuditLogger;

class TransactionDeskController extends Controller
{
    /**
     * Admin Transaction Desk Command Center.
     */
    public function index(Request $request, Response $response): void
    {
        $filters = [
            'stage' => $request->query('stage', ''),
            'payment_status' => $request->query('payment_status', ''),
            'officer_id' => $request->query('officer_id', ''),
            'q' => $request->query('q', '')
        ];

        $transactions = PropertyTransaction::getAllForAdmin($filters);

        $stats = [
            'total_transactions' => (int)Database::fetchColumn("SELECT COUNT(*) FROM property_transactions"),
            'pending_review' => (int)Database::fetchColumn("SELECT COUNT(*) FROM property_transactions WHERE stage IN ('enquiry_received', 'under_review')"),
            'awaiting_transfer' => (int)Database::fetchColumn("SELECT COUNT(*) FROM property_transactions WHERE payment_status = 'awaiting_transfer'"),
            'under_verification' => (int)Database::fetchColumn("SELECT COUNT(*) FROM property_transactions WHERE payment_status IN ('evidence_submitted', 'under_verification')"),
            'settlement_pending' => (int)Database::fetchColumn("SELECT COUNT(*) FROM property_transactions WHERE settlement_status = 'pending'"),
            'total_funds_received' => (float)Database::fetchColumn("SELECT COALESCE(SUM(amount_paid), 0) FROM property_transactions WHERE payment_status = 'payment_confirmed'")
        ];

        $officers = Database::query(
            "SELECT u.id, u.full_name, u.email 
             FROM users u
             JOIN user_roles ur ON ur.user_id = u.id
             JOIN roles r ON r.id = ur.role_id
             WHERE r.name IN ('super_admin', 'transaction_officer', 'finance_admin', 'moderator')
             ORDER BY u.full_name ASC"
        );

        $html = $this->render('admin/transactions/index', [
            'title' => 'Afriserva Transaction Desk | Command Center',
            'transactions' => $transactions,
            'stats' => $stats,
            'filters' => $filters,
            'officers' => $officers
        ], 'layouts/admin');

        $response->html($html);
    }

    /**
     * Transaction Details & Management Workspace for Staff.
     */
    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $transaction = PropertyTransaction::findById($id);

        if (!$transaction) {
            flash('danger', 'Transaction record not found.');
            $this->redirect($response, url('admin/transactions'));
            return;
        }

        $timeline = PropertyTransaction::getTimeline($id, true);
        $evidence = PropertyTransaction::getEvidence($id);
        $activeAccount = CompanyBankAccount::getActiveAccount($transaction['currency'] ?? 'NGN');
        $allAccounts = CompanyBankAccount::getAll();

        $officers = Database::query(
            "SELECT u.id, u.full_name, u.email 
             FROM users u
             JOIN user_roles ur ON ur.user_id = u.id
             JOIN roles r ON r.id = ur.role_id
             WHERE r.name IN ('super_admin', 'transaction_officer', 'finance_admin', 'moderator')
             ORDER BY u.full_name ASC"
        );

        // Audit log view of sensitive buyer data
        AuditLogger::log('sensitive_buyer_data_viewed', 'property_transaction', $id, null, [
            'staff_id' => Auth::id(),
            'buyer_id' => $transaction['buyer_id'],
            'reference' => $transaction['transaction_reference']
        ]);

        $html = $this->render('admin/transactions/show', [
            'title' => "Transaction {$transaction['transaction_reference']} | Afriserva Transaction Desk",
            'transaction' => $transaction,
            'timeline' => $timeline,
            'evidence' => $evidence,
            'activeAccount' => $activeAccount,
            'allAccounts' => $allAccounts,
            'officers' => $officers
        ], 'layouts/admin');

        $response->html($html);
    }

    /**
     * Assign Transaction Officer.
     */
    public function assignOfficer(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $officerId = (int)$request->input('officer_id');

        if ($officerId > 0 && PropertyTransaction::assignOfficer($id, $officerId, Auth::id())) {
            flash('success', "Transaction officer assigned successfully.");
        } else {
            flash('danger', "Failed to assign transaction officer.");
        }

        $this->redirect($response, url("admin/transactions/{$id}"));
    }

    /**
     * Update Transaction Stage.
     */
    public function updateStage(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $stage = trim($request->input('stage', ''));
        $notes = trim($request->input('notes', ''));

        if (PropertyTransaction::updateStage($id, $stage, Auth::id(), 'admin', $notes)) {
            flash('success', "Transaction stage updated to " . strtoupper(str_replace('_', ' ', $stage)));
        } else {
            flash('danger', "Invalid stage progression requested.");
        }

        $this->redirect($response, url("admin/transactions/{$id}"));
    }

    /**
     * Issue Official Payment Instructions to Buyer.
     */
    public function issuePaymentInstructions(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $bankAccountId = !empty($request->input('bank_account_id')) ? (int)$request->input('bank_account_id') : null;
        $deadline = !empty($request->input('payment_deadline')) ? trim($request->input('payment_deadline')) : null;

        try {
            PropertyTransaction::issuePaymentInstructions($id, Auth::id(), $bankAccountId, $deadline);

            $txn = PropertyTransaction::findById($id);
            if ($txn) {
                NotificationService::send(
                    (int)$txn['buyer_id'],
                    "Payment Instructions Issued: {$txn['transaction_reference']}",
                    "Official Afriserva company bank account details have been issued for property '{$txn['property_title']}'. Amount: ₦" . number_format((float)$txn['buyer_price'], 2) . ". Please make payment to the verified company account.",
                    'transaction',
                    url("dashboard/transactions/{$id}")
                );
            }

            flash('success', "Official payment instructions successfully generated and delivered to the buyer.");
        } catch (\Throwable $e) {
            flash('danger', "Could not issue payment instructions: " . $e->getMessage());
        }

        $this->redirect($response, url("admin/transactions/{$id}"));
    }

    /**
     * Verify or Reject Manual Bank Transfer Payment Evidence.
     */
    public function verifyPayment(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $decision = trim($request->input('decision', 'confirm')); // 'confirm' or 'reject'
        $reconciliationRef = trim($request->input('reconciliation_reference', ''));
        $notes = trim($request->input('verification_notes', ''));
        $verifiedAmount = !empty($request->input('verified_amount')) ? (float)$request->input('verified_amount') : null;

        if (PropertyTransaction::verifyPayment($id, Auth::id(), $decision, $reconciliationRef, $notes, $verifiedAmount)) {
            $txn = PropertyTransaction::findById($id);
            if ($decision === 'confirm') {
                NotificationService::send(
                    (int)$txn['buyer_id'],
                    "Payment Confirmed: {$txn['transaction_reference']}",
                    "Your bank transfer of ₦" . number_format((float)$txn['amount_paid'], 2) . " has been verified and confirmed by Afriserva.",
                    'payment',
                    url("dashboard/transactions/{$id}")
                );

                // Notify seller without exposing buyer identity!
                NotificationService::send(
                    (int)$txn['seller_id'],
                    "Transaction Funded: {$txn['property_ref']}",
                    "Payment for listing '{$txn['property_title']}' has been confirmed in Afriserva escrow. Settlement status: Pending. Reference: {$txn['transaction_reference']}.",
                    'transaction',
                    url("dashboard/transactions/{$id}")
                );

                flash('success', "Payment confirmed successfully. Reconciliation reference recorded.");
            } else {
                NotificationService::send(
                    (int)$txn['buyer_id'],
                    "Payment Evidence Rejected: {$txn['transaction_reference']}",
                    "Payment evidence submitted could not be reconciled: {$notes}. Please contact Afriserva support or upload an official bank confirmation.",
                    'payment',
                    url("dashboard/transactions/{$id}")
                );
                flash('warning', "Payment evidence rejected. Notification sent to buyer.");
            }
        } else {
            flash('danger', "Failed to record payment verification decision.");
        }

        $this->redirect($response, url("admin/transactions/{$id}"));
    }

    /**
     * Record Seller Settlement.
     */
    public function recordSettlement(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $settlementStatus = trim($request->input('settlement_status', 'settled'));
        $notes = trim($request->input('settlement_notes', ''));

        if (PropertyTransaction::recordSettlement($id, Auth::id(), [
            'settlement_status' => $settlementStatus,
            'settlement_notes' => $notes
        ])) {
            $txn = PropertyTransaction::findById($id);
            if ($txn && $settlementStatus === 'settled') {
                NotificationService::send(
                    (int)$txn['seller_id'],
                    "Proceeds Dispatched: {$txn['property_ref']}",
                    "Your net proceeds of ₦" . number_format((float)$txn['seller_expected_net'], 2) . " have been settled by Afriserva. Reference: {$txn['transaction_reference']}.",
                    'transaction',
                    url("dashboard/transactions/{$id}")
                );
            }
            flash('success', "Seller settlement status updated to " . strtoupper($settlementStatus));
        } else {
            flash('danger', "Failed to update seller settlement status.");
        }

        $this->redirect($response, url("admin/transactions/{$id}"));
    }

    /**
     * Manage Official Company Receiving Bank Accounts.
     */
    public function bankAccounts(Request $request, Response $response): void
    {
        $accounts = CompanyBankAccount::getAll();

        $html = $this->render('admin/bank_accounts/index', [
            'title' => 'Official Afriserva Receiving Accounts | Bank Settings',
            'accounts' => $accounts
        ], 'layouts/admin');

        $response->html($html);
    }

    /**
     * Store new receiving bank account.
     */
    public function storeBankAccount(Request $request, Response $response): void
    {
        $input = $request->all();
        $errors = $this->validate($input, [
            'account_name' => 'required',
            'bank_name' => 'required',
            'account_number' => 'required'
        ]);

        if (!empty($errors)) {
            flash('danger', 'Please provide account name, bank name, and account number.');
            $this->redirect($response, url('admin/bank-accounts'));
            return;
        }

        CompanyBankAccount::create($input, Auth::id());
        flash('success', "New receiving bank account registered.");
        $this->redirect($response, url('admin/bank-accounts'));
    }

    /**
     * Activate receiving bank account.
     */
    public function activateBankAccount(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        if (CompanyBankAccount::setActive($id, Auth::id())) {
            flash('success', "Account activated as primary Afriserva receiving account.");
        } else {
            flash('danger', "Failed to activate bank account.");
        }
        $this->redirect($response, url('admin/bank-accounts'));
    }

    /**
     * Dedicated Commission Settings & Financial Rules.
     */
    public function commissionSettings(Request $request, Response $response): void
    {
        $settings = PricingEngine::getGlobalSettings();
        $defaultPlan = Subscription::getDefaultPlan();
        $allPlans = Subscription::getAllPlans();

        $sellerCount = (int)Database::fetchColumn(
            "SELECT COUNT(DISTINCT u.id) 
             FROM users u
             JOIN user_roles ur ON ur.user_id = u.id
             JOIN roles r ON r.id = ur.role_id
             WHERE r.name IN ('seller', 'agent', 'landlord', 'developer')"
        );

        $commissionPlanUserCount = (int)Database::fetchColumn(
            "SELECT COUNT(DISTINCT s.user_id) 
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             WHERE sp.slug = 'afriserva-commission-plan' AND s.status = 'active'"
        );

        $html = $this->render('admin/commission/settings', [
            'title' => 'Afriserva Commission Plan & Pricing Engine | Admin Settings',
            'settings' => $settings,
            'defaultPlan' => $defaultPlan,
            'allPlans' => $allPlans,
            'sellerCount' => $sellerCount,
            'commissionPlanUserCount' => $commissionPlanUserCount
        ], 'layouts/admin');

        $response->html($html);
    }

    /**
     * Update Commission Settings.
     */
    public function updateCommissionSettings(Request $request, Response $response): void
    {
        $input = $request->all();

        $rate = (float)($input['commission_rate'] ?? 5.00);
        $markup = (float)($input['fixed_markup'] ?? 0.00);
        $mode = $input['commission_mode'] ?? 'percentage';
        $basis = $input['commission_basis'] ?? 'added_to_asking';
        $reqReview = !empty($input['require_manual_review']) ? '1' : '0';
        $enableTransfer = !empty($input['enable_manual_transfer']) ? '1' : '0';

        // Update system settings table
        Database::execute("INSERT INTO system_settings (`key`, `value`, `group_name`, `created_at`) VALUES ('commission_default_rate', ?, 'commission', NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)", [(string)$rate]);
        Database::execute("INSERT INTO system_settings (`key`, `value`, `group_name`, `created_at`) VALUES ('commission_default_fixed_markup', ?, 'commission', NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)", [(string)$markup]);
        Database::execute("INSERT INTO system_settings (`key`, `value`, `group_name`, `created_at`) VALUES ('commission_default_mode', ?, 'commission', NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)", [$mode]);
        Database::execute("INSERT INTO system_settings (`key`, `value`, `group_name`, `created_at`) VALUES ('commission_default_basis', ?, 'commission', NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)", [$basis]);
        Database::execute("INSERT INTO system_settings (`key`, `value`, `group_name`, `created_at`) VALUES ('commission_require_manual_review', ?, 'commission', NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)", [$reqReview]);
        Database::execute("INSERT INTO system_settings (`key`, `value`, `group_name`, `created_at`) VALUES ('commission_enable_manual_transfer', ?, 'commission', NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)", [$enableTransfer]);

        // Also update default plan row in subscription_plans
        Database::execute(
            "UPDATE subscription_plans 
             SET commission_rate = ?, fixed_markup = ?, commission_mode = ?, commission_basis = ?
             WHERE slug = 'afriserva-commission-plan' OR is_default = 1",
            [$rate, $markup, $mode, $basis]
        );

        AuditLogger::log('commission_settings_updated', 'system_settings', 1, null, [
            'admin_id' => Auth::id(),
            'rate' => $rate,
            'markup' => $markup,
            'mode' => $mode,
            'basis' => $basis
        ]);

        flash('success', "Commission and pricing engine settings updated successfully.");
        $this->redirect($response, url('admin/commission-settings'));
    }

    /**
     * Idempotently migrate eligible existing sellers and agents to the zero-subscription commission plan.
     */
    public function migrateUsersToCommissionPlan(Request $request, Response $response): void
    {
        $migratedCount = Subscription::migrateEligibleUsersToCommissionPlan();

        flash('success', "Migration complete. Assigned Afriserva Commission Plan to {$migratedCount} eligible sellers and agents without modifying existing paid subscriptions.");
        $this->redirect($response, url('admin/commission-settings'));
    }

    /**
     * User Dashboard: My Transactions (Role-aware: Buyer vs Seller).
     */
    public function myTransactions(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $isSeller = Auth::hasRole(['seller', 'agent', 'landlord', 'developer', 'agency_admin']);

        $buyerTransactions = PropertyTransaction::getByBuyer($userId);
        $sellerTransactions = $isSeller ? PropertyTransaction::getBySeller($userId) : [];

        $html = $this->render('dashboard/transactions/index', [
            'title' => 'My Property Transactions | Afriserva Real Estate',
            'buyerTransactions' => $buyerTransactions,
            'sellerTransactions' => $sellerTransactions,
            'isSeller' => $isSeller
        ], 'layouts/dashboard');

        $response->html($html);
    }

    /**
     * User Dashboard: View Single Transaction.
     * Enforces strict buyer privacy: Sellers/Agents CANNOT see buyer identity or bank transfer slips.
     */
    public function showTransaction(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $userId = Auth::id();

        $txn = PropertyTransaction::findById($id);
        if (!$txn) {
            flash('danger', 'Transaction not found.');
            $this->redirect($response, url('dashboard/transactions'));
            return;
        }

        $isBuyer = ((int)$txn['buyer_id'] === $userId);
        $isSeller = ((int)$txn['seller_id'] === $userId);
        $isAgent = (!empty($txn['agent_id']) && (int)$txn['agent_id'] === $userId);
        $isStaff = Auth::hasRole(['super_admin', 'transaction_officer', 'finance_admin', 'moderator']);

        if (!$isBuyer && !$isSeller && !$isAgent && !$isStaff) {
            flash('danger', 'Unauthorized access to this transaction record.');
            $this->redirect($response, url('dashboard/transactions'));
            return;
        }

        // STRICT PRIVACY: If viewed by Seller or Agent, sanitize buyer details!
        if (!$isBuyer && !$isStaff) {
            $txn['buyer_name'] = 'Verified Afriserva Buyer';
            $txn['buyer_email'] = '***@protected.afriserva.com';
            $txn['buyer_phone'] = '***-***-****';
            $txn['buyer_notes'] = null;
        }

        $timeline = PropertyTransaction::getTimeline($id, $isStaff);
        $evidence = ($isBuyer || $isStaff) ? PropertyTransaction::getEvidence($id) : [];
        $activeAccount = CompanyBankAccount::getActiveAccount($txn['currency'] ?? 'NGN');

        $html = $this->render('dashboard/transactions/show', [
            'title' => "Transaction {$txn['transaction_reference']} | Afriserva Real Estate",
            'transaction' => $txn,
            'timeline' => $timeline,
            'evidence' => $evidence,
            'isBuyer' => $isBuyer,
            'isSeller' => $isSeller || $isAgent,
            'isStaff' => $isStaff,
            'activeAccount' => $activeAccount
        ], 'layouts/dashboard');

        $response->html($html);
    }

    /**
     * Buyer initiates formal purchase / offer from property page.
     */
    public function initiateTransaction(Request $request, Response $response, array $params): void
    {
        $propId = (int)($params['id'] ?? 0);
        $buyerId = Auth::id();

        if (!$buyerId) {
            flash('warning', 'Please sign in or register to initiate a property transaction.');
            $this->redirect($response, url('login'));
            return;
        }

        try {
            $txnId = PropertyTransaction::create([
                'property_id' => $propId,
                'buyer_id' => $buyerId,
                'stage' => 'offer_submitted',
                'buyer_notes' => trim($request->input('notes', 'Buyer requested formal transaction through Afriserva.'))
            ]);

            flash('success', 'Your transaction request has been registered with the Afriserva Transaction Desk! A dedicated Transaction Officer will review and provide official payment instructions.');
            $this->redirect($response, url("dashboard/transactions/{$txnId}"));
        } catch (\Throwable $e) {
            flash('danger', 'Could not initiate transaction: ' . $e->getMessage());
            $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('/'));
        }
    }

    /**
     * Buyer submits manual bank transfer payment evidence.
     */
    public function submitEvidence(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $buyerId = Auth::id();
        $input = $request->all();

        $receiptUrl = '';
        $receiptName = '';

        if (!empty($_FILES['receipt_file']['tmp_name'])) {
            $file = $_FILES['receipt_file'];
            $maxBytes = 10 * 1024 * 1024; // 10MB
            if ($file['size'] > $maxBytes) {
                flash('danger', 'Uploaded receipt exceeds 10MB.');
                $this->redirect($response, url("dashboard/transactions/{$id}"));
                return;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
            if (!in_array($ext, $allowed)) {
                flash('danger', 'Invalid receipt format. Only PDF, JPG, and PNG are accepted.');
                $this->redirect($response, url("dashboard/transactions/{$id}"));
                return;
            }

            $baseDir = dirname(__DIR__, 2);
            $targetDir = $baseDir . '/storage/documents/receipts';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $safeFileName = 'receipt_' . $id . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destPath = $targetDir . '/' . $safeFileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $receiptUrl = 'storage/documents/receipts/' . $safeFileName;
                $receiptName = basename($file['name']);
            }
        } elseif (!empty($input['receipt_file_url'])) {
            $receiptUrl = trim($input['receipt_file_url']);
            $receiptName = basename($receiptUrl);
        }

        if (empty($receiptUrl)) {
            flash('danger', 'Please upload a valid transfer receipt file (PDF, JPG, or PNG).');
            $this->redirect($response, url("dashboard/transactions/{$id}"));
            return;
        }

        try {
            PropertyTransaction::submitPaymentEvidence($id, $buyerId, [
                'amount_paid' => (float)($input['amount_paid'] ?? 0),
                'payment_date' => $input['payment_date'] ?? date('Y-m-d'),
                'payment_channel' => $input['payment_channel'] ?? 'bank_transfer',
                'reference_or_session_id' => trim($input['reference_or_session_id'] ?? ''),
                'receipt_file_url' => $receiptUrl,
                'receipt_original_name' => $receiptName,
                'buyer_notes' => trim($input['buyer_notes'] ?? '')
            ]);

            flash('success', 'Transfer confirmation uploaded successfully. Your payment is now Under Administrative Verification by Afriserva.');
        } catch (\Throwable $e) {
            flash('danger', 'Error submitting payment evidence: ' . $e->getMessage());
        }

        $this->redirect($response, url("dashboard/transactions/{$id}"));
    }
}
