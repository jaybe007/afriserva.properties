<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load Autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    // Shared-hosting fallback autoloader (zero composer CLI required)
    spl_autoload_register(function (string $class): void {
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/../app/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
    require_once __DIR__ . '/../app/Helpers/Formatters.php';
}

// Simple .env Loader
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\RoleMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Controllers\HomeController;
use App\Controllers\PropertyController;
use App\Controllers\AuthController;
use App\Controllers\EnquiryController;
use App\Controllers\DashboardController;
use App\Controllers\AdminController;
use App\Controllers\PaymentController;
use App\Controllers\LeadAttributionController;
use App\Controllers\DocumentStreamController;
use App\Controllers\ShortLetController;
use App\Controllers\StaticPagesController;
use App\Controllers\MessageController;
use App\Controllers\RentalManagementController;
use App\Controllers\SeoController;
use App\Middlewares\SecurityHeadersMiddleware;

Session::start();
SecurityHeadersMiddleware::apply();
\App\Core\Database::autoMigrateIfEmpty();

$request = new Request();
$response = new Response();
$router = new Router();

// 1. PUBLIC MARKETPLACE & DISCOVERY ROUTES
$router->get('/', [HomeController::class, 'index']);
$router->get('/sitemap.xml', [SeoController::class, 'sitemapXml']);
$router->get('/banners/{id}/click', [HomeController::class, 'trackBannerClick']);
$router->get('/properties', [PropertyController::class, 'index']);
$router->get('/properties/map', [PropertyController::class, 'map']);
$router->get('/properties/{slug}', [PropertyController::class, 'show']);
$router->get('/api/properties/coordinates', [PropertyController::class, 'apiCoordinates']);
$router->get('/properties/{id}/track-lead', [LeadAttributionController::class, 'trackWhatsAppClick']);
$router->post('/properties/{id}/favorite', [PropertyController::class, 'toggleFavorite'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/properties/{id}/report', [PropertyController::class, 'reportListing'], [CsrfMiddleware::class]);
$router->post('/properties/save-search-alert', [PropertyController::class, 'saveSearchAlert'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/search-alerts/{id}/delete', [PropertyController::class, 'deleteSearchAlert'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/short-let/book', [ShortLetController::class, 'book'], [CsrfMiddleware::class, AuthMiddleware::class]);

// 2. PRODUCT VISION SECTORS & SERVICES
$router->get('/new-developments', [StaticPagesController::class, 'newDevelopments']);
$router->get('/investments', [StaticPagesController::class, 'investments']);
$router->get('/property-management', [StaticPagesController::class, 'propertyManagement']);
$router->get('/market-insights', [StaticPagesController::class, 'marketInsights']);
$router->get('/market-insights/{slug}', [StaticPagesController::class, 'marketArticle']);
$router->get('/agents', [StaticPagesController::class, 'agentsDirectory']);
$router->get('/agencies', [StaticPagesController::class, 'agenciesDirectory']);
$router->get('/switch-currency', [StaticPagesController::class, 'switchCurrency']);
$router->post('/newsletter/subscribe', [HomeController::class, 'subscribeNewsletter'], [CsrfMiddleware::class]);
$router->get('/cron/run', [StaticPagesController::class, 'runCron']);

// 3. ENQUIRIES & VIEWINGS
$router->post('/enquiries', [EnquiryController::class, 'submitEnquiry'], [CsrfMiddleware::class]);
$router->post('/viewings', [EnquiryController::class, 'bookViewing'], [CsrfMiddleware::class]);
$router->get('/admin/documents/{id}/download', [DocumentStreamController::class, 'download'], [AuthMiddleware::class]);

// 4. AUTHENTICATION & IDENTITY LIFECYCLE
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/login/2fa', [AuthController::class, 'twoFactorForm']);
$router->post('/login/2fa', [AuthController::class, 'verifyTwoFactor'], [CsrfMiddleware::class]);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);
$router->get('/forgot-password', [AuthController::class, 'forgotPasswordForm']);
$router->post('/forgot-password', [AuthController::class, 'sendResetLink'], [CsrfMiddleware::class]);
$router->get('/reset-password', [AuthController::class, 'resetPasswordForm']);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], [CsrfMiddleware::class]);
$router->get('/verify-email', [AuthController::class, 'verifyEmail']);
$router->post('/dashboard/verify-phone', [AuthController::class, 'verifyPhone'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/request-deletion', [AuthController::class, 'requestAccountDeletion'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/sessions/{id}/revoke', [AuthController::class, 'revokeSession'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/sessions/revoke-others', [AuthController::class, 'revokeOtherSessions'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

// 5. USER, AGENT & LANDLORD DASHBOARDS
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/dashboard/listings', [DashboardController::class, 'myListings'], [AuthMiddleware::class]);
$router->get('/dashboard/listings/create', [DashboardController::class, 'createListingForm'], [AuthMiddleware::class]);
$router->post('/dashboard/listings/store', [DashboardController::class, 'storeListing'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/dashboard/listings/edit/{id}', [DashboardController::class, 'editListingForm'], [AuthMiddleware::class]);
$router->post('/dashboard/listings/{id}/update', [DashboardController::class, 'updateListing'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/listings/{id}/images/store', [DashboardController::class, 'addPropertyImage'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/listings/{id}/images/{imageId}/delete', [DashboardController::class, 'deletePropertyImage'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/listings/{id}/images/{imageId}/cover', [DashboardController::class, 'setCoverImage'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/listings/{id}/videos/store', [DashboardController::class, 'savePropertyVideo'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/listings/{id}/quick-status', [DashboardController::class, 'updateQuickStatus'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/listings/{id}/delete', [DashboardController::class, 'deleteListing'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/dashboard/team', [DashboardController::class, 'team'], [AuthMiddleware::class]);
$router->post('/dashboard/team/invite', [DashboardController::class, 'inviteTeamMember'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/team/{id}/remove', [DashboardController::class, 'removeTeamMember'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/dashboard/verification', [DashboardController::class, 'verification'], [AuthMiddleware::class]);
$router->post('/dashboard/verification/upload', [DashboardController::class, 'uploadVerificationDocument'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/dashboard/invoices', [DashboardController::class, 'invoices'], [AuthMiddleware::class]);
$router->get('/dashboard/export/listings', [DashboardController::class, 'exportListings'], [AuthMiddleware::class]);
$router->get('/dashboard/export/leads', [DashboardController::class, 'exportLeads'], [AuthMiddleware::class]);
$router->get('/dashboard/enquiries', [DashboardController::class, 'enquiries'], [AuthMiddleware::class]);
$router->post('/dashboard/enquiries/{id}/status', [DashboardController::class, 'updateEnquiryStatus'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/enquiries/{id}/unlock', [EnquiryController::class, 'unlockContact'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/dashboard/viewings', [DashboardController::class, 'viewings'], [AuthMiddleware::class]);
$router->post('/dashboard/viewings/{id}/status', [EnquiryController::class, 'updateViewingStatus'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/dashboard/saved', [DashboardController::class, 'savedProperties'], [AuthMiddleware::class]);
$router->get('/dashboard/subscription', [DashboardController::class, 'subscription'], [AuthMiddleware::class]);
$router->get('/dashboard/profile', [DashboardController::class, 'profile'], [AuthMiddleware::class]);
$router->post('/dashboard/profile', [DashboardController::class, 'profile'], [CsrfMiddleware::class, AuthMiddleware::class]);

// 6. MESSAGING & NOTIFICATIONS (SECTION 13)
$router->get('/dashboard/messages', [MessageController::class, 'index'], [AuthMiddleware::class]);
$router->get('/dashboard/messages/{id}', [MessageController::class, 'thread'], [AuthMiddleware::class]);
$router->post('/dashboard/messages/{id}/send', [MessageController::class, 'send'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/dashboard/notifications', [MessageController::class, 'notifications'], [AuthMiddleware::class]);
$router->post('/dashboard/notifications/{id}/read', [MessageController::class, 'markNotificationRead'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/notifications/mark-all-read', [MessageController::class, 'markAllNotificationsRead'], [CsrfMiddleware::class, AuthMiddleware::class]);

// 7. RENTAL & PROPERTY MANAGEMENT MODULE (SECTION 16)
$router->get('/properties/{slug}/apply', [RentalManagementController::class, 'applyForm']);
$router->post('/properties/{slug}/apply', [RentalManagementController::class, 'submitApplication'], [CsrfMiddleware::class]);
$router->get('/dashboard/rental-applications', [RentalManagementController::class, 'applications'], [AuthMiddleware::class]);
$router->post('/dashboard/rental-applications/{id}/status', [RentalManagementController::class, 'reviewApplication'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/rental-applications/{id}/screening', [RentalManagementController::class, 'screenTenant'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/dashboard/leases', [RentalManagementController::class, 'leases'], [AuthMiddleware::class]);
$router->post('/dashboard/leases/store', [RentalManagementController::class, 'createLease'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/leases/{id}/record-payment', [RentalManagementController::class, 'recordRentPayment'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/dashboard/leases/{id}/statement', [RentalManagementController::class, 'statement'], [AuthMiddleware::class]);
$router->get('/dashboard/maintenance', [RentalManagementController::class, 'maintenance'], [AuthMiddleware::class]);
$router->post('/dashboard/maintenance/store', [RentalManagementController::class, 'submitMaintenance'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/maintenance/{id}/assign', [RentalManagementController::class, 'assignMaintenance'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/dashboard/maintenance/{id}/resolve', [RentalManagementController::class, 'resolveMaintenance'], [CsrfMiddleware::class, AuthMiddleware::class]);

// 8. PAYMENTS, MONETIZATION & INVOICING (SECTIONS 14 & 15)
$router->get('/checkout/{plan}', [PaymentController::class, 'checkout'], [AuthMiddleware::class]);
$router->post('/checkout/process', [PaymentController::class, 'process'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->get('/checkout/verify/{gateway}', [PaymentController::class, 'verify'], [AuthMiddleware::class]);
$router->get('/dashboard/invoices/{id}', [PaymentController::class, 'showInvoice'], [AuthMiddleware::class]);
$router->post('/dashboard/payments/{id}/refund-request', [PaymentController::class, 'requestRefund'], [CsrfMiddleware::class, AuthMiddleware::class]);
$router->post('/webhook/{gateway}', [PaymentController::class, 'webhook']);

// 9. ADMINISTRATION COMMAND CENTER & VERIFICATION HQ
$router->get('/admin', [AdminController::class, 'index'], [RoleMiddleware::class]);
$router->get('/admin/plans', [AdminController::class, 'plans'], [RoleMiddleware::class]);
$router->post('/admin/plans/{id}/update', [AdminController::class, 'updatePlan'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/refunds', [AdminController::class, 'refunds'], [RoleMiddleware::class]);
$router->post('/admin/refunds/{id}/process', [AdminController::class, 'processRefund'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/banners', [AdminController::class, 'banners'], [RoleMiddleware::class]);
$router->post('/admin/banners/store', [AdminController::class, 'storeBanner'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/verifications', [AdminController::class, 'verifications'], [RoleMiddleware::class]);
$router->post('/admin/verifications/{id}/review', [AdminController::class, 'reviewVerification'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/verifications/{id}/assign', [AdminController::class, 'assignReviewer'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/listings', [AdminController::class, 'listings'], [RoleMiddleware::class]);
$router->post('/admin/listings/{id}/status', [AdminController::class, 'updateListingStatus'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/listings/{id}/delete', [AdminController::class, 'deleteListing'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/categories', [AdminController::class, 'categories'], [RoleMiddleware::class]);
$router->post('/admin/categories/store', [AdminController::class, 'storeCategory'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/amenities/store', [AdminController::class, 'storeAmenity'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/coupons', [AdminController::class, 'coupons'], [RoleMiddleware::class]);
$router->post('/admin/coupons/store', [AdminController::class, 'storeCoupon'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/users', [AdminController::class, 'users'], [RoleMiddleware::class]);
$router->post('/admin/users/{id}/toggle', [AdminController::class, 'toggleUserStatus'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/users/{id}/role', [AdminController::class, 'updateUserRole'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/users/{id}/reset-password', [AdminController::class, 'resetUserPassword'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/deletion-requests', [AdminController::class, 'deletionRequests'], [RoleMiddleware::class]);
$router->post('/admin/deletion-requests/{id}/process', [AdminController::class, 'processDeletionRequest'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/locations', [AdminController::class, 'locations'], [RoleMiddleware::class]);
$router->post('/admin/locations/store', [AdminController::class, 'storeLocation'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/locations/import', [AdminController::class, 'importLocations'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/reports', [AdminController::class, 'reports'], [RoleMiddleware::class]);
$router->post('/admin/reports/{id}/status', [AdminController::class, 'updateReportStatus'], [CsrfMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/failed-logins', [AdminController::class, 'failedLogins'], [RoleMiddleware::class]);
$router->get('/admin/audit-logs', [AdminController::class, 'auditLogs'], [RoleMiddleware::class]);
$router->get('/admin/export/users', [AdminController::class, 'exportUsers'], [RoleMiddleware::class]);
$router->get('/admin/export/listings', [AdminController::class, 'exportListings'], [RoleMiddleware::class]);
$router->get('/admin/settings', [AdminController::class, 'settings'], [RoleMiddleware::class]);
$router->post('/admin/settings', [AdminController::class, 'settings'], [CsrfMiddleware::class, RoleMiddleware::class]);

// Dispatch application
$router->dispatch($request, $response);
