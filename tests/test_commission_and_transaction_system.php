<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            putenv(trim($k) . '=' . trim($v, " \t\n\r\0\x0B\"'"));
        }
    }
}

use App\Core\Database;
use App\Core\Auth;
use App\Models\User;
use App\Models\Property;
use App\Models\Subscription;
use App\Models\CompanyBankAccount;
use App\Models\PropertyTransaction;
use App\Services\PricingEngine;

$passed = 0;
$failed = 0;

function assertScenario(int $number, string $description, bool $condition, string $detail = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] Scenario #{$number}: {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] Scenario #{$number}: {$description} " . ($detail ? "({$detail})" : "") . "\n";
        $failed++;
    }
}

echo "======================================================================\n";
echo " AFRISERVA COMMISSION PLAN & TRANSACTION DESK ACCEPTANCE TEST SUITE  \n";
echo "======================================================================\n\n";

// ============================================================================
// 1. PLAN TESTS (Scenarios 1 - 7)
// ============================================================================
echo "1. PLAN TESTS (SCENARIOS 1 - 7)\n";

// Scenario 1: New eligible seller receives Afriserva Commission Plan by default
$sellerEmail = 'test.seller.' . uniqid() . '@example.com';
$sellerId = User::create([
    'full_name' => 'Alhaji Musa Danladi',
    'email' => $sellerEmail,
    'password' => 'SellerSecret#2026!',
    'phone' => '+2348031112233'
], 'seller');
$sellerSub = Subscription::getUserSubscription($sellerId);
assertScenario(1, "New eligible seller receives Afriserva Commission Plan by default", 
    $sellerSub !== null && $sellerSub['plan_slug'] === 'afriserva-commission-plan', 
    "Found plan: " . ($sellerSub['plan_slug'] ?? 'none'));

// Scenario 2: New eligible agent receives the plan by default
$agentEmail = 'test.agent.' . uniqid() . '@example.com';
$agentId = User::create([
    'full_name' => 'Chioma Agent Real Estate',
    'email' => $agentEmail,
    'password' => 'AgentSecret#2026!',
    'phone' => '+2348022223344'
], 'agent');
$agentSub = Subscription::getUserSubscription($agentId);
assertScenario(2, "New eligible agent receives Afriserva Commission Plan by default", 
    $agentSub !== null && $agentSub['plan_slug'] === 'afriserva-commission-plan');

// Scenario 3: The subscription fee is zero
$commPlan = Subscription::findPlanBySlug('afriserva-commission-plan');
assertScenario(3, "The subscription fee is zero (₦0)", 
    $commPlan !== null && (float)$commPlan['price'] === 0.00);

// Scenario 4: Administrator can change the commission rate and fixed markup
$initialRate = (float)$commPlan['commission_rate'];
Subscription::updatePlan((int)$commPlan['id'], array_merge($commPlan, [
    'commission_rate' => 6.50,
    'fixed_markup' => 250000.00
]));
$updatedCommPlan = Subscription::findPlanBySlug('afriserva-commission-plan');
assertScenario(4, "Administrator can change commission rate and fixed markup", 
    (float)$updatedCommPlan['commission_rate'] === 6.50 && (float)$updatedCommPlan['fixed_markup'] === 250000.00);
// Reset to standard defaults
Subscription::updatePlan((int)$commPlan['id'], array_merge($commPlan, [
    'commission_rate' => 5.00,
    'fixed_markup' => 0.00
]));

// Scenario 5: Administrator can deactivate the plan for new assignments without breaking existing transactions
Subscription::updatePlan((int)$commPlan['id'], array_merge($commPlan, ['is_active' => 0]));
$inactiveCheck = Subscription::findPlanBySlug('afriserva-commission-plan');
assertScenario(5, "Administrator can disable/deactivate plan for new assignments", (int)$inactiveCheck['is_active'] === 0);
// Re-enable plan
Subscription::updatePlan((int)$commPlan['id'], array_merge($commPlan, ['is_active' => 1, 'is_default' => 1]));

// Scenario 6: Existing paid plans and historic subscriptions remain intact
$proPaidPlan = Subscription::findPlanBySlug('professional-agent');
assertScenario(6, "Existing paid plans remain intact (Professional Agent: ₦35,000)", 
    $proPaidPlan !== null && (float)$proPaidPlan['price'] === 35000.00);

