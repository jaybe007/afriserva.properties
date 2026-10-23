<!-- System Health & Platform Integrity Strip -->
<div style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color: #FFFFFF; border-radius: var(--radius-lg); padding: 20px 24px; margin-bottom: 24px; box-shadow: var(--shadow-luxury);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10B981; box-shadow: 0 0 8px #10B981;"></span>
                <h4 style="font-size: 15px; font-weight: 700; color: var(--color-accent); margin: 0;">
                    Infrastructure Health & Operational Status
                </h4>
            </div>
            <div style="font-size: 12px; color: #94A3B8;">
                Real-time operational indicators for legal compliance, database connections, and memory overhead.
            </div>
        </div>
        <div style="display: flex; gap: 20px; align-items: center; font-size: 13px; flex-wrap: wrap;">
            <div>
                <span style="color: #94A3B8;">PHP Runtime:</span> <strong><?= PHP_VERSION ?></strong>
            </div>
            <div>
                <span style="color: #94A3B8;">DB Engine:</span> <strong>MySQL 8.0+ (InnoDB)</strong>
            </div>
            <div>
                <span style="color: #94A3B8;">Memory Allocated:</span> <strong><?= round(memory_get_usage(true) / 1024 / 1024, 2) ?> MB</strong>
            </div>
            <div>
                <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 11px; padding: 4px 10px;">
                    ● All Systems Operational
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Compliance & Security Alert Banners -->
<?php if (!empty($stats['pending_deletions']) && $stats['pending_deletions'] > 0): ?>
    <div class="alert alert-warning" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; background: #FEF3C7; border-color: #F59E0B; color: #92400E;">
        <div>
            <strong>⚠️ NDPR Statutory Notice:</strong> <?= $stats['pending_deletions'] ?> pending account deletion/anonymization requests awaiting compliance action.
        </div>
        <a href="<?= url('admin/deletion-requests') ?>" class="btn btn-sm btn-outline" style="border-color: #92400E; color: #92400E;">Review Requests →</a>
    </div>
<?php endif; ?>

<?php if (!empty($stats['unresolved_reports']) && $stats['unresolved_reports'] > 0): ?>
    <div class="alert alert-danger" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <strong>🚨 Fraud & Dispute Alert:</strong> <?= $stats['unresolved_reports'] ?> consumer listing complaints pending investigation.
        </div>
        <a href="<?= url('admin/reports') ?>" class="btn btn-sm btn-primary">Audit Reports →</a>
    </div>
<?php endif; ?>

<?php if (!empty($stats['pending_refunds']) && $stats['pending_refunds'] > 0): ?>
    <div class="alert alert-info" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <strong>⚖️ Financial Reconciliation:</strong> <?= $stats['pending_refunds'] ?> payment refund requests require administrative decision.
        </div>
        <a href="<?= url('admin/refunds') ?>" class="btn btn-sm btn-outline">Process Refunds →</a>
    </div>
<?php endif; ?>

<!-- Quick Actions Command Bar -->
<div class="table-card" style="margin-bottom: 24px; padding: 16px 20px;">
    <div style="font-size: 12px; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px;">
        ⚡ Instant Executive Actions
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= url('admin/verifications') ?>" class="btn btn-primary btn-sm">
            🛡️ Verify Pending Deeds (<?= $stats['pending_reviews'] ?>)
        </a>
        <a href="<?= url('admin/listings') ?>" class="btn btn-outline btn-sm">
            📋 Moderate Listings
        </a>
        <a href="<?= url('admin/users') ?>" class="btn btn-outline btn-sm">
            👥 User Clearances & RBAC
        </a>
        <a href="<?= url('admin/plans') ?>" class="btn btn-outline btn-sm">
            💎 Subscription Plans
        </a>
        <a href="<?= url('admin/settings') ?>" class="btn btn-outline btn-sm">
            ⚙️ Global Settings
        </a>
        <a href="<?= url('admin/audit-logs') ?>" class="btn btn-outline btn-sm">
            📜 Security Audit Trail
        </a>
    </div>
</div>

