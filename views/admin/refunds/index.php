<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px;">Payment Refund Requests & Financial Reconciliation HQ</h2>
    <p style="color: var(--color-text-muted);">Audit customer refund claims, authorize reversals, and maintain payment gateway ledger parity.</p>
</div>

<div class="table-card">
    <?php if (empty($refunds)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">⚖️</div>
            <h4>No Refund Requests</h4>
            <p>All client payment transactions are fully settled and reconciled.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date Requested</th>
                    <th>Customer Account</th>
                    <th>Payment Reference & Gateway</th>
                    <th>Amount Claimed</th>
                    <th>Reason / Justification</th>
                    <th>Status</th>
                    <th style="text-align: right;">Review Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($refunds as $ref): ?>
                    <tr>
                        <td>
                            <strong><?= date('M d, Y', strtotime($ref['created_at'])) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= date('h:i A', strtotime($ref['created_at'])) ?></small>
                        </td>
                        <td>
                            <strong><?= e($ref['user_name']) ?></strong><br>
                            <small>✉️ <?= e($ref['user_email']) ?></small>
                        </td>
                        <td>
                            <span style="font-family: monospace; font-size: 12px; font-weight: 600;"><?= e($ref['payment_reference']) ?></span><br>
                            <small style="text-transform: uppercase; color: var(--color-text-muted);"><?= e($ref['gateway']) ?></small>
                        </td>
                        <td>
                            <strong style="font-size: 15px; color: var(--color-primary);"><?= format_money($ref['amount']) ?></strong>
                        </td>
                        <td style="max-width: 250px;">
                            <div style="font-size: 13px; color: var(--color-secondary); line-height: 1.4;">
                                "<?= e($ref['reason']) ?>"
                            </div>
                            <?php if (!empty($ref['admin_notes'])): ?>
                                <small style="color: #64748B; font-style: italic; display: block; margin-top: 4px;">
                                    Notes: <?= e($ref['admin_notes']) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $ref['status'] === 'approved' || $ref['status'] === 'processed' ? 'badge-verified' : ($ref['status'] === 'rejected' ? 'badge-danger' : 'badge-gold') ?>">
                                <?= strtoupper($ref['status']) ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <?php if ($ref['status'] === 'pending'): ?>
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <form action="<?= url('admin/refunds/' . $ref['id'] . '/process') ?>" method="POST" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="status" value="approved">
                                        <input type="hidden" name="admin_notes" value="Approved by compliance directorate.">
                                        <button type="submit" class="btn btn-primary btn-sm" style="background: var(--color-success); border-color: var(--color-success);">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <form action="<?= url('admin/refunds/' . $ref['id'] . '/process') ?>" method="POST" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="status" value="rejected">
                                        <input type="hidden" name="admin_notes" value="Rejected: Service provision already delivered.">
                                        <button type="submit" class="btn btn-outline btn-sm" style="color: var(--color-danger);">
                                            ✕ Reject
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span style="font-size: 12px; color: var(--color-text-muted);">
                                    Processed on <?= date('M d, Y', strtotime($ref['reviewed_at'] ?? $ref['created_at'])) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