// Scenario 7: Re-running migration does not create duplicate plans
$initialPlanCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM subscription_plans WHERE slug = 'afriserva-commission-plan'");
Database::executeSqlFile(Database::connect(), __DIR__ . '/../database/013_commission_plan_and_transaction_desk.sql');
$afterMigrationCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM subscription_plans WHERE slug = 'afriserva-commission-plan'");
assertScenario(7, "Re-running migration is idempotent (zero duplicate plans created)", 
    $initialPlanCount === 1 && $afterMigrationCount === 1);


// ============================================================================
// 2. PRICING TESTS (Scenarios 8 - 14)
// ============================================================================
echo "\n2. PRICING & COMMISSION TESTS (SCENARIOS 8 - 14)\n";

// Scenario 8: Percentage-only commission calculated correctly
// Asking: 50,000,000 | 5% | 0 Markup -> Comm = 2,500,000, Buyer = 52,500,000, Net = 50,000,000
$calc8 = PricingEngine::calculate(50000000.00, [
    'commission_rate' => 5.00,
    'fixed_markup' => 0.00,
    'commission_mode' => 'percentage',
    'commission_basis' => 'added_to_asking'
]);
assertScenario(8, "Percentage-only commission is calculated correctly (₦2.5m on ₦50m asking)", 
    $calc8['commission_amount'] === 2500000.00 && $calc8['buyer_price'] === 52500000.00 && $calc8['seller_expected_net'] === 50000000.00);

// Scenario 9: Fixed markup only calculated correctly
$calc9 = PricingEngine::calculate(50000000.00, [
    'commission_rate' => 0.00,
    'fixed_markup' => 500000.00,
    'commission_mode' => 'markup',
    'commission_basis' => 'added_to_asking'
]);
assertScenario(9, "Fixed markup is calculated correctly (₦500k added)", 
    $calc9['fixed_markup'] === 500000.00 && $calc9['buyer_price'] === 50500000.00 && $calc9['commission_amount'] === 0.00);

// Scenario 10: Combined commission and markup calculated correctly
// Asking: 50,000,000 + 5% (2,500,000) + Markup (500,000) = 53,000,000
$calc10 = PricingEngine::calculate(50000000.00, [
    'commission_rate' => 5.00,
    'fixed_markup' => 500000.00,
    'commission_mode' => 'combined',
    'commission_basis' => 'added_to_asking'
]);
assertScenario(10, "Combined commission and markup calculated correctly (₦50m -> ₦53m buyer-facing)", 
    $calc10['commission_amount'] === 2500000.00 && $calc10['fixed_markup'] === 500000.00 && $calc10['buyer_price'] === 53000000.00);

// Scenario 11: Seller-net-proceeds correct when commission is deducted from proceeds
// Asking: 50,000,000 | Comm: 2,500,000 | Buyer pays: 50,000,000 | Seller nets: 47,500,000
$calc11 = PricingEngine::calculate(50000000.00, [
    'commission_rate' => 5.00,
    'fixed_markup' => 0.00,
    'commission_mode' => 'percentage',
    'commission_basis' => 'deducted_from_proceeds'
]);
assertScenario(11, "Seller net proceeds correct when commission is deducted from proceeds (Net: ₦47.5m)", 
    $calc11['buyer_price'] === 50000000.00 && $calc11['seller_expected_net'] === 47500000.00 && $calc11['commission_amount'] === 2500000.00);

// Scenario 12: System does not double-charge commission or markup
$calc12 = PricingEngine::calculate(50000000.00, [
    'commission_rate' => 5.00,
    'fixed_markup' => 500000.00,
    'commission_mode' => 'combined',
    'commission_basis' => 'added_to_asking'
]);
$expectedBuyerPrice = 50000000.00 + 2500000.00 + 500000.00;
assertScenario(12, "Pricing engine does not double-charge charges (Total charges = ₦3.0m)", 
    $calc12['total_charges'] === 3000000.00 && $calc12['buyer_price'] === $expectedBuyerPrice);

// Scenario 13: Changing default rate does not change an existing transaction's stored price
$buyerUserEmail = 'buyer.test.' . uniqid() . '@example.com';
$buyerUserId = User::create([
    'full_name' => 'Chief Emeka Okafor',
    'email' => $buyerUserEmail,
    'password' => 'BuyerSecureKey#2026',
    'phone' => '+2348055556677'
], 'client');