<!-- Admin Metrics Overview Grid -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Platform Users</span>
            <div class="stat-icon">👥</div>
        </div>
        <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
        <div style="font-size: 12px; color: var(--color-primary); margin-top: 8px;">Registered entities & brokerages</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Marketplace Inventory</span>
            <div class="stat-icon">🏢</div>
        </div>
        <div class="stat-value"><?= number_format($stats['total_listings']) ?></div>
        <div style="font-size: 12px; color: var(--color-success); margin-top: 8px;">Published real estate assets</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Verification Queue</span>
            <div class="stat-icon">🛡️</div>
        </div>
        <div class="stat-value" style="color: <?= $stats['pending_reviews'] > 0 ? '#F59E0B' : '#10B981' ?>;">
            <?= number_format($stats['pending_reviews']) ?>
        </div>
        <div style="font-size: 12px; color: var(--color-warning); margin-top: 8px;">Awaiting title document audit</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Client Leads</span>
            <div class="stat-icon">✉️</div>
        </div>
        <div class="stat-value"><?= number_format($stats['total_enquiries']) ?></div>
        <div style="font-size: 12px; color: var(--color-info); margin-top: 8px;">Buyer & investor enquiries</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Platform Revenue</span>
            <div class="stat-icon">💳</div>
        </div>
        <div class="stat-value" style="color: var(--color-primary); font-size: 24px;">
            <?= format_money($stats['total_revenue'] ?? 0) ?>
        </div>
        <div style="font-size: 12px; color: var(--color-success); margin-top: 8px;">Settled transactions & plans</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Active Subscriptions</span>
            <div class="stat-icon">💎</div>
        </div>
        <div class="stat-value"><?= number_format($stats['active_subscriptions']) ?></div>
        <div style="font-size: 12px; color: var(--color-accent); margin-top: 8px;">Paid broker accounts</div>
    </div>
</div>

<!-- Moderation Quick Queue -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 2px;">Pending Legal Verification Queue</h3>
            <div style="font-size: 12px; color: var(--color-text-muted);">Listings awaiting review under Section 12 Title Audit Policy</div>
        </div>
        <a href="<?= url('admin/verifications') ?>" class="btn btn-outline btn-sm">Full Verification Hub →</a>
    </div>

    <?php if (empty($pendingListings)): ?>
        <div style="padding: 36px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 32px; margin-bottom: 8px;">✓</div>
            <strong>Zero pending items in moderation queue.</strong> All property submissions have been audited.
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ref Code</th>
                    <th>Property Title</th>
                    <th>Listed By</th>
                    <th>Location</th>
                    <th>Price</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingListings as $listing): ?>
                    <tr>
                        <td><code><?= e($listing['reference_code']) ?></code></td>
                        <td>
                            <strong><?= e($listing['title']) ?></strong><br>
                            <span class="badge badge-gold" style="font-size: 10px;"><?= strtoupper($listing['verification_status'] ?? 'submitted') ?></span>
                        </td>
                        <td>
                            <?= e($listing['agent_name']) ?><br>
                            <small style="color: var(--color-text-muted);"><?= e($listing['agent_email']) ?></small>
                        </td>
                        <td><?= e($listing['city_name']) ?>, <?= e($listing['state_name']) ?></td>
                        <td><strong><?= format_money($listing['price'], $listing['currency'] ?? 'NGN') ?></strong></td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <form action="<?= url('admin/listings/' . $listing['id'] . '/status') ?>" method="POST" style="display: inline; margin: 0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-primary btn-sm" style="background: #10B981; border-color: #10B981; font-size: 11px; padding: 4px 8px;">
                                        ✓ Verify & Publish
                                    </button>
                                </form>
                                <a href="<?= url('admin/verifications') ?>" class="btn btn-outline btn-sm" style="font-size: 11px; padding: 4px 8px;">
                                    Inspect Deeds
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Recent Users List -->
<div class="table-card">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 2px;">Recently Registered Platform Entities</h3>
            <div style="font-size: 12px; color: var(--color-text-muted);">Latest consumer, agent, and institutional sign-ups</div>
        </div>
        <a href="<?= url('admin/users') ?>" class="btn btn-outline btn-sm">Manage Users & Clearances →</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Entity Name</th>
                <th>Email Address</th>
                <th>Assigned Role</th>
                <th>Status</th>
                <th>Registered Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentUsers as $ru): ?>
                <tr>
                    <td><strong><?= e($ru['full_name']) ?></strong></td>
                    <td><?= e($ru['email']) ?></td>
                    <td><span class="badge badge-primary"><?= strtoupper($ru['role_name'] ?? 'client') ?></span></td>
                    <td>
                        <span class="badge <?= $ru['status'] === 'active' ? 'badge-verified' : 'badge-gold' ?>">
                            <?= strtoupper($ru['status']) ?>
                        </span>
                    </td>
                    <td><?= date('d M Y', strtotime($ru['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
