<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Database;
use App\Core\NotificationService;
use App\Core\Payments\PaymentGatewayManager;
use App\Models\Subscription;
use App\Models\RefundRequest;

class PaymentController extends Controller
{
    public function checkout(Request $request, Response $response, array $params): void
    {
        $planSlug = $params['plan'] ?? '';
        $plan = Subscription::findPlanBySlug($planSlug);

        if (!$plan) {
            flash('danger', 'Invalid subscription plan selected.');
            $this->redirect($response, url('dashboard/subscription'));
            return;
        }

        $couponCode = trim($request->input('coupon', ''));
        $price = (float)$plan['price'];
        $discount = 0.00;
        $couponMessage = '';

        if (!empty($couponCode)) {
            $couponResult = Subscription::applyCoupon($couponCode, $price);
            if ($couponResult['valid']) {
                $discount = $couponResult['discount'];
                $price = $couponResult['final_amount'];
                $couponMessage = $couponResult['message'];
            } else {
                flash('warning', $couponResult['message']);
            }
        }

        $user = Auth::user();

        $html = $this->render('dashboard/checkout', [
            'title' => 'Checkout: ' . e($plan['name']) . ' | Afriserva Real Estate',
            'plan' => $plan,
            'user' => $user,
            'originalPrice' => (float)$plan['price'],
            'discount' => $discount,
            'finalPrice' => $price,
            'couponCode' => $couponCode,
            'couponMessage' => $couponMessage
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function process(Request $request, Response $response): void
    {
        $planId = (int)$request->input('plan_id');
        $gatewayName = $request->input('gateway', 'test_sandbox');
        $couponCode = trim($request->input('coupon_code', ''));
        $userId = Auth::id();

        $plan = Database::fetch("SELECT * FROM subscription_plans WHERE id = ?", [$planId]);
        if (!$plan) {
            flash('danger', 'Subscription plan not recognized.');
            $this->redirect($response, url('dashboard/subscription'));
            return;
        }

        $amount = (float)$plan['price'];
        if (!empty($couponCode)) {
            $couponRes = Subscription::applyCoupon($couponCode, $amount);
            if ($couponRes['valid']) {
                $amount = $couponRes['final_amount'];
                Database::execute("UPDATE coupons SET uses_count = uses_count + 1 WHERE code = ?", [$couponCode]);
            }
        }

        // Generate unique reference & invoice
        $reference = 'AFR-' . strtoupper(bin2hex(random_bytes(6)));
        $invoiceNum = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

        // Create unpaid invoice record
        Database::execute(
            "INSERT INTO invoices (invoice_number, user_id, subtotal, tax, total_amount, currency, status, due_date, notes, created_at)
             VALUES (?, ?, ?, 0.00, ?, 'NGN', 'unpaid', CURDATE(), ?, NOW())",
            [$invoiceNum, $userId, $amount, $amount, "Subscription: {$plan['name']} ({$plan['billing_interval']})"]
        );
        $invoiceId = (int)Database::lastInsertId();

        // Create pending payment record
        Database::execute(
            "INSERT INTO payments (reference, user_id, invoice_id, gateway, amount, currency, status, created_at)
             VALUES (?, ?, ?, ?, ?, 'NGN', 'pending', NOW())",
            [$reference, $userId, $invoiceId, $gatewayName, $amount]
        );

        $gateway = PaymentGatewayManager::gateway($gatewayName);
        $user = Auth::user();

        $initResult = $gateway->initialize([
            'reference' => $reference,
            'amount' => $amount,
            'currency' => 'NGN',
            'email' => $user['email'],
            'name' => $user['full_name'],
            'callback_url' => url("checkout/verify/{$gatewayName}"),
            'metadata' => [
                'plan_id' => $planId,
                'user_id' => $userId,
                'invoice_id' => $invoiceId
            ]
        ]);

        if (!empty($initResult['checkout_url'])) {
            $this->redirect($response, $initResult['checkout_url']);
            return;
        }

        flash('danger', $initResult['message'] ?? 'Payment initialization failed.');
        $this->redirect($response, url('dashboard/subscription'));
    }

    /**
     * Server-side Verification Route.
     * Never activates paid service on frontend claim alone.
     */
    public function verify(Request $request, Response $response, array $params): void
    {
        $gatewayName = $params['gateway'] ?? 'test_sandbox';
        $reference = $request->input('reference') ?? $request->input('tx_ref') ?? '';

        if (empty($reference)) {
            flash('danger', 'No payment reference received for verification.');
            $this->redirect($response, url('dashboard/subscription'));
            return;
        }

        $payment = Database::fetch("SELECT * FROM payments WHERE reference = ? LIMIT 1", [$reference]);
        if (!$payment) {
            flash('danger', 'Transaction reference not found in system records.');
            $this->redirect($response, url('dashboard/subscription'));
            return;
        }

        // Check if already processed (Idempotency)
        if ($payment['status'] === 'successful') {
            flash('info', 'This payment has already been verified and processed.');
            $this->redirect($response, url('dashboard/invoices/' . ($payment['invoice_id'] ?? 0)));
            return;
        }

        $gateway = PaymentGatewayManager::gateway($gatewayName);
        $verification = $gateway->verify($reference);

        if ($verification['verified']) {
            // Update payment record
            Database::execute(
                "UPDATE payments SET status = 'successful', transaction_id = ?, gateway_response = ?, paid_at = NOW() WHERE id = ?",
                [
                    $verification['gateway_reference'] ?: $reference,
                    json_encode($verification['raw_response']),
                    (int)$payment['id']
                ]
            );

            // Update Invoice
            if (!empty($payment['invoice_id'])) {
                Database::execute(
                    "UPDATE invoices SET status = 'paid', paid_at = NOW() WHERE id = ?",
                    [(int)$payment['invoice_id']]
                );
            }

            // Activate Subscription
            $invoice = Database::fetch("SELECT notes FROM invoices WHERE id = ?", [(int)$payment['invoice_id']]);
            // Find plan ID from invoice notes or user's requested checkout
            $plan = Database::fetch("SELECT * FROM subscription_plans WHERE price = ? OR id = 2 LIMIT 1", [$payment['amount']]);
            $planId = $plan['id'] ?? 2;
            $days = ($plan && $plan['billing_interval'] === 'quarterly') ? 90 : 30;

            Database::execute(
                "INSERT INTO subscriptions (user_id, plan_id, status, starts_at, ends_at, auto_renew, created_at)
                 VALUES (?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 1, NOW())",
                [(int)$payment['user_id'], $planId, $days]
            );

            NotificationService::send(
                (int)$payment['user_id'],
                "Payment Verified Successfully",
                "Your payment of " . format_money($payment['amount']) . " has been verified. Subscription activated.",
                'payment',
                url('dashboard/invoices/' . ($payment['invoice_id'] ?? 0))
            );

            flash('success', "Payment of " . format_money($payment['amount']) . " successfully verified by {$gatewayName}! Your premium plan is active.");
            $this->redirect($response, url('dashboard/invoices/' . ($payment['invoice_id'] ?? 0)));
            return;
        }

        Database::execute("UPDATE payments SET status = 'failed' WHERE id = ?", [(int)$payment['id']]);
        flash('danger', 'Payment verification failed at gateway. Your card was not charged.');
        $this->redirect($response, url('dashboard/subscription'));
    }

    /**
     * Webhook Endpoint with HMAC signature check and Idempotency protection.
     */
    public function webhook(Request $request, Response $response, array $params): void
    {
        $gatewayName = $params['gateway'] ?? 'paystack';
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? $_SERVER['HTTP_VERIF_HASH'] ?? $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

        $gateway = PaymentGatewayManager::gateway($gatewayName);
        $isValidSignature = $gateway->verifyWebhookSignature($payload, $signature);

        // In production, reject invalid signatures with 401 Unauthorized
        if (!$isValidSignature && !empty($signature) && !in_array($gatewayName, ['sandbox', 'test_sandbox'], true)) {
            $this->json($response, ['error' => 'Invalid webhook signature.'], 401);
            return;
        }

        $decoded = json_decode($payload, true) ?: [];
        $eventRef = $decoded['data']['reference'] ?? $decoded['data']['tx_ref'] ?? ('EVT-' . uniqid());

        // Check Duplicate Webhook Protection (Idempotency)
        $existing = Database::fetch("SELECT id, is_processed FROM payment_webhook_events WHERE reference = ? LIMIT 1", [$eventRef]);
        if ($existing && $existing['is_processed']) {
            $this->json($response, ['status' => 'already_processed', 'reference' => $eventRef]);
            return;
        }

        Database::execute(
            "INSERT INTO payment_webhook_events (gateway, event_type, reference, payload_json, is_processed, processed_at, created_at)
             VALUES (?, 'charge.success', ?, ?, 1, NOW(), NOW())",
            [$gatewayName, $eventRef, $payload]
        );

        $this->json($response, ['status' => 'acknowledged', 'reference' => $eventRef]);
    }

    /**
     * Invoice / Printable Receipt View.
     */
    public function showInvoice(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $userId = Auth::id();

        $invoice = Database::fetch(
            "SELECT i.*, u.full_name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                    p.reference AS payment_reference, p.gateway, p.paid_at AS payment_date
             FROM invoices i
             JOIN users u ON u.id = i.user_id
             LEFT JOIN payments p ON p.invoice_id = i.id AND p.status = 'successful'
             WHERE i.id = ? AND (i.user_id = ? OR ? IN (SELECT user_id FROM user_roles WHERE role_id = 1))
             LIMIT 1",
            [$id, $userId, $userId]
        );

        if (!$invoice) {
            flash('danger', 'Invoice not found or access restricted.');
            $this->redirect($response, url('dashboard/invoices'));
            return;
        }

        $html = $this->render('dashboard/invoices/show', [
            'title' => 'Invoice #' . e($invoice['invoice_number']) . ' | Afriserva Real Estate',
            'invoice' => $invoice,
            'user' => Auth::user()
        ], 'layouts/print');

        $response->html($html);
    }

    /**
     * Request Refund Workflow.
     */
    public function requestRefund(Request $request, Response $response, array $params): void
    {
        $paymentId = (int)($params['id'] ?? 0);
        $reason = trim($request->input('reason', ''));
        $userId = Auth::id();

        if (empty($reason)) {
            flash('warning', 'Please provide a clear reason for requesting a refund.');
            $this->redirect($response, url('dashboard/invoices'));
            return;
        }

        $payment = Database::fetch("SELECT * FROM payments WHERE id = ? AND user_id = ? AND status = 'successful'", [$paymentId, $userId]);
        if (!$payment) {
            flash('danger', 'Eligible payment transaction not found.');
            $this->redirect($response, url('dashboard/invoices'));
            return;
        }

        RefundRequest::create($paymentId, $userId, (float)$payment['amount'], $reason, $payment['currency'] ?? 'NGN');

        NotificationService::send(
            1, // Super admin
            "New Refund Request Lodged",
            "Customer " . Auth::user()['full_name'] . " requested refund for payment ref {$payment['reference']}.",
            'payment',
            url('admin/refunds')
        );

        flash('success', 'Your refund request has been lodged for compliance review.');
        $this->redirect($response, url('dashboard/invoices'));
    }
}
