<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 24px; font-weight: 700; color: var(--color-primary); margin: 0 0 4px;">Marketplace Property Moderation Queue</h2>
        <p style="color: var(--color-text-muted); margin: 0;">Review submitted property listings, verify legal titles, promote featured developments, and manage marketplace inventory.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?= url('admin/verifications') ?>" class="btn btn-outline btn-sm">
            🛡️ Legal Title Due Diligence Hub →
        </a>
        <a href="<?= url('admin/export/listings') ?>" class="btn btn-primary btn-sm">
            📥 Export Inventory CSV
        </a>
    </div>
</div>

<!-- Quick Metric Pills -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card" style="padding: 16px 20px;">
        <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Total Inventory</div>
        <div style="font-size: 26px; font-weight: 800; color: var(--color-primary); margin-top: 4px;"><?= number_format($counts['total'] ?? count($properties)) ?></div>
    </div>
    <div class="stat-card" style="padding: 16px 20px;">
        <div style="font-size: 12px; font-weight: 600; color: #10B981; text-transform: uppercase;">Published & Live</div>
        <div style="font-size: 26px; font-weight: 800; color: #10B981; margin-top: 4px;"><?= number_format($counts['published'] ?? 0) ?></div>
    </div>
    <div class="stat-card" style="padding: 16px 20px;">
        <div style="font-size: 12px; font-weight: 600; color: #F59E0B; text-transform: uppercase;">Pending Moderation</div>
        <div style="font-size: 26px; font-weight: 800; color: #F59E0B; margin-top: 4px;"><?= number_format($counts['pending'] ?? 0) ?></div>
    </div>
    <div class="stat-card" style="padding: 16px 20px;">
        <div style="font-size: 12px; font-weight: 600; color: #EF4444; text-transform: uppercase;">Rejected Submissions</div>
        <div style="font-size: 26px; font-weight: 800; color: #EF4444; margin-top: 4px;"><?= number_format($counts['rejected'] ?? 0) ?></div>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="table-card" style="margin-bottom: 24px; padding: 20px;">
    <form action="<?= url('admin/listings') ?>" method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto auto; gap: 12px; align-items: end;">
        <div>
            <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Search Listings</label>
            <input type="text" name="q" class="form-control" placeholder="Search by title, reference code or agent..." value="<?= e($filters['q'] ?? '') ?>">
        </div>
        <div>
            <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Listing Status</label>
            <select name="status" class="form-control">
                <option value="">-- All Statuses --</option>
                <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published (Live)</option>
                <option value="pending_review" <?= ($filters['status'] ?? '') === 'pending_review' ? 'selected' : '' ?>>Pending Review</option>
                <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
            </select>
        </div>
        <div>
            <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Title Verification</label>
            <select name="verification" class="form-control">
                <option value="">-- All Verifications --</option>
                <option value="verified" <?= ($filters['verification'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified</option>
                <option value="under_review" <?= ($filters['verification'] ?? '') === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                <option value="submitted" <?= ($filters['verification'] ?? '') === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                <option value="rejected" <?= ($filters['verification'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 20px;">
                🔍 Filter
            </button>
        </div>
        <?php if (!empty($filters['q']) || !empty($filters['status']) || !empty($filters['verification'])): ?>
            <div>
                <a href="<?= url('admin/listings') ?>" class="btn btn-outline" style="height: 42px; display: flex; align-items: center;">
                    ✕ Reset
                </a>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- Moderation Table -->
<div class="table-card">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Marketplace Properties (<?= count($properties) ?> entries)</h3>
    </div>

    <?php if (empty($properties)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 36px; margin-bottom: 12px;">🏢</div>
            <h4>No property listings matched the query criteria</h4>
            <p><a href="<?= url('admin/listings') ?>">Clear search filters</a> to view all inventory records.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Ref Code</th>
                    <th>Property Title & Type</th>
                    <th>Listed By</th>
                    <th>Location</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Promotion</th>
                    <th style="text-align: right;">Moderation Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($properties as $p): ?>
                    <tr>
                        <td style="width: 70px;">
                            <?php if (!empty($p['cover_image'])): ?>
                                <img src="<?= e($p['cover_image']) ?>" alt="Property" style="width: 60px; height: 44px; object-fit: cover; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onerror="this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=100';">
                            <?php else: ?>
                                <div style="width: 60px; height: 44px; background: #E2E8F0; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 18px;">🏢</div>
                            <?php endif; ?>
                        </td>
                        <td><code><?= e($p['reference_code']) ?></code></td>
                        <td style="max-width: 240px;">
                            <a href="<?= url('properties/' . $p['slug']) ?>" target="_blank" style="color: var(--color-primary); font-weight: 600; text-decoration: none; display: block; line-height: 1.3;" title="View public listing">
                                <?= e($p['title']) ?> ↗
                            </a>
                            <div style="margin-top: 4px; display: flex; gap: 4px; flex-wrap: wrap;">
                                <span class="badge" style="font-size: 10px; background: #F1F5F9; color: #475569; text-transform: uppercase;">
                                    <?= e($p['transaction_type']) ?>
                                </span>
                                <?php if (!empty($p['verification_status'])): ?>
                                    <span class="badge <?= $p['verification_status'] === 'verified' ? 'badge-verified' : ($p['verification_status'] === 'rejected' ? 'badge-danger' : 'badge-gold') ?>" style="font-size: 10px;">
                                        <?= strtoupper($p['verification_status']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong><?= e($p['agent_name']) ?></strong>
                        </td>
                        <td><?= e($p['city_name']) ?>, <?= e($p['state_name']) ?></td>
                        <td>
                            <strong style="color: var(--color-primary); font-size: 14px;">
                                <?= format_money($p['price'], $p['currency'] ?? 'NGN') ?>
                            </strong>
                        </td>
                        <td>
                            <span class="badge <?= $p['status'] === 'published' ? 'badge-verified' : ($p['status'] === 'rejected' ? 'badge-danger' : 'badge-gold') ?>">
                                <?= strtoupper($p['status']) ?>
                            </span>
                        </td>
                        <td>
                            <form action="<?= url('admin/listings/' . $p['id'] . '/status') ?>" method="POST" style="display: inline; margin: 0;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle_featured">
                                <button type="submit" class="btn btn-sm <?= !empty($p['is_featured']) ? 'btn-accent' : 'btn-outline' ?>" style="font-size: 11px; padding: 4px 8px;">
                                    <?= !empty($p['is_featured']) ? '★ Featured' : '☆ Standard' ?>
                                </button>
                            </form>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 4px; align-items: center; justify-content: flex-end;">
                                <?php if ($p['status'] !== 'published'): ?>
                                    <form action="<?= url('admin/listings/' . $p['id'] . '/status') ?>" method="POST" style="display: inline; margin: 0;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-primary btn-sm" style="font-size: 11px; background: #10B981; border-color: #10B981; padding: 4px 8px;" title="Verify and publish live">
                                            ✓ Publish
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($p['status'] !== 'rejected'): ?>
                                    <form action="<?= url('admin/listings/' . $p['id'] . '/status') ?>" method="POST" style="display: inline; margin: 0;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-outline btn-sm" style="font-size: 11px; color: var(--color-danger); border-color: var(--color-danger); padding: 4px 8px;" onclick="return confirm('Reject this listing submission?');" title="Reject listing">
                                            ✕ Reject
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Delete / Archive -->
                                <form action="<?= url('admin/listings/' . $p['id'] . '/delete') ?>" method="POST" style="display: inline; margin: 0;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline btn-sm" style="font-size: 11px; color: #64748B; border-color: #CBD5E1; padding: 4px 8px;" onclick="return confirm('Remove listing #<?= $p['id'] ?> from marketplace inventory?');" title="Remove listing permanently">
                                        🗑
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