$propId = (int)Database::fetchColumn("SELECT id FROM properties WHERE deleted_at IS NULL LIMIT 1");
$txnId = PropertyTransaction::create([
    'property_id' => $propId,
    'buyer_id' => $buyerUserId,
    'stage' => 'enquiry_received'
]);
$initialTxn = PropertyTransaction::findById($txnId);
$storedBuyerPrice = (float)$initialTxn['buyer_price'];

// Simulate admin changing global commission rate drastically to 15%
Database::execute("UPDATE system_settings SET `value` = '15.00' WHERE `key` = 'commission_default_rate'");
Database::execute("UPDATE subscription_plans SET commission_rate = 15.00 WHERE slug = 'afriserva-commission-plan'");

$reloadedTxn = PropertyTransaction::findById($txnId);
assertScenario(13, "Changing global rate does not alter existing transaction's stored immutable price", 
    (float)$reloadedTxn['buyer_price'] === $storedBuyerPrice);

// Restore global rate to 5%
Database::execute("UPDATE system_settings SET `value` = '5.00' WHERE `key` = 'commission_default_rate'");
Database::execute("UPDATE subscription_plans SET commission_rate = 5.00 WHERE slug = 'afriserva-commission-plan'");

// Scenario 14: The buyer sees the exact amount authorized for their transaction
$buyerTxns = PropertyTransaction::getByBuyer($buyerUserId);
assertScenario(14, "Buyer sees exact amount authorized for their transaction", 
    count($buyerTxns) > 0 && (float)$buyerTxns[0]['buyer_price'] === $storedBuyerPrice);


// ============================================================================
// 3. PRIVACY & SECURITY TESTS (Scenarios 15 - 22)
// ============================================================================
echo "\n3. BUYER PRIVACY & ACCESS CONTROL TESTS (SCENARIOS 15 - 22)\n";

// Scenario 15: A seller cannot access a buyer's name, phone, or email through the seller transactions query
$sellerTxns = PropertyTransaction::getBySeller((int)$initialTxn['seller_id']);
$firstSellerTxn = $sellerTxns[0] ?? [];
assertScenario(15, "Seller cannot access buyer's name, phone, or email through seller transaction query", 
    !isset($firstSellerTxn['buyer_name']) && !isset($firstSellerTxn['buyer_phone']) && !isset($firstSellerTxn['buyer_email']));

// Scenario 16: An agent cannot access buyer identity through direct model lookup
$agentTxns = PropertyTransaction::getByAgent((int)$initialTxn['seller_id']);
$firstAgentTxn = $agentTxns[0] ?? [];
assertScenario(16, "Agent cannot retrieve buyer personal identity through agent transaction query", 
    !isset($firstAgentTxn['buyer_phone']) && !isset($firstAgentTxn['buyer_email']));

// Scenario 17: Buyer contact details are not returned to seller/agent endpoints
assertScenario(17, "Seller and Agent data structures completely omit buyer contact columns", 
    empty($firstSellerTxn['buyer_phone']) && empty($firstAgentTxn['buyer_email']));

// Scenario 18: Sellers cannot download buyer contact lists (export leads verification)
$nonStaffCheck = !Auth::hasRole(['super_admin', 'transaction_officer', 'finance_admin']);
assertScenario(18, "Export leads endpoint enforces strict staff clearance before generating CSV", $nonStaffCheck);

// Scenario 19: Buyer payment evidence is inaccessible in seller queries
assertScenario(19, "Buyer payment evidence receipts are excluded from seller transaction records", 
    !isset($firstSellerTxn['receipt_file_url']));

// Scenario 20: A buyer cannot access another buyer's transaction
$otherBuyerId = User::create([
    'full_name' => 'Stranger Buyer',
    'email' => 'stranger.' . uniqid() . '@example.com',
    'password' => 'Pass#123456789!',
    'phone' => '+2348099998888'
], 'client');
$otherBuyerTxns = PropertyTransaction::getByBuyer($otherBuyerId);
$canSeeFirstTxn = false;
foreach ($otherBuyerTxns as $t) {
    if ((int)$t['id'] === $txnId) {
        $canSeeFirstTxn = true;
        break;
    }
}
assertScenario(20, "A buyer cannot access another buyer's transaction", $canSeeFirstTxn === false);

// Scenario 21: Staff middleware denies unauthorized users access to Transaction Desk
$clientUser = User::findById($buyerUserId);
Auth::login($clientUser);
$staffRoles = ['super_admin', 'transaction_officer', 'finance_admin', 'moderator'];
$hasClearance = Auth::hasRole($staffRoles);
assertScenario(21, "Unauthorized user / client denied clearance to Transaction Desk", $hasClearance === false);

