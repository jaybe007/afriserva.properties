<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Core\Auth;
use App\Models\Location;
use App\Models\Property;

class AdminController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $stats = [
            'total_users' => (int)Database::fetchColumn("SELECT COUNT(*) FROM users"),
            'total_listings' => (int)Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL"),
            'pending_reviews' => (int)Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE status = 'pending_review' OR verification_status IN ('submitted', 'under_review')"),
            'total_enquiries' => (int)Database::fetchColumn("SELECT COUNT(*) FROM enquiries"),
            'active_subscriptions' => (int)Database::fetchColumn("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'"),
            'total_agencies' => (int)Database::fetchColumn("SELECT COUNT(*) FROM agencies"),
            'total_revenue' => (float)Database::fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'successful'"),
            'pending_refunds' => (int)Database::fetchColumn("SELECT COUNT(*) FROM refund_requests WHERE status = 'pending'"),
            'pending_deletions' => (int)Database::fetchColumn("SELECT COUNT(*) FROM account_deletion_requests WHERE status = 'pending'"),
            'unresolved_reports' => (int)Database::fetchColumn("SELECT COUNT(*) FROM listing_reports WHERE status = 'pending'"),
            'recent_failed_logins' => (int)Database::fetchColumn("SELECT COUNT(*) FROM login_attempts WHERE attempted_at >= (NOW() - INTERVAL 24 HOUR)")
        ];

        $pendingListings = Database::query(
            "SELECT p.*, u.full_name AS agent_name, u.email AS agent_email, s.name AS state_name, ci.name AS city_name
             FROM properties p
             JOIN users u ON u.id = p.user_id
             JOIN states s ON s.id = p.state_id
             JOIN cities ci ON ci.id = p.city_id
             WHERE p.status = 'pending_review' OR p.verification_status IN ('submitted', 'under_review')
             ORDER BY p.created_at DESC
             LIMIT 10"
        );

        $recentUsers = Database::query(
            "SELECT u.*, (SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = u.id LIMIT 1) AS role_name
             FROM users u
             ORDER BY u.created_at DESC
             LIMIT 8"
        );

        $html = $this->render('admin/index', [
            'title' => 'Executive Administrative Command Center | Afriserva Real Estate',
            'stats' => $stats,
            'pendingListings' => $pendingListings,
            'recentUsers' => $recentUsers
        ], 'layouts/admin');

        $response->html($html);
    }

    public function verifications(Request $request, Response $response): void
    {
        $verifications = Database::query(
            "SELECT p.*, u.full_name AS agent_name, u.email AS agent_email, u.phone AS agent_phone,
                    s.name AS state_name, ci.name AS city_name,
                    rev.full_name AS reviewer_name
             FROM properties p
             JOIN users u ON u.id = p.user_id
             JOIN states s ON s.id = p.state_id
             JOIN cities ci ON ci.id = p.city_id
             LEFT JOIN users rev ON rev.id = p.assigned_reviewer_id
             WHERE p.deleted_at IS NULL
             ORDER BY 
                CASE 
                    WHEN p.verification_status IN ('submitted', 'under_review') THEN 1
                    WHEN p.verification_status = 'additional_info_required' THEN 2
                    WHEN p.verification_status = 'verified' THEN 3
                    ELSE 4
                END,
                p.created_at DESC"
        );

        // Attach documents and duplicate warnings to each listing
        foreach ($verifications as &$item) {
            $item['documents'] = Database::query(
                "SELECT * FROM property_documents WHERE property_id = ? ORDER BY created_at DESC",
                [$item['id']]
            );
            $item['duplicate_candidates'] = Property::detectDuplicates(
                $item['title'],
                $item['address'],
                (float)$item['price'],
                (int)$item['state_id'],
                $item['id']
            );
        }
        unset($item);

        $moderators = Database::query(
            "SELECT u.id, u.full_name, u.email 
             FROM users u
             JOIN user_roles ur ON ur.user_id = u.id
             JOIN roles r ON r.id = ur.role_id
             WHERE r.name IN ('super_admin', 'moderator')"
        );

        $html = $this->render('admin/verifications', [
            'title' => 'Property Verification & Title Due Diligence | Afriserva Real Estate',
            'verifications' => $verifications,
            'moderators' => $moderators
        ], 'layouts/admin');

        $response->html($html);
    }

    public function reviewVerification(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $status = $request->input('verification_status');
        $reason = $request->input('rejection_reason');
        $notes = $request->input('notes');
        $reviewerId = Auth::id();

        Property::updateVerificationStatus($id, $status, $reviewerId, $reason, $notes);

        flash('success', "Property #{$id} verification decision recorded as " . strtoupper($status));
        $this->redirect($response, url('admin/verifications'));
    }

    public function assignReviewer(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $reviewerId = (int)$request->input('reviewer_id');

        if ($reviewerId) {
            Property::assignReviewer($id, $reviewerId);
            flash('success', "Property #{$id} assigned to reviewer #{$reviewerId}.");
        }

        $this->redirect($response, url('admin/verifications'));
    }

    public function listings(Request $request, Response $response): void
    {
        $q = trim($request->input('q', ''));
        $statusFilter = trim($request->input('status', ''));
        $verifFilter = trim($request->input('verification', ''));

        $where = ["p.deleted_at IS NULL"];
        $params = [];

        if (!empty($q)) {
            $where[] = "(p.title LIKE ? OR p.reference_code LIKE ? OR u.full_name LIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        if (!empty($statusFilter)) {
            $where[] = "p.status = ?";
            $params[] = $statusFilter;
        }

        if (!empty($verifFilter)) {
            $where[] = "p.verification_status = ?";
            $params[] = $verifFilter;
        }

        $whereSql = implode(" AND ", $where);

        $properties = Database::query(
            "SELECT p.*, u.full_name AS agent_name, s.name AS state_name, ci.name AS city_name,
                    (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY is_cover DESC LIMIT 1) AS cover_image
             FROM properties p
             JOIN users u ON u.id = p.user_id
             JOIN states s ON s.id = p.state_id
             JOIN cities ci ON ci.id = p.city_id
             WHERE {$whereSql}
             ORDER BY p.created_at DESC",
            $params
        );

        $counts = [
            'total' => (int)Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL"),
            'pending' => (int)Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE status = 'pending_review' AND deleted_at IS NULL"),
            'published' => (int)Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE status = 'published' AND deleted_at IS NULL"),
            'rejected' => (int)Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE status = 'rejected' AND deleted_at IS NULL"),
        ];

        $html = $this->render('admin/listings', [
            'title' => 'Property Listing Moderation & Verification | Afriserva Real Estate',
            'properties' => $properties,
            'counts' => $counts,
            'filters' => [
                'q' => $q,
                'status' => $statusFilter,
                'verification' => $verifFilter
            ]
        ], 'layouts/admin');

        $response->html($html);
    }

    public function updateListingStatus(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $action = $request->input('action');

        if ($action === 'approve') {
            Database::execute("UPDATE properties SET status = 'published', verification_status = 'verified' WHERE id = ?", [$id]);
            \App\Helpers\AuditLogger::log('property_approve', 'property', $id, null, ['status' => 'published', 'verification' => 'verified']);
            flash('success', "Property #{$id} has been verified and published live.");
        } elseif ($action === 'reject') {
            Database::execute("UPDATE properties SET status = 'rejected', verification_status = 'rejected' WHERE id = ?", [$id]);
            \App\Helpers\AuditLogger::log('property_reject', 'property', $id, null, ['status' => 'rejected', 'verification' => 'rejected']);
            flash('warning', "Property #{$id} was marked as rejected.");
        } elseif ($action === 'toggle_featured') {
            Database::execute("UPDATE properties SET is_featured = 1 - is_featured WHERE id = ?", [$id]);
            \App\Helpers\AuditLogger::log('property_toggle_featured', 'property', $id);
            flash('success', "Featured status updated for property #{$id}.");
        }

        $this->redirect($response, url('admin/listings'));
    }

    public function deleteListing(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        if ($id > 0) {
            Database::execute("UPDATE properties SET deleted_at = NOW(), status = 'archived' WHERE id = ?", [$id]);
            \App\Helpers\AuditLogger::log('property_deleted_admin', 'property', $id);
            flash('info', "Property #{$id} has been removed from marketplace inventory.");
        }
        $this->redirect($response, url('admin/listings'));
    }

    public function reports(Request $request, Response $response): void
    {
        $reports = Database::query(
            "SELECT lr.*, p.title AS property_title, p.slug AS property_slug, p.reference_code,
                    u.full_name AS reporter_name, u.email AS reporter_email
             FROM listing_reports lr
             JOIN properties p ON p.id = lr.property_id
             LEFT JOIN users u ON u.id = lr.user_id
             ORDER BY lr.created_at DESC"
        );

        $html = $this->render('admin/reports', [
            'title' => 'Compliance Dispute & Fraud Reports | Afriserva Real Estate',
            'reports' => $reports
        ], 'layouts/admin');

        $response->html($html);
    }

    public function updateReportStatus(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $status = $request->input('status');

        Database::execute("UPDATE listing_reports SET status = ? WHERE id = ?", [$status, $id]);
        \App\Helpers\AuditLogger::log('report_status_update', 'listing_report', $id, null, ['status' => $status]);

        flash('success', "Report #{$id} status updated to {$status}.");
        $this->redirect($response, url('admin/reports'));
    }

    public function auditLogs(Request $request, Response $response): void
    {
        $logs = Database::query(
            "SELECT al.*, u.full_name AS operator_name, u.email AS operator_email
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT 100"
        );

        $html = $this->render('admin/audit_logs', [
            'title' => 'Administrative Security & Operational Audit Log | Afriserva Real Estate',
            'logs' => $logs
        ], 'layouts/admin');

        $response->html($html);
    }

    public function failedLogins(Request $request, Response $response): void
    {
        $failedLogins = Database::query(
            "SELECT * FROM login_attempts ORDER BY attempted_at DESC LIMIT 100"
        );

        $html = $this->render('admin/failed_logins', [
            'title' => 'Failed Logins & Threat Intelligence Surveillance | Afriserva Real Estate',
            'failedLogins' => $failedLogins
        ], 'layouts/admin');

        $response->html($html);
    }

    public function categories(Request $request, Response $response): void
    {
        $categories = Database::query(
            "SELECT pc.*, 
                    (SELECT COUNT(*) FROM properties p WHERE p.category_id = pc.id AND p.deleted_at IS NULL) AS property_count
             FROM property_categories pc
             ORDER BY pc.display_order ASC"
        );

        $amenities = Database::query(
            "SELECT * FROM amenities ORDER BY group_name ASC, name ASC"
        );

        $html = $this->render('admin/categories', [
            'title' => 'Categories & Amenities Management | Afriserva Real Estate',
            'categories' => $categories,
            'amenities' => $amenities
        ], 'layouts/admin');

        $response->html($html);
    }

    public function storeCategory(Request $request, Response $response): void
    {
        $name = trim($request->input('name', ''));
        $icon = trim($request->input('icon', '🏢'));

        if (!empty($name)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            Database::execute(
                "INSERT INTO property_categories (name, slug, icon, is_active, display_order) VALUES (?, ?, ?, 1, 99)",
                [$name, $slug, $icon]
            );
            \App\Helpers\AuditLogger::log('category_created', 'property_category', (int)Database::lastInsertId(), null, ['name' => $name]);
            flash('success', "Category '{$name}' created.");
        }

        $this->redirect($response, url('admin/categories'));
    }

    public function storeAmenity(Request $request, Response $response): void
    {
        $name = trim($request->input('name', ''));
        $group = trim($request->input('group_name', 'general'));

        if (!empty($name)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            Database::execute(
                "INSERT INTO amenities (name, slug, group_name) VALUES (?, ?, ?)",
                [$name, $slug, $group]
            );
            \App\Helpers\AuditLogger::log('amenity_created', 'amenity', (int)Database::lastInsertId(), null, ['name' => $name]);
            flash('success', "Amenity '{$name}' created.");
        }

        $this->redirect($response, url('admin/categories'));
    }

    public function coupons(Request $request, Response $response): void
    {
        $coupons = Database::query("SELECT * FROM coupons ORDER BY created_at DESC");

        $html = $this->render('admin/coupons', [
            'title' => 'Promotional Campaigns & Coupons | Afriserva Real Estate',
            'coupons' => $coupons
        ], 'layouts/admin');

        $response->html($html);
    }

    public function storeCoupon(Request $request, Response $response): void
    {
        $code = strtoupper(trim($request->input('code', '')));
        $type = $request->input('discount_type', 'percentage');
        $value = (float)$request->input('discount_value', 0);
        $maxUses = !empty($request->input('max_uses')) ? (int)$request->input('max_uses') : null;

        if (!empty($code) && $value > 0) {
            Database::execute(
                "INSERT INTO coupons (code, discount_type, discount_value, max_uses, is_active, created_at)
                 VALUES (?, ?, ?, ?, 1, NOW())",
                [$code, $type, $value, $maxUses]
            );
            \App\Helpers\AuditLogger::log('coupon_created', 'coupon', (int)Database::lastInsertId(), null, ['code' => $code, 'value' => $value]);
            flash('success', "Promotional voucher code '{$code}' activated.");
        }

        $this->redirect($response, url('admin/coupons'));
    }

    public function users(Request $request, Response $response): void
    {
        $q = trim($request->input('q', ''));
        $roleFilter = trim($request->input('role', ''));
        $statusFilter = trim($request->input('status', ''));

        $where = ["1=1"];
        $params = [];

        if (!empty($q)) {
            $where[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        if (!empty($roleFilter)) {
            $where[] = "EXISTS (SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = u.id AND r.name = ?)";
            $params[] = $roleFilter;
        }

        if (!empty($statusFilter)) {
            $where[] = "u.status = ?";
            $params[] = $statusFilter;
        }

        $whereSql = implode(" AND ", $where);

        $users = Database::query(
            "SELECT u.*, 
                    (SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = u.id LIMIT 1) AS role_name,
                    (SELECT r.label FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = u.id LIMIT 1) AS role_label,
                    (SELECT COUNT(*) FROM properties p WHERE p.user_id = u.id AND p.deleted_at IS NULL) AS total_listings
             FROM users u
             WHERE {$whereSql}
             ORDER BY u.created_at DESC",
            $params
        );

        $roles = Database::query("SELECT id, name, label FROM roles ORDER BY label ASC");

        $counts = [
            'total' => (int)Database::fetchColumn("SELECT COUNT(*) FROM users"),
            'active' => (int)Database::fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'active'"),
            'suspended' => (int)Database::fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'suspended'"),
            'agents' => (int)Database::fetchColumn("SELECT COUNT(*) FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE r.name = 'agent'"),
            'landlords' => (int)Database::fetchColumn("SELECT COUNT(*) FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE r.name = 'landlord'")
        ];

        $html = $this->render('admin/users', [
            'title' => 'User Management & Clearance | Afriserva Real Estate',
            'users' => $users,
            'roles' => $roles,
            'counts' => $counts,
            'filters' => [
                'q' => $q,
                'role' => $roleFilter,
                'status' => $statusFilter
            ]
        ], 'layouts/admin');

        $response->html($html);
    }

    public function toggleUserStatus(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);

        // Security check: Prevent super admin from accidentally suspending own account
        if ($id === (int)Auth::id()) {
            flash('danger', 'Security Protection: You cannot suspend your own active administrator account.');
            $this->redirect($response, url('admin/users'));
            return;
        }

        $user = Database::fetch("SELECT status FROM users WHERE id = ?", [$id]);
        if ($user) {
            $newStatus = ($user['status'] === 'active') ? 'suspended' : 'active';
            Database::execute("UPDATE users SET status = ? WHERE id = ?", [$newStatus, $id]);
            \App\Helpers\AuditLogger::log('user_status_toggle', 'user', $id, ['status' => $user['status']], ['status' => $newStatus]);
            flash('info', "User #{$id} status changed to {$newStatus}.");
        }
        $this->redirect($response, url('admin/users'));
    }

    public function updateUserRole(Request $request, Response $response, array $params): void
    {
        $userId = (int)($params['id'] ?? 0);
        $roleName = trim($request->input('role_name', ''));

        if ($userId <= 0 || empty($roleName)) {
            flash('danger', 'Invalid user or role selection.');
            $this->redirect($response, url('admin/users'));
            return;
        }

        $role = Database::fetch("SELECT id, name, label FROM roles WHERE name = ?", [$roleName]);
        if (!$role) {
            flash('danger', "Role '{$roleName}' does not exist.");
            $this->redirect($response, url('admin/users'));
            return;
        }

        $prevRole = Database::fetchColumn(
            "SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = ? LIMIT 1",
            [$userId]
        );

        Database::execute("DELETE FROM user_roles WHERE user_id = ?", [$userId]);
        Database::execute("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)", [$userId, $role['id']]);

        \App\Helpers\AuditLogger::log('user_role_updated', 'user', $userId, ['role' => $prevRole], ['role' => $roleName]);
        flash('success', "Role for User #{$userId} updated to " . strtoupper($role['label']) . ".");
        $this->redirect($response, url('admin/users'));
    }

    public function resetUserPassword(Request $request, Response $response, array $params): void
    {
        $userId = (int)($params['id'] ?? 0);
        $customPass = trim($request->input('new_password', ''));

        if ($userId <= 0) {
            flash('danger', 'Invalid user account.');
            $this->redirect($response, url('admin/users'));
            return;
        }

        if (empty($customPass)) {
            $generatedPassword = 'Pass#' . bin2hex(random_bytes(4)) . '!2026';
        } else {
            if (strlen($customPass) < 8) {
                flash('danger', 'Password must be at least 8 characters long.');
                $this->redirect($response, url('admin/users'));
                return;
            }
            $generatedPassword = $customPass;
        }

        $hash = password_hash($generatedPassword, PASSWORD_ARGON2ID);
        Database::execute("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?", [$hash, $userId]);
        \App\Helpers\AuditLogger::log('user_password_admin_reset', 'user', $userId);

        flash('success', "Password for User #{$userId} reset successfully! Temporary Password: <strong>{$generatedPassword}</strong> (Please share securely with the user).");
        $this->redirect($response, url('admin/users'));
    }

    public function exportUsers(Request $request, Response $response): void
    {
        $users = Database::query(
            "SELECT u.id, u.uuid, u.full_name, u.email, u.phone, u.status, u.email_verified_at, u.created_at,
                    (SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = u.id LIMIT 1) AS role_name
             FROM users u
             ORDER BY u.created_at DESC"
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=users_export_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['User ID', 'UUID', 'Full Name', 'Email', 'Phone', 'Role', 'Status', 'Email Verified', 'Created At']);

        foreach ($users as $u) {
            fputcsv($output, [
                $u['id'],
                $u['uuid'],
                $u['full_name'],
                $u['email'],
                $u['phone'],
                strtoupper($u['role_name'] ?? 'client'),
                strtoupper($u['status']),
                $u['email_verified_at'] ? 'YES' : 'NO',
                $u['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    public function exportListings(Request $request, Response $response): void
    {
        $listings = Database::query(
            "SELECT p.reference_code, p.title, p.transaction_type, p.price, p.currency, p.status, p.verification_status,
                    s.name AS state_name, ci.name AS city_name, u.full_name AS agent_name, p.created_at
             FROM properties p
             JOIN states s ON s.id = p.state_id
             JOIN cities ci ON ci.id = p.city_id
             JOIN users u ON u.id = p.user_id
             WHERE p.deleted_at IS NULL
             ORDER BY p.created_at DESC"
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=admin_listings_export_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Reference Code', 'Title', 'Transaction Type', 'Price', 'Currency', 'State', 'City', 'Listed By', 'Status', 'Verification', 'Created At']);

        foreach ($listings as $l) {
            fputcsv($output, [
                $l['reference_code'],
                $l['title'],
                strtoupper($l['transaction_type']),
                $l['price'],
                $l['currency'],
                $l['state_name'],
                $l['city_name'],
                $l['agent_name'],
                strtoupper($l['status']),
                strtoupper($l['verification_status']),
                $l['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    public function settings(Request $request, Response $response): void
    {
        if ($request->isPost()) {
            $post = $request->all();
            foreach ($post as $key => $value) {
                if ($key === '_csrf_token') continue;
                Database::execute(
                    "UPDATE system_settings SET `value` = ? WHERE `key` = ?",
                    [trim($value), $key]
                );
            }
            flash('success', 'System parameters and operational configurations updated successfully.');
            $this->redirect($response, url('admin/settings'));
            return;
        }

        $settings = Database::query("SELECT * FROM system_settings ORDER BY `group` ASC, `key` ASC");

        $html = $this->render('admin/settings', [
            'title' => 'Global Platform Configuration & API Keys | Afriserva Real Estate',
            'settings' => $settings
        ], 'layouts/admin');

        $response->html($html);
    }

    public function locations(Request $request, Response $response): void
    {
        $countries = Database::query("SELECT * FROM countries ORDER BY name ASC");
        $states = Database::query(
            "SELECT s.*, c.name AS country_name,
                    (SELECT COUNT(*) FROM cities ci WHERE ci.state_id = s.id) AS cities_count,
                    (SELECT COUNT(*) FROM local_governments lg WHERE lg.state_id = s.id) AS lgas_count,
                    (SELECT COUNT(*) FROM properties p WHERE p.state_id = s.id) AS properties_count
             FROM states s
             JOIN countries c ON c.id = s.country_id
             ORDER BY c.name ASC, s.name ASC"
        );
        $cities = Database::query(
            "SELECT ci.*, s.name AS state_name 
             FROM cities ci 
             JOIN states s ON s.id = ci.state_id 
             ORDER BY s.name ASC, ci.name ASC"
        );
        $lgas = Database::query(
            "SELECT lg.*, s.name AS state_name 
             FROM local_governments lg 
             JOIN states s ON s.id = lg.state_id 
             ORDER BY s.name ASC, lg.name ASC"
        );

        $html = $this->render('admin/locations', [
            'title' => 'Geographic Location Management (36 States, LGAs & Global) | Afriserva Real Estate',
            'countries' => $countries,
            'states' => $states,
            'cities' => $cities,
            'lgas' => $lgas
        ], 'layouts/admin');

        $response->html($html);
    }

    public function storeLocation(Request $request, Response $response): void
    {
        $input = $request->all();
        $type = $input['location_type'] ?? 'state';
        $name = trim($input['name'] ?? '');

        if (empty($name)) {
            flash('danger', 'Location name is required.');
            $this->redirect($response, url('admin/locations'));
            return;
        }

        switch ($type) {
            case 'city':
                $stateId = (int)($input['state_id'] ?? 1);
                Location::createCity(['state_id' => $stateId, 'name' => $name]);
                flash('success', "City '{$name}' created successfully.");
                break;
            case 'lga':
                $stateId = (int)($input['state_id'] ?? 1);
                Location::createLga(['state_id' => $stateId, 'name' => $name]);
                flash('success', "Local Government Area (LGA) '{$name}' created successfully.");
                break;
            case 'neighbourhood':
                $cityId = (int)($input['city_id'] ?? 1);
                $lgaId = !empty($input['lga_id']) ? (int)$input['lga_id'] : null;
                Location::createNeighbourhood(['city_id' => $cityId, 'lga_id' => $lgaId, 'name' => $name]);
                flash('success', "Neighbourhood '{$name}' created successfully.");
                break;
            case 'state':
            default:
                $countryId = (int)($input['country_id'] ?? 1);
                $code = trim($input['code'] ?? '');
                Location::createState(['country_id' => $countryId, 'name' => $name, 'code' => $code]);
                flash('success', "State / Territory '{$name}' indexed successfully.");
                break;
        }

        $this->redirect($response, url('admin/locations'));
    }

    public function deletionRequests(Request $request, Response $response): void
    {
        $requests = Database::query(
            "SELECT adr.*, u.full_name, u.email,
                    (SELECT COUNT(*) FROM properties p WHERE p.user_id = u.id AND p.deleted_at IS NULL) AS active_listings
             FROM account_deletion_requests adr
             JOIN users u ON u.id = adr.user_id
             ORDER BY adr.created_at DESC"
        );

        $html = $this->render('admin/deletion_requests', [
            'title' => 'Account Deletion & Data Privacy Requests | Afriserva Real Estate',
            'requests' => $requests
        ], 'layouts/admin');

        $response->html($html);
    }

    public function processDeletionRequest(Request $request, Response $response, array $params): void
    {
        $reqId = (int)($params['id'] ?? 0);
        $decision = $request->input('decision', 'approve');
        $userId = (int)$request->input('user_id', 0);

        if ($decision === 'approve' && $userId > 0) {
            Database::execute(
                "UPDATE users SET 
                    full_name = 'Anonymized User', 
                    email = CONCAT('purged_', id, '@afriserva.local'), 
                    phone = NULL, 
                    bio = NULL, 
                    avatar_url = NULL, 
                    status = 'suspended',
                    updated_at = NOW() 
                 WHERE id = ?",
                [$userId]
            );
            Database::execute("UPDATE account_deletion_requests SET status = 'approved', reviewed_at = NOW() WHERE id = ?", [$reqId]);
            \App\Helpers\AuditLogger::log('account_deletion_approved', 'user', $userId, null, ['request_id' => $reqId]);
            flash('success', "Account #{$userId} has been purged and personal data anonymized under NDPR regulations.");
        } else {
            Database::execute("UPDATE account_deletion_requests SET status = 'rejected', reviewed_at = NOW() WHERE id = ?", [$reqId]);
            \App\Helpers\AuditLogger::log('account_deletion_rejected', 'user', $userId, null, ['request_id' => $reqId]);
            flash('warning', "Account deletion request #{$reqId} was rejected.");
        }

        $this->redirect($response, url('admin/deletion-requests'));
    }

    public function importLocations(Request $request, Response $response): void
    {
        $csvData = trim($request->input('csv_data', ''));
        $stateId = (int)$request->input('state_id', 1);

        if (empty($csvData)) {
            flash('danger', 'CSV dataset content is required for location import.');
            $this->redirect($response, url('admin/locations'));
            return;
        }

        $lines = explode("\n", str_replace("\r", "", $csvData));
        $imported = 0;

        foreach ($lines as $line) {
            $name = trim($line);
            if (!empty($name) && strtolower($name) !== 'name' && strtolower($name) !== 'lga') {
                Location::createLga(['state_id' => $stateId, 'name' => $name]);
                $imported++;
            }
        }

        \App\Helpers\AuditLogger::log('locations_dataset_imported', 'local_governments', $stateId, null, ['count' => $imported]);
        flash('success', "Successfully validated and imported {$imported} local government areas into state registry.");
        $this->redirect($response, url('admin/locations'));
    }

    public function plans(Request $request, Response $response): void
    {
        $plans = \App\Models\Subscription::getAllPlans();

        $html = $this->render('admin/plans/index', [
            'title' => 'Monetization & Subscription Plans Management | Afriserva Real Estate',
            'plans' => $plans
        ], 'layouts/admin');

        $response->html($html);
    }

    public function updatePlan(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $data = [
            'name' => $request->input('name'),
            'price' => (float)$request->input('price', 0),
            'currency' => $request->input('currency', 'NGN'),
            'billing_interval' => $request->input('billing_interval', 'monthly'),
            'listing_allowance' => (int)$request->input('listing_allowance', 5),
            'featured_allowance' => (int)$request->input('featured_allowance', 1),
            'team_members_limit' => (int)$request->input('team_members_limit', 1),
            'listing_duration_days' => (int)$request->input('listing_duration_days', 30),
            'grace_period_days' => (int)$request->input('grace_period_days', 7),
            'analytics_level' => $request->input('analytics_level', 'basic'),
            'renewal_policy' => $request->input('renewal_policy', 'flexible'),
            'lead_price' => (float)$request->input('lead_price', 0),
            'is_active' => (int)$request->input('is_active', 1)
        ];

        \App\Models\Subscription::updatePlan($id, $data);
        \App\Helpers\AuditLogger::log('subscription_plan_updated', 'subscription_plans', $id, null, $data);

        flash('success', "Subscription plan terms for '{$data['name']}' updated successfully.");
        $this->redirect($response, url('admin/plans'));
    }

    public function refunds(Request $request, Response $response): void
    {
        $refunds = \App\Models\RefundRequest::getAll();

        $html = $this->render('admin/refunds/index', [
            'title' => 'Refund Management & Reconciliation HQ | Afriserva Real Estate',
            'refunds' => $refunds
        ], 'layouts/admin');

        $response->html($html);
    }

    public function processRefund(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $status = $request->input('status', 'approved');
        $adminNotes = $request->input('admin_notes', 'Refund authorized per terms of service.');
        $reviewerId = \App\Core\Auth::id();

        \App\Models\RefundRequest::process($id, $status, $adminNotes, $reviewerId);
        \App\Helpers\AuditLogger::log('refund_processed', 'refund_requests', $id, null, ['status' => $status, 'notes' => $adminNotes]);

        flash('success', "Refund request #{$id} status transitioned to: {$status}.");
        $this->redirect($response, url('admin/refunds'));
    }

    public function banners(Request $request, Response $response): void
    {
        $banners = \App\Models\BannerAd::getAll();

        $html = $this->render('admin/banners/index', [
            'title' => 'Banner Advertisements Management | Afriserva Real Estate',
            'banners' => $banners
        ], 'layouts/admin');

        $response->html($html);
    }

    public function storeBanner(Request $request, Response $response): void
    {
        $input = $request->all();
        \App\Models\BannerAd::create($input);
        \App\Helpers\AuditLogger::log('banner_ad_created', 'banner_advertisements', null, null, ['title' => $input['title']]);

        flash('success', "Banner advertisement '{$input['title']}' published successfully.");
        $this->redirect($response, url('admin/banners'));
    }
}

