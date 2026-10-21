<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Promotions, Coupons & Discount Campaigns</h1>
        <p class="dashboard-subtitle">Manage marketing vouchers, agent subscription discount codes, and featured listing campaigns.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; align-items: start;">
    <div class="table-card">
        <div class="table-header">
            <h3 style="font-size: 18px; font-weight: 600;">Active Campaign Coupons</h3>
            <span class="badge badge-primary"><?= count($coupons) ?> Coupons</span>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Discount Value</th>
                    <th>Usage</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $c): ?>
                    <tr>
                        <td><code><?= e($c['code']) ?></code></td>
                        <td><span class="badge badge-gold"><?= strtoupper($c['discount_type']) ?></span></td>
                        <td>
                            <strong>
                                <?= $c['discount_type'] === 'percentage' ? e($c['discount_value']) . '%' : format_money($c['discount_value'], 'NGN') ?>
                            </strong>
                        </td>
                        <td><?= $c['uses_count'] ?> / <?= $c['max_uses'] ?? '∞' ?></td>
                        <td><?= !empty($c['expires_at']) ? date('d M Y', strtotime($c['expires_at'])) : 'Permanent' ?></td>
                        <td>
                            <span class="badge <?= $c['is_active'] ? 'badge-verified' : 'badge-gold' ?>">
                                <?= $c['is_active'] ? 'ACTIVE' : 'DISABLED' ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Create Coupon Form -->
    <div class="dashboard-card">
        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 16px; color: var(--color-primary);">Create Campaign Code</h3>
        <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 20px;">
            Discount codes are validated server-side during checkout for subscription tiers and listing boosts.
        </p>

        <form action="<?= url('admin/coupons/store') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Promotional Code</label>
                <input type="text" name="code" class="form-control" required placeholder="e.g. ABUJA2026" style="text-transform: uppercase;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Discount Type</label>
                    <select name="discount_type" class="form-control">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Naira (₦)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Value</label>
                    <input type="number" step="0.01" name="discount_value" class="form-control" required placeholder="20.00">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Max Redemptions</label>
                <input type="number" name="max_uses" class="form-control" placeholder="500">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px;">
                ➕ Launch Promotional Code
            </button>
        </form>
    </div>
</div>