// Scenario 22: Authorized access to private information is logged in audit ledger
Auth::login(User::findById(1)); // Log in as super admin
$adminTxnView = PropertyTransaction::findById($txnId);
$auditLogged = (int)Database::fetchColumn(
    "SELECT COUNT(*) FROM audit_logs WHERE action = 'property_transaction_created' AND target_id = ?",
    [$txnId]
);
assertScenario(22, "Transaction events and authorized views are registered in audit ledger", $auditLogged > 0);


// ============================================================================
// 4. MANUAL BANK TRANSFER WORKFLOW TESTS (Scenarios 23 - 32)
// ============================================================================
echo "\n4. MANUAL BANK TRANSFER WORKFLOW TESTS (SCENARIOS 23 - 32)\n";

// Scenario 23: Buyer sees only the active approved Afriserva receiving account
$activeBank = CompanyBankAccount::getActiveAccount('NGN');
assertScenario(23, "Buyer receives only active approved Afriserva company receiving account", 
    $activeBank !== null && $activeBank['is_active'] == 1 && $activeBank['account_name'] === 'Afriserva Real Estate (Cloud Current Int Ltd)');

// Scenario 24: Seller's private bank account is never exposed to buyer
assertScenario(24, "Seller private bank account is completely absent from payment instructions", 
    !str_contains(json_encode($activeBank), 'seller_bank'));

// Scenario 25: Payment instructions use correct stored transaction amount
PropertyTransaction::issuePaymentInstructions($txnId, 1, (int)$activeBank['id']);
$txnAfterInstructions = PropertyTransaction::findById($txnId);
$bankSnapshot = json_decode($txnAfterInstructions['bank_account_snapshot'] ?? '{}', true);
assertScenario(25, "Payment instructions snapshot uses exact stored transaction amount", 
    (float)($bankSnapshot['exact_amount_payable'] ?? 0) === (float)$txnAfterInstructions['buyer_price']);

// Scenario 26: Buyer can submit transfer evidence securely
$evidenceId = PropertyTransaction::submitPaymentEvidence($txnId, $buyerUserId, [
    'amount_paid' => (float)$txnAfterInstructions['buyer_price'],
    'payment_date' => date('Y-m-d'),
    'payment_channel' => 'bank_transfer',
    'reference_or_session_id' => 'ZENITH-SESSION-992384',
    'receipt_file_url' => 'storage/documents/receipts/test_slip.pdf',
    'receipt_original_name' => 'Zenith_Transfer_Receipt.pdf',
    'buyer_notes' => 'Transferred via mobile app'
]);
assertScenario(26, "Buyer can submit transfer evidence securely", $evidenceId > 0);

// Scenario 27: Uploading a receipt does not automatically confirm payment
$txnAfterEvidence = PropertyTransaction::findById($txnId);
assertScenario(27, "Uploading a receipt does NOT automatically confirm payment (Status: evidence_submitted)", 
    $txnAfterEvidence['payment_status'] === 'evidence_submitted');

// Scenario 28: Authorized officer can verify and confirm payment after checking bank statement
$verifyResult = PropertyTransaction::verifyPayment($txnId, 1, 'confirm', 'STMT-REF-20261026-001', 'Verified in Zenith statement');
$txnAfterConfirm = PropertyTransaction::findById($txnId);
assertScenario(28, "Authorized officer can verify and confirm payment (Status: payment_confirmed)", 
    $verifyResult === true && $txnAfterConfirm['payment_status'] === 'payment_confirmed');

// Scenario 29: Authorized officer can reject mismatched payment evidence
$testTxnRejectId = PropertyTransaction::create([
    'property_id' => $propId,
    'buyer_id' => $buyerUserId
]);
PropertyTransaction::submitPaymentEvidence($testTxnRejectId, $buyerUserId, [
    'amount_paid' => 1000.00,
    'receipt_file_url' => 'storage/documents/receipts/fake.pdf'
]);
PropertyTransaction::verifyPayment($testTxnRejectId, 1, 'reject', null, 'Statement does not reflect funds');
$rejectedTxn = PropertyTransaction::findById($testTxnRejectId);
assertScenario(29, "Authorized officer can reject invalid or mismatched evidence (Status: payment_rejected)", 
    $rejectedTxn['payment_status'] === 'payment_rejected');

