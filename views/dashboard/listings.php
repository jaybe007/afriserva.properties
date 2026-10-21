<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 24px;">My Property Listings & Portfolios</h2>
        <p style="color: var(--color-text-muted);">Manage your active, draft, under review, and completed transactions.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?= url('dashboard/export/listings') ?>" class="btn btn-outline btn-sm">
            📊 Export CSV
        </a>
        <a href="<?= url('dashboard/listings/create') ?>" class="btn btn-accent btn-sm">
            ➕ List New Property
        </a>
    </div>
</div>

<!-- Onboarding Checklist Widget -->
<?php if (count($properties) <= 1): ?>
<div style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); color: #FFFFFF; border-radius: var(--radius-lg); padding: 24px; margin-bottom: 24px; box-shadow: var(--shadow-luxury);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h4 style="font-size: 16px; font-weight: 700; color: var(--color-accent-gold); margin-bottom: 4px;">🚀 Property Owner & Agent Fast-Track Onboarding</h4>
            <p style="font-size: 13px; color: #94A3B8; margin: 0;">Complete these three statutory milestones to earn the Verified Seller Badge.</p>
        </div>
        <span class="badge badge-gold">2 / 3 Steps Completed</span>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div style="background: rgba(255,255,255,0.08); padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; border-left: 3px solid #10B981;">
            ✓ <strong>Profile & Phone Verified</strong>
        </div>
        <div style="background: rgba(255,255,255,0.08); padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; border-left: 3px solid #10B981;">
            ✓ <strong>Create First Listing</strong>
        </div>
        <div style="background: rgba(255,255,255,0.08); padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; border-left: 3px solid var(--color-accent-gold);">
            ⏳ <strong>Submit Title Document</strong>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="table-card">
    <?php if (empty($properties)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">🏢</div>
            <h4>You haven't listed any properties yet</h4>
            <p style="margin-bottom: 24px;">Start by uploading your verified property details and high-resolution photographs.</p>
            <a href="<?= url('dashboard/listings/create') ?>" class="btn btn-accent">Create Your First Listing</a>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Reference</th>
                    <th>Property Title</th>
                    <th>Price</th>
                    <th>Lifecycle</th>
                    <th>Verification</th>
                    <th>Views</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($properties as $prop): ?>
                    <tr>
                        <td style="width: 80px;">
                            <img src="<?= e($prop['cover_image']) ?>" alt="Property" style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px;">
                        </td>
                        <td><code><?= e($prop['reference_code']) ?></code></td>
                        <td>
                            <strong><?= e($prop['title']) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= e($prop['city_name']) ?>, <?= e($prop['state_name']) ?></small>
                        </td>
                        <td><strong><?= format_money($prop['price'], $prop['currency']) ?></strong></td>
                        <td>
                            <span class="badge <?= in_array($prop['status'], ['published', 'sold', 'rented']) ? 'badge-verified' : 'badge-gold' ?>">
                                <?= strtoupper($prop['status']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= ($prop['verification_status'] ?? 'unverified') === 'verified' ? 'badge-verified' : 'badge-primary' ?>">
                                <?= ucwords(str_replace('_', ' ', $prop['verification_status'] ?? 'unverified')) ?>
                            </span>
                        </td>
                        <td>👁 <?= $prop['view_count'] ?></td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                <a href="<?= url('dashboard/listings/edit/' . $prop['id']) ?>" class="btn btn-outline btn-sm">
                                    ✏️ Edit
                                </a>

                                <!-- Quick Status Switch Dropdown / Actions -->
                                <form action="<?= url('dashboard/listings/' . $prop['id'] . '/quick-status') ?>" method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <?php if ($prop['status'] === 'published'): ?>
                                        <input type="hidden" name="status" value="sold">
                                        <button type="submit" class="btn btn-outline btn-sm" title="Mark as Sold">
                                            🏷 Sold
                                        </button>
                                    <?php elseif ($prop['status'] === 'sold'): ?>
                                        <input type="hidden" name="status" value="published">
                                        <button type="submit" class="btn btn-outline btn-sm" title="Reactivate Listing">
                                            🔄 Reactivate
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="status" value="published">
                                        <button type="submit" class="btn btn-outline btn-sm">
                                            Publish
                                        </button>
                                    <?php endif; ?>
                                </form>

                                <form action="<?= url('dashboard/listings/' . $prop['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Archive listing from portfolio?');" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--color-danger); border-color: var(--color-danger);">
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
