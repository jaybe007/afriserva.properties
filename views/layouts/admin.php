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
    <aside class="dashboard-sidebar" style="background: #000B1A;">
        <div class="sidebar-header">
            <a href="<?= url('admin') ?>" class="brand-logo">
                <div class="brand-icon" style="background: var(--color-accent); color: #000B1A;">★</div>
                <div class="brand-text">
                    <span class="brand-name" style="color: #FFFFFF;">Afriserva</span>
                    <span class="brand-tag" style="color: var(--color-accent);">Real Estate HQ</span>
                </div>
            </a>
        </div>

        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="<?= url('admin') ?>" class="sidebar-link">
                    <span>⚡</span> System Overview
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/verifications') ?>" class="sidebar-link">
                    <span>🛡️</span> Property Verifications
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/listings') ?>" class="sidebar-link">
                    <span>📋</span> Moderation Queue
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/users') ?>" class="sidebar-link">
                    <span>👥</span> User Accounts & RBAC
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/categories') ?>" class="sidebar-link">
                    <span>🏷️</span> Categories & Amenities
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/locations') ?>" class="sidebar-link">
                    <span>🗺</span> Geographic Hierarchy
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/coupons') ?>" class="sidebar-link">
                    <span>🎟️</span> Coupons & Promotions
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/plans') ?>" class="sidebar-link">
                    <span>💎</span> Subscription Plans
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/refunds') ?>" class="sidebar-link">
                    <span>⚖️</span> Refunds & Reconciliation
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/banners') ?>" class="sidebar-link">
                    <span>📢</span> Banner Advertisements
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/reports') ?>" class="sidebar-link">
                    <span>⚠️</span> Dispute & Fraud Reports
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/failed-logins') ?>" class="sidebar-link">
                    <span>🚨</span> Threat & Failed Logins
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/audit-logs') ?>" class="sidebar-link">
                    <span>📜</span> Security Audit Logs
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/settings') ?>" class="sidebar-link">
                    <span>⚙️</span> Platform Settings
                </a>
            </li>
            <li class="sidebar-item" style="margin-top: 24px; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 16px;">
                <a href="<?= url('dashboard') ?>" class="sidebar-link">
                    <span>←</span> Return to Portal
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('/') ?>" class="sidebar-link">
                    <span>🌐</span> View Public Site
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div style="font-size: 11px; text-transform: uppercase; color: var(--color-accent); margin-bottom: 8px;">
                Clearance: Level 5 Root
            </div>
            <a href="<?= url('logout') ?>" class="btn btn-outline btn-sm" style="width: 100%; border-color: rgba(255, 255, 255, 0.2); color: #FFF;">
                Emergency Log Off
            </a>
        </div>
    </aside>

    <div class="dashboard-main">
        <header class="dashboard-topbar">
            <h2 class="topbar-title"><?= e($title ?? 'Command Center') ?></h2>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="<?= url('admin/export/listings') ?>" class="btn btn-outline btn-sm" style="font-size: 12px;">
                    📥 Export Listings CSV
                </a>
                <a href="<?= url('admin/export/users') ?>" class="btn btn-outline btn-sm" style="font-size: 12px;">
                    📥 Export Users CSV
                </a>
                <span class="badge badge-gold">Super Admin Session</span>
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