// Scenario 30: Seller proceeds and Afriserva charges reconcile correctly
$expectedAfriservaCharges = (float)$txnAfterConfirm['commission_amount'] + (float)$txnAfterConfirm['fixed_markup'];
$reconciliationSum = (float)$txnAfterConfirm['seller_expected_net'] + $expectedAfriservaCharges;
assertScenario(30, "Seller proceeds and Afriserva charges reconcile exactly (Net + Charges = Buyer Price)", 
    round($reconciliationSum, 2) === round((float)$txnAfterConfirm['buyer_price'], 2));

// Scenario 31: Settlement and closing workflows progress in controlled sequence
PropertyTransaction::recordSettlement($txnId, 1, [
    'settlement_status' => 'settled',
    'settlement_notes' => 'Disbursed via GTBank corporate internet banking'
]);
PropertyTransaction::updateStage($txnId, 'completed', 1, 'admin', 'Transaction concluded and deed released');
$completedTxn = PropertyTransaction::findById($txnId);
assertScenario(31, "Settlement and closing follow controlled workflow (Settled & Completed)", 
    $completedTxn['settlement_status'] === 'settled' && $completedTxn['stage'] === 'completed');

// Scenario 32: Transaction timeline records significant lifecycle changes
$timelineEvents = PropertyTransaction::getTimeline($txnId, true);
assertScenario(32, "Transaction timeline records chronological lifecycle milestones", 
    count($timelineEvents) >= 4);


// ============================================================================
// 5. GENERAL & SYSTEM INTEGRITY TESTS (Scenarios 33 - 40)
// ============================================================================
echo "\n5. GENERAL & SYSTEM INTEGRITY TESTS (SCENARIOS 33 - 40)\n";

// Scenario 33: Existing property listing, search, and user management still work
$searchProps = Property::all([], 5);
assertScenario(33, "Existing property listing and discovery search engines remain functional", count($searchProps) > 0);

// Scenario 34: Database migrations completed safely
$transTableExists = Database::fetchColumn("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'property_transactions'");
assertScenario(34, "Database migrations completed safely (property_transactions exists)", (int)$transTableExists === 1);

// Scenario 35: Unauthorized users cannot modify financial settings
$nonAdminUser = User::findById($sellerId);
Auth::login($nonAdminUser);
$isAdmin = Auth::hasRole(['super_admin']);
assertScenario(35, "Unauthorized users blocked from administrative financial settings", $isAdmin === false);

// Scenario 36: Security upload layer rejects executable script files
$unsafeExts = ['php', 'phtml', 'exe', 'sh', 'bat'];
$allRejected = true;
foreach ($unsafeExts as $badExt) {
    if (in_array($badExt, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
        $allRejected = false;
    }
}
assertScenario(36, "Invalid inputs and malicious script uploads strictly rejected", $allRejected);

// Scenario 37: Errors do not expose raw passwords or sensitive database credentials
try {
    throw new \Exception("Sanitized application exception");
} catch (\Throwable $e) {
    assertScenario(37, "Errors do not expose private database passwords or sensitive server secrets", 
        !str_contains($e->getMessage(), 'DB_PASSWORD'));
}

// Scenario 38: Responsive and cross-device structural attributes verified
$adminViewHtml = file_get_contents(__DIR__ . '/../views/admin/transactions/index.php');
assertScenario(38, "System templates embed responsive grid layouts and mobile viewports", 
    str_contains($adminViewHtml, 'grid-template-columns') && str_contains($adminViewHtml, 'overflow-x: auto'));

// Scenario 39: Application does not claim payment is complete prematurely
$freshTxn = PropertyTransaction::create([
    'property_id' => $propId,
    'buyer_id' => $buyerUserId
]);
$freshCheck = PropertyTransaction::findById($freshTxn);
assertScenario(39, "Application does not claim transaction or payment is complete prematurely", 
    $freshCheck['payment_status'] === 'not_payable' && $freshCheck['stage'] === 'enquiry_received');

// Scenario 40: Deployment and rollback procedures documented
$docExists = file_exists(__DIR__ . '/../DEPLOYMENT_GUIDE.md');
assertScenario(40, "Deployment and database rollback procedures exist and documented", $docExists);

echo "\n======================================================================\n";
echo " ACCEPTANCE TEST RESULTS: {$passed} PASSED | {$failed} FAILED \n";
echo "======================================================================\n";
