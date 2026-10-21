<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Failed Login Monitoring & Threat Intelligence</h1>
        <p class="dashboard-subtitle">Section 11 Compliance: Real-time surveillance of credential-stuffing attempts, rate limit strikes, and anomalous IP addresses.</p>
    </div>
</div>

<div class="table-card">
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">Recorded Authentication Failures</h3>
        <span class="badge badge-gold"><?= count($failedLogins) ?> Incident Events</span>
    </div>

    <?php if (empty($failedLogins)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">🛡️</div>
            <h4>Zero Threat Anomalies Detected</h4>
            <p>No brute-force or credential stuffing patterns detected in recent login telemetry.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Target Account Email</th>
                    <th>Origin IP Address</th>
                    <th>Security Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($failedLogins as $attempt): ?>
                    <tr>
                        <td><?= date('d M Y, H:i:s', strtotime($attempt['attempted_at'])) ?></td>
                        <td><strong><?= e($attempt['email']) ?></strong></td>
                        <td><code><?= e($attempt['ip_address']) ?></code></td>
                        <td>
                            <span class="badge badge-gold" style="font-size: 11px;">
                                Throttled / Logged
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
