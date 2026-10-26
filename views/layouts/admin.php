<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (!function_exists('navIsActive')) {
    function navIsActive(string $segment, string $currentPath): string {
        if ($segment === 'admin') {
            return (str_ends_with($currentPath, '/admin') || str_ends_with($currentPath, '/admin/')) ? 'active' : '';
        }
        return str_contains($currentPath, '/admin/' . $segment) ? 'active' : '';
    }
}

// Live sidebar counters
try {
    $sbVerifications = (int)\App\Core\Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE verification_status IN ('submitted', 'under_review') AND deleted_at IS NULL");
    $sbListings = (int)\App\Core\Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE status = 'pending_review' AND deleted_at IS NULL");
    $sbReports = (int)\App\Core\Database::fetchColumn("SELECT COUNT(*) FROM listing_reports WHERE status = 'pending'");
    $sbDeletions = (int)\App\Core\Database::fetchColumn("SELECT COUNT(*) FROM account_deletion_requests WHERE status = 'pending'");
} catch (\Exception $e) {
    $sbVerifications = $sbListings = $sbReports = $sbDeletions = 0;
}
$currentUser = \App\Core\Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Super Admin Command HQ | Afriserva Real Estate') ?></title>
    
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
</head>
<body>

<div class="dashboard-wrapper">
    <!-- Admin Sidebar -->
    <aside class="dashboard-sidebar" style="background: #000B1A; width: 280px;">
        <div class="sidebar-header" style="height: 80px; display: flex; align-items: center; justify-content: space-between; padding: 0 20px;">
            <a href="<?= url('admin') ?>" class="brand-logo" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                <div class="brand-icon" style="background: var(--color-accent); color: #000B1A; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800;">★</div>
                <div class="brand-text">
                    <span class="brand-name" style="color: #FFFFFF; font-weight: 700; font-size: 16px; letter-spacing: -0.5px;">Afriserva</span>
                    <span class="brand-tag" style="color: var(--color-accent); font-size: 10px; display: block; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">Root Command HQ</span>
                </div>
            </a>
        </div>

        <ul class="sidebar-nav" style="padding: 16px 12px; overflow-y: auto; max-height: calc(100vh - 170px);">
            <li class="sidebar-item">
                <a href="<?= url('admin') ?>" class="sidebar-link <?= navIsActive('admin', $currentPath) ?>">
                    <span>⚡</span> System Overview
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/verifications') ?>" class="sidebar-link <?= navIsActive('verifications', $currentPath) ?>" style="justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span>🛡️</span> Property Verifications
                    </div>
                    <?php if ($sbVerifications > 0): ?>
                        <span class="badge" style="background: #EF4444; color: #FFF; font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: 700;">
                            <?= $sbVerifications ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/listings') ?>" class="sidebar-link <?= navIsActive('listings', $currentPath) ?>" style="justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span>📋</span> Moderation Queue
                    </div>
                    <?php if ($sbListings > 0): ?>
                        <span class="badge" style="background: #F59E0B; color: #FFF; font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: 700;">
                            <?= $sbListings ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/users') ?>" class="sidebar-link <?= navIsActive('users', $currentPath) ?>">
                    <span>👥</span> User Accounts & RBAC
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/categories') ?>" class="sidebar-link <?= navIsActive('categories', $currentPath) ?>">
                    <span>🏷️</span> Categories & Amenities
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/locations') ?>" class="sidebar-link <?= navIsActive('locations', $currentPath) ?>">
                    <span>🗺</span> Geographic Hierarchy
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/coupons') ?>" class="sidebar-link <?= navIsActive('coupons', $currentPath) ?>">
                    <span>🎟️</span> Coupons & Promotions
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/plans') ?>" class="sidebar-link <?= navIsActive('plans', $currentPath) ?>">
                    <span>💎</span> Subscription Plans
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/refunds') ?>" class="sidebar-link <?= navIsActive('refunds', $currentPath) ?>">
                    <span>⚖️</span> Refunds & Reconciliation
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/banners') ?>" class="sidebar-link <?= navIsActive('banners', $currentPath) ?>">
                    <span>📢</span> Banner Advertisements
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/reports') ?>" class="sidebar-link <?= navIsActive('reports', $currentPath) ?>" style="justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span>⚠️</span> Dispute & Fraud Reports
                    </div>
                    <?php if ($sbReports > 0): ?>
                        <span class="badge" style="background: #DC2626; color: #FFF; font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: 700;">
                            <?= $sbReports ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/deletion-requests') ?>" class="sidebar-link <?= navIsActive('deletion-requests', $currentPath) ?>" style="justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span>🔒</span> NDPR Data Privacy & Erasure
                    </div>
                    <?php if ($sbDeletions > 0): ?>
                        <span class="badge" style="background: #8B5CF6; color: #FFF; font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: 700;">
                            <?= $sbDeletions ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/failed-logins') ?>" class="sidebar-link <?= navIsActive('failed-logins', $currentPath) ?>">
                    <span>🚨</span> Threat & Failed Logins
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/audit-logs') ?>" class="sidebar-link <?= navIsActive('audit-logs', $currentPath) ?>">
                    <span>📜</span> Security Audit Logs
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/settings') ?>" class="sidebar-link <?= navIsActive('settings', $currentPath) ?>">
                    <span>⚙️</span> Platform Settings
                </a>
            </li>
            <li class="sidebar-item" style="margin-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 12px;">
                <a href="<?= url('dashboard') ?>" class="sidebar-link">
                    <span>←</span> Return to User Portal
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('/') ?>" class="sidebar-link" target="_blank">
                    <span>🌐</span> View Live Marketplace ↗
                </a>
            </li>
        </ul>

        <div class="sidebar-footer" style="padding: 16px; background: rgba(0, 0, 0, 0.3);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--color-accent); color: #000B1A; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">
                    <?= strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)) ?>
                </div>
                <div style="line-height: 1.2; overflow: hidden;">
                    <div style="color: #FFF; font-size: 13px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap;"><?= e($currentUser['full_name'] ?? 'Super Admin') ?></div>
                    <div style="font-size: 10px; color: var(--color-accent); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Level 5 Root Clearance</div>
                </div>
            </div>
            <a href="<?= url('logout') ?>" class="btn btn-outline btn-sm" style="width: 100%; border-color: rgba(239, 68, 68, 0.4); color: #FCA5A5; font-size: 12px;">
                Emergency Secure Log Off
            </a>
        </div>
    </aside>

    <div class="dashboard-main">
        <header class="dashboard-topbar">
            <div>
                <h2 class="topbar-title" style="margin: 0; font-size: 20px; font-weight: 700; color: var(--color-primary);"><?= e($title ?? 'Command Center') ?></h2>
                <div style="font-size: 12px; color: var(--color-text-muted);">Afriserva Institutional Central Management</div>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="<?= url('admin/export/listings') ?>" class="btn btn-outline btn-sm" style="font-size: 12px; font-weight: 600;">
                    📥 Export Listings CSV
                </a>
                <a href="<?= url('admin/export/users') ?>" class="btn btn-outline btn-sm" style="font-size: 12px; font-weight: 600;">
                    📥 Export Users CSV
                </a>
                <span class="badge badge-gold" style="font-size: 11px; padding: 6px 12px; font-weight: 700; letter-spacing: 0.5px;">
                    👑 ROOT CLEARANCE
                </span>
            </div>
        </header>

        <div class="dashboard-body">
            <!-- Flash Notifications -->
            <?php if ($msg = flash('success')): ?>
                <div class="alert alert-success">✓ <?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('danger')): ?>
                <div class="alert alert-danger">✕ <?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('warning')): ?>
                <div class="alert alert-warning">⚠ <?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('info')): ?>
                <div class="alert alert-info">ℹ <?= e($msg) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </div>
</div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
