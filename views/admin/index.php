<!-- System Health & Platform Integrity Strip -->
<div style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color: #FFFFFF; border-radius: var(--radius-lg); padding: 20px 24px; margin-bottom: 24px; box-shadow: var(--shadow-luxury);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h4 style="font-size: 15px; font-weight: 700; color: var(--color-accent-gold); margin-bottom: 4px;">
                ⚡ Infrastructure System Health & Server Monitoring
            </h4>
            <div style="font-size: 12px; color: #94A3B8;">
                Real-time operational indicators for compliance, database connections, and memory overhead.
            </div>
        </div>
        <div style="display: flex; gap: 20px; align-items: center; font-size: 13px;">
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
                <span class="badge badge-verified" style="background: rgba(16, 185, 129, 0.2); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.4);">
                    ● All Systems Operational
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Admin Metrics Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Platform Users</span>
            <div class="stat-icon">👥</div>
        </div>
        <div class="stat-value"><?= $stats['total_users'] ?></div>
        <div style="font-size: 12px; color: var(--color-primary); margin-top: 8px;">Registered entities & brokers</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Published Listings</span>
            <div class="stat-icon">🏢</div>
        </div>
        <div class="stat-value"><?= $stats['total_listings'] ?></div>
        <div style="font-size: 12px; color: var(--color-success); margin-top: 8px;">Active marketplace inventory</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Verification & Moderation</span>
            <div class="stat-icon">🛡️</div>
        </div>
        <div class="stat-value"><?= $stats['pending_reviews'] ?></div>
        <div style="font-size: 12px; color: var(--color-warning); margin-top: 8px;">Awaiting title & fraud review</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Client Leads</span>
            <div class="stat-icon">✉️</div>
        </div>
        <div class="stat-value"><?= $stats['total_enquiries'] ?></div>
        <div style="font-size: 12px; color: var(--color-info); margin-top: 8px;">Buyer & tenant enquiries</div>
    </div>
</div>

<!-- Moderation Quick Queue -->
<div class="table-card">
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">Pending Verification Queue</h3>
        <a href="<?= url('admin/verifications') ?>" class="btn btn-outline btn-sm">Full Verification Hub →</a>
    </div>

    <?php if (empty($pendingListings)): ?>
        <div style="padding: 32px; text-align: center; color: var(--color-text-muted);">
            ✓ All property submissions have been moderated. Zero items in queue.
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingListings as $listing): ?>
                    <tr>
                        <td><code><?= e($listing['reference_code']) ?></code></td>
                        <td>
                            <strong><?= e($listing['title']) ?></strong><br>
                            <small class="badge badge-gold"><?= strtoupper($listing['verification_status'] ?? 'submitted') ?></small>
                        </td>
                        <td>
                            <?= e($listing['agent_name']) ?><br>
                            <small style="color: var(--color-text-muted);"><?= e($listing['agent_email']) ?></small>
                        </td>
                        <td><?= e($listing['city_name']) ?>, <?= e($listing['state_name']) ?></td>
                        <td><strong><?= format_money($listing['price'], $listing['currency']) ?></strong></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <form action="<?= url('admin/listings/' . $listing['id'] . '/status') ?>" method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-primary btn-sm" style="background: var(--color-success); border-color: var(--color-success);">
                                        Verify & Publish
                                    </button>
                                </form>
                                <a href="<?= url('admin/verifications') ?>" class="btn btn-outline btn-sm">
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
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">Recently Registered Platform Entities</h3>
        <a href="<?= url('admin/users') ?>" class="btn btn-outline btn-sm">Manage Users →</a>
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
