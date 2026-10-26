<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Core/Request.php';
require_once __DIR__ . '/../app/Core/Response.php';
require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Helpers/AuditLogger.php';
require_once __DIR__ . '/../app/Services/AntiBypassFilter.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Property.php';
require_once __DIR__ . '/../app/Models/Enquiry.php';
require_once __DIR__ . '/../app/Models/Conversation.php';
require_once __DIR__ . '/../app/Models/Message.php';
require_once __DIR__ . '/../app/Models/ViewingAppointment.php';

use App\Core\Database;
use App\Core\Auth;
use App\Services\AntiBypassFilter;
use App\Models\Enquiry;
use App\Models\ViewingAppointment;

Database::connect();

$passed = 0;
$failed = 0;

function assertCheck(string $desc, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$desc}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$desc}\n";
        $failed++;
    }
}

echo "======================================================================\n";
echo " AFRISERVA ANTI-BYPASS SECURITY ENGINE VERIFICATION SUITE \n";
echo "======================================================================\n\n";

// 1. Phone number bypass detection
$t1 = "Call me directly on 08031234567 to arrange payment";
assertCheck("Standard Nigerian phone number detected and blocked", AntiBypassFilter::containsBypassAttempt($t1));

$t2 = "Reach me at +234 803 123 4567 off-platform";
assertCheck("International formatted Nigerian number with spaces blocked", AntiBypassFilter::containsBypassAttempt($t2));

$t3 = "My number is 0 8 0 3 9 9 9 8 8 8 8 call me";
assertCheck("Spaced-out obfuscated phone digits blocked", AntiBypassFilter::containsBypassAttempt($t3));

$t4 = "Text me on zero eight zero three one two three";
assertCheck("Spelled out phone digit words blocked", AntiBypassFilter::containsBypassAttempt($t4));

// 2. Email bypass detection
$t5 = "Email me directly at seller@privatefirm.com";
assertCheck("Standard email address detected and blocked", AntiBypassFilter::containsBypassAttempt($t5));

$t6 = "My email is buyer [at] gmail [dot] com send deed there";
assertCheck("Obfuscated bracketed email detected and blocked", AntiBypassFilter::containsBypassAttempt($t6));

// 3. Bank account bypass detection
$t7 = "Transfer directly to my GTBank account 0123456789";
assertCheck("10-digit NUBAN bank account with bank keyword blocked", AntiBypassFilter::containsBypassAttempt($t7));

// 4. WhatsApp and social bypass detection
$t8 = "Chat with me on wa.me/2348039998888 for lower price";
assertCheck("wa.me direct WhatsApp URL detected and blocked", AntiBypassFilter::containsBypassAttempt($t8));

$t9 = "Connect with me on telegram: @directbroker";
assertCheck("Telegram handle detected and blocked", AntiBypassFilter::containsBypassAttempt($t9));

$t10 = "Visit my website www.offmarketdeals.ng";
assertCheck("External website URL detected and blocked", AntiBypassFilter::containsBypassAttempt($t10));

// 5. Legitimate safe communication is NOT blocked
$tSafe = "Good day, I would like to confirm if this 4-bedroom duplex has valid Governor's Consent and whether an inspection tour is possible this Saturday morning.";
assertCheck("Legitimate property inquiry without contact details is APPROVED", !AntiBypassFilter::containsBypassAttempt($tSafe));

// 6. Sanitization replaces sensitive contact info
$sanitized = AntiBypassFilter::sanitize("My phone is 08031234567 and email is deal@gmail.com");
assertCheck("Sanitizer successfully redacts contact details with safety placeholder", 
    str_contains($sanitized, '[PROTECTED - CONTACT EXCHANGED PROHIBITED]') && 
    !str_contains($sanitized, '08031234567') && 
    !str_contains($sanitized, 'deal@gmail.com'));

// 7. Structured evaluation returns descriptive reason
$eval = AntiBypassFilter::evaluate("Pay to GTBank 0123456789 or call 08031234567", "direct_message", 1);
assertCheck("Evaluation returns is_blocked=true and explanatory policy reason", 
    $eval['is_blocked'] === true && !empty($eval['reason']));

// 8. ViewingAppointment masks client contact info for agents
$agentAppointments = ViewingAppointment::getByAgent(1);
$allMasked = true;
foreach ($agentAppointments as $app) {
    if (!str_contains($app['client_phone'], '***') && !empty($app['client_phone'])) {
        $allMasked = false;
        break;
    }
}
assertCheck("ViewingAppointment::getByAgent() strictly masks client phone numbers", $allMasked);

// 9. Enquiry::getByAgent() masks client contact info
$agentEnquiries = Enquiry::getByAgent(1);
$enqMasked = true;
foreach ($agentEnquiries as $e) {
    if (!str_contains($e['phone'], '***') && !empty($e['phone'])) {
        $enqMasked = false;
        break;
    }
    if (isset($e['raw_phone']) || isset($e['raw_email'])) {
        $enqMasked = false;
        break;
    }
}
assertCheck("Enquiry::getByAgent() enforces masked phone and excludes raw contact keys", $enqMasked);

// 10. Buyer direct contact with Admin Desk (Before or After Viewing)
$buyerUser = \App\Models\User::findById(2);
if ($buyerUser) {
    Auth::login($buyerUser);
    $adminId = 1;
    $propId = (int)Database::fetchColumn("SELECT id FROM properties LIMIT 1");
    $directAdminConvId = \App\Models\Conversation::findOrCreate((int)$buyerUser['id'], $adminId, null);
    $propAdminConvId = \App\Models\Conversation::findOrCreate((int)$buyerUser['id'], $adminId, $propId);
    
    assertCheck("Buyer can initiate direct conversation thread with Super Admin", $directAdminConvId > 0);
    assertCheck("Buyer can initiate property-specific admin coordination thread before/after viewing", $propAdminConvId > 0);
}

// 11. Buyer viewing inspections retrieval
$userViewings = ViewingAppointment::getByUser(2);
assertCheck("Buyer can access their own inspection tours via ViewingAppointment::getByUser()", is_array($userViewings));

echo "\n======================================================================\n";
echo " ANTI-BYPASS & BUYER-ADMIN CONTACT TEST RESULTS: {$passed} PASSED | {$failed} FAILED \n";
echo "======================================================================\n";

if ($failed > 0) {
    exit(1);
}
