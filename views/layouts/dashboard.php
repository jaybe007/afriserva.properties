<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Portal Dashboard | Afriserva Real Estate') ?></title>
    
    <!-- CSS Design System -->
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
</head>
<body>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-header">
            <a href="<?= url('/') ?>" class="brand-logo">
                <div class="brand-icon">A</div>
                <div class="brand-text">
                    <span class="brand-name" style="color: #FFFFFF;">Afriserva</span>
                    <span class="brand-tag">Real Estate</span>
                </div>
            </a>
        </div>

        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="<?= url('dashboard') ?>" class="sidebar-link active">
                    <span>📊</span> Overview
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('dashboard/saved') ?>" class="sidebar-link">
                    <span>❤️</span> Saved Shortlist
                </a>
            </li>

            <li class="sidebar-item">
                <a href="<?= url('dashboard/messages') ?>" class="sidebar-link">
                    <span>💬</span> Messages & Chat
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('dashboard/notifications') ?>" class="sidebar-link">
                    <span>🔔</span> Notifications
                </a>
            </li>

            <?php if (has_role(['agent', 'agency_admin', 'landlord', 'developer', 'seller', 'property_manager', 'super_admin'])): ?>
                <li class="sidebar-item">
                    <a href="<?= url('dashboard/listings') ?>" class="sidebar-link">
                        <span>🏢</span> My Listings
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?= url('dashboard/listings/create') ?>" class="sidebar-link">
                        <span>➕</span> Create Listing
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?= url('dashboard/enquiries') ?>" class="sidebar-link">
                        <span>✉️</span> Leads & Enquiries
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?= url('dashboard/viewings') ?>" class="sidebar-link">
                        <span>📅</span> Inspection Tours
                    </a>
                </li>
                <?php if (has_role(['agency_admin', 'super_admin'])): ?>
                    <li class="sidebar-item">
                        <a href="<?= url('dashboard/team') ?>" class="sidebar-link">
                            <span>👥</span> Agency Team
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (has_role(['landlord', 'property_manager', 'developer', 'agent', 'agency_admin', 'super_admin'])): ?>
                    <li class="sidebar-item">
                        <a href="<?= url('dashboard/rental-applications') ?>" class="sidebar-link">
                            <span>📑</span> Rental Applications
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="<?= url('dashboard/leases') ?>" class="sidebar-link">
                            <span>📜</span> Tenancy & Leases
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="<?= url('dashboard/maintenance') ?>" class="sidebar-link">
                            <span>🔧</span> Maintenance Hub
                        </a>
                    </li>
                <?php endif; ?>
                <li class="sidebar-item">
                    <a href="<?= url('dashboard/invoices') ?>" class="sidebar-link">
                        <span>💳</span> Invoices & Billing
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?= url('dashboard/subscription') ?>" class="sidebar-link">
                        <span>⭐</span> Membership Tier
                    </a>
                </li>
            <?php else: ?>
                <li class="sidebar-item">
                    <a href="<?= url('dashboard/viewings') ?>" class="sidebar-link">
                        <span>📅</span> My Inspection Tours
                    </a>
                </li>
                <?php if (has_role('tenant')): ?>
                    <li class="sidebar-item">
                        <a href="<?= url('dashboard/leases') ?>" class="sidebar-link">
                            <span>📜</span> My Active Leases
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="<?= url('dashboard/rental-applications') ?>" class="sidebar-link">
                            <span>📑</span> My Tenancy Apps
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="<?= url('dashboard/maintenance') ?>" class="sidebar-link">
                            <span>🔧</span> Maintenance Requests
                        </a>
                    </li>
                <?php else: ?>
                    <li class="sidebar-item">
                        <a href="<?= url('dashboard/rental-applications') ?>" class="sidebar-link">
                            <span>📑</span> My Tenancy Apps
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="<?= url('dashboard/leases') ?>" class="sidebar-link">
                            <span>📜</span> My Leases
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="<?= url('dashboard/maintenance') ?>" class="sidebar-link">
                            <span>🔧</span> Maintenance Requests
                        </a>
                    </li>
                <?php endif; ?>
                <li class="sidebar-item">
                    <a href="<?= url('properties') ?>" class="sidebar-link">
                        <span>🔍</span> Browse Listings
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?= url('properties/map') ?>" class="sidebar-link">
                        <span>🗺</span> Map Explorer
                    </a>
                </li>
            <?php endif; ?>

            <li class="sidebar-item">
                <a href="<?= url('dashboard/profile') ?>" class="sidebar-link">
                    <span>👤</span> Profile & Security
                </a>
            </li>

            <?php if (has_role('super_admin') || has_role('moderator')): ?>
                <li class="sidebar-item" style="margin-top: 24px; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 16px;">
                    <a href="<?= url('admin') ?>" class="sidebar-link" style="color: var(--color-accent);">
                        <span>🛡</span> Super Admin HQ
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-footer">
            <div style="font-size: 13px; color: #FFFFFF; font-weight: 600; margin-bottom: 4px;">
                <?= e(auth_user()['full_name'] ?? 'User') ?>
            </div>
            <div style="font-size: 11px; text-transform: uppercase; color: var(--color-accent); margin-bottom: 16px; letter-spacing: 0.5px;">
                <?= e(format_role_label(auth_user()['roles'][0] ?? 'client')) ?>
            </div>
            <a href="<?= url('logout') ?>" class="btn btn-outline btn-sm" style="width: 100%; border-color: rgba(255, 255, 255, 0.2); color: #FFF;">
                Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="dashboard-main">
        <header class="dashboard-topbar">
            <h2 class="topbar-title"><?= e($title ?? 'Dashboard') ?></h2>
            <div class="topbar-user">
                <a href="<?= url('/') ?>" class="btn btn-outline btn-sm">Public Website ↗</a>
                <span class="badge badge-verified">Active Account</span>
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
