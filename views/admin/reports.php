<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px;">Listing Compliance & Fraud Reports Queue</h2>
    <p style="color: var(--color-text-muted);">Investigate consumer reports regarding inaccurate pricing, fraudulent representation, or disputed title documents.</p>
</div>

<div class="table-card">
    <?php if (empty($reports)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">🛡️</div>
            <h4>No Active Dispute Reports</h4>
            <p>All active marketplace listings comply with current compliance checks.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Reported Date</th>
                    <th>Reported Property</th>
                    <th>Reason / Nature</th>
                    <th>Description & Evidence</th>
                    <th>Reported By</th>
                    <th>Current Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $r): ?>
                    <tr>
                        <td>
                            <?= date('M d, Y', strtotime($r['created_at'])) ?><br>
                            <small style="color: var(--color-text-muted);"><?= date('h:i A', strtotime($r['created_at'])) ?></small>
                        </td>
                        <td>
                            <a href="<?= url('properties/' . $r['property_slug']) ?>" target="_blank" style="color: var(--color-primary); font-weight: 600;">
                                <?= e($r['property_title']) ?>
                            </a><br>
                            <small style="color: var(--color-text-muted);">Ref: <code><?= e($r['reference_code']) ?></code></small>
                        </td>
                        <td>
                            <span class="badge badge-primary"><?= e($r['reason']) ?></span>
                        </td>
                        <td style="max-width: 320px;">
                            <div style="font-size: 13px; color: var(--color-secondary); line-height: 1.5;">
                                "<?= e($r['details']) ?>"
                            </div>
                        </td>
                        <td>
                            <strong><?= e($r['reporter_name'] ?? 'Anonymous Buyer') ?></strong><br>
                            <small><?= e($r['reporter_email'] ?? '—') ?></small>
                        </td>
                        <td>
                            <span class="badge <?= $r['status'] === 'pending' ? 'badge-gold' : ($r['status'] === 'action_taken' ? 'badge-verified' : 'badge-primary') ?>">
                                <?= strtoupper($r['status']) ?>
                            </span>
                        </td>
                        <td>
                            <form action="<?= url('admin/reports/' . $r['id'] . '/status') ?>" method="POST" style="margin: 0;">
                                <?= csrf_field() ?>
                                <select name="status" class="form-control" style="font-size: 12px; height: 34px; padding: 4px;" onchange="this.form.submit()">
                                    <option value="pending" <?= $r['status'] === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                                    <option value="investigated" <?= $r['status'] === 'investigated' ? 'selected' : '' ?>>Investigated</option>
                                    <option value="action_taken" <?= $r['status'] === 'action_taken' ? 'selected' : '' ?>>Action Taken</option>
                                    <option value="dismissed" <?= $r['status'] === 'dismissed' ? 'selected' : '' ?>>Dismiss Report</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
