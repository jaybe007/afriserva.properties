<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Invoices & Billing History</h1>
        <p class="dashboard-subtitle">Inspect subscription billing receipts, premium promotions, and payment reconciliation records.</p>
    </div>
</div>

<div class="table-card">
    <?php if (empty($invoices)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">🧾</div>
            <h4>No Invoices Generated Yet</h4>
            <p>Your subscription invoices, featured placement receipts, and payment logs will appear here.</p>
            <a href="<?= url('dashboard/subscription') ?>" class="btn btn-primary" style="margin-top: 16px;">View Membership Plans</a>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Billing Date</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><code><?= e($inv['invoice_number'] ?? 'INV-' . $inv['id']) ?></code></td>
                        <td><?= date('d M Y', strtotime($inv['created_at'])) ?></td>
                        <td><?= e($inv['description'] ?? 'Professional Subscription Plan') ?></td>
                        <td><strong><?= format_money($inv['total_amount'] ?? $inv['amount'] ?? 0, 'NGN') ?></strong></td>
                        <td>
                            <span class="badge <?= ($inv['status'] ?? 'paid') === 'paid' ? 'badge-verified' : 'badge-gold' ?>">
                                <?= strtoupper($inv['status'] ?? 'paid') ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-outline btn-sm" onclick="window.print()">
                                🖨 Print Receipt
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
