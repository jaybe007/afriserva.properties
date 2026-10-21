<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px;">Administrative Security & Operational Audit Trail</h2>
    <p style="color: var(--color-text-muted);">Immutable audit log of all sensitive operations, clearance changes, listing moderations, and platform security events.</p>
</div>

<div class="table-card">
    <?php if (empty($logs)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">📜</div>
            <h4>Audit Trail Initialized</h4>
            <p>Administrative and security events will be automatically recorded here.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Operator</th>
                    <th>Action</th>
                    <th>Target Entity</th>
                    <th>IP Address</th>
                    <th>Values / Payload</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>
                            <?= date('M d, Y', strtotime($log['created_at'])) ?><br>
                            <small style="color: var(--color-text-muted);"><?= date('h:i:s A', strtotime($log['created_at'])) ?></small>
                        </td>
                        <td>
                            <strong><?= e($log['operator_name'] ?? 'System Process') ?></strong><br>
                            <small><?= e($log['operator_email'] ?? '') ?></small>
                        </td>
                        <td>
                            <span class="badge badge-primary"><?= e($log['action']) ?></span>
                        </td>
                        <td>
                            <code><?= e($log['target_type'] ?? '—') ?> #<?= $log['target_id'] ?? 0 ?></code>
                        </td>
                        <td>
                            <small><?= e($log['ip_address']) ?></small>
                        </td>
                        <td style="max-width: 320px; font-size: 11px; font-family: monospace; word-break: break-all;">
                            <?= e($log['new_values'] ?? '—') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
