<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Core/Request.php';
require_once __DIR__ . '/../app/Core/Response.php';
require_once __DIR__ . '/../app/Core/CSRF.php';
require_once __DIR__ . '/../app/Core/View.php';
require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Helpers/AuditLogger.php';
require_once __DIR__ . '/../app/Services/AntiBypassFilter.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Property.php';
require_once __DIR__ . '/../app/Models/Location.php';
require_once __DIR__ . '/../app/Models/BannerAd.php';
require_once __DIR__ . '/../app/Controllers/HomeController.php';
require_once __DIR__ . '/../app/Controllers/PropertyController.php';
require_once __DIR__ . '/../app/Helpers/Formatters.php';

use App\Core\Request;
use App\Core\Response;
use App\Controllers\HomeController;
use App\Controllers\PropertyController;

\App\Core\Session::start();

class TestResponse extends Response {
    public string $body = '';
    public int $code = 200;

    public function html(string $content, int $status = 200): void {
        $this->body = $content;
        $this->code = $status;
    }

    public function getBody(): string {
        return $this->body;
    }

    public function getStatusCode(): int {
        return $this->code;
    }
}

$passed = 0;
$failed = 0;

function assertCheck(bool $condition, string $desc): void {
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
echo " NIGERIA PROPERTY CENTRE SEARCH & HOMEPAGE SUITE \n";
echo "======================================================================\n\n";

// 1. Test HomeController rendering
$_SERVER['REQUEST_URI'] = '/';
$req = new Request();
$res = new TestResponse();
$homeController = new HomeController();
$homeController->index($req, $res);
$content = $res->getBody();

assertCheck(strpos($content, 'Property for Sale & Rent in Nigeria') !== false, 'Hero title matches Nigeria Property Centre portal heading');
assertCheck(strpos($content, 'data-type="sale"') !== false && strpos($content, 'For Sale') !== false, 'For Sale tab rendered with data-type="sale"');
assertCheck(strpos($content, 'data-type="rent"') !== false && strpos($content, 'For Rent') !== false, 'For Rent tab rendered with data-type="rent"');
assertCheck(strpos($content, 'data-type="short_let"') !== false && strpos($content, 'Short Let') !== false, 'Short Let tab rendered with data-type="short_let"');
assertCheck(strpos($content, 'data-type="land"') !== false && strpos($content, 'Land') !== false, 'Land tab rendered with data-type="land"');
assertCheck(strpos($content, 'data-type="commercial"') !== false && strpos($content, 'Commercial') !== false, 'Commercial tab rendered with data-type="commercial"');
assertCheck(strpos($content, 'search-bedrooms-col') !== false, 'Bedrooms container present for dynamic conditional visibility');
assertCheck(strpos($content, 'search-min-price-select') !== false, 'Min Price dropdown element present');
assertCheck(strpos($content, 'search-max-price-select') !== false, 'Max Price dropdown element present');
assertCheck(strpos($content, 'Flats for rent in Lekki') !== false, 'Nigeria Property Centre Popular Search tag: Flats for rent in Lekki');
assertCheck(strpos($content, 'Houses for sale in Ikoyi') !== false, 'Nigeria Property Centre Popular Search tag: Houses for sale in Ikoyi');
assertCheck(strpos($content, 'Duplexes in Ajah') !== false, 'Nigeria Property Centre Popular Search tag: Duplexes in Ajah');
assertCheck(strpos($content, 'Land in Ibeju Lekki') !== false, 'Nigeria Property Centre Popular Search tag: Land in Ibeju Lekki');
assertCheck(strpos($content, 'Explore Real Estate by Property Type') !== false, 'Nigeria Property Centre Category Browse Section rendered');

// 2. Test PropertyController search routing with Nigeria Property Centre parameters
$_GET = ['type' => 'sale', 'q' => 'Ikoyi'];
$pReq = new Request();
$pRes = new TestResponse();
$pController = new PropertyController();
$pController->index($pReq, $pRes);
assertCheck($pRes->getStatusCode() === 200, 'Property search by type=sale&q=Ikoyi returns 200 OK');

$_GET = ['type' => 'rent', 'q' => 'Lekki'];
$pReq = new Request();
$pRes = new TestResponse();
$pController->index($pReq, $pRes);
assertCheck($pRes->getStatusCode() === 200, 'Property search by type=rent&q=Lekki returns 200 OK');

$_GET = ['type' => 'land'];
$pReq = new Request();
$pRes = new TestResponse();
$pController->index($pReq, $pRes);
assertCheck($pRes->getStatusCode() === 200, 'Property search by type=land returns 200 OK');

$_GET = ['type' => 'commercial'];
$pReq = new Request();
$pRes = new TestResponse();
$pController->index($pReq, $pRes);
assertCheck($pRes->getStatusCode() === 200, 'Property search by type=commercial returns 200 OK');

echo "\n======================================================================\n";
echo " VERIFICATION RESULTS: {$passed} PASSED | {$failed} FAILED \n";
echo "======================================================================\n";

exit($failed > 0 ? 1 : 0);
