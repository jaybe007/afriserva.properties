<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">My Booked Inspections</span>
            <div class="stat-icon">📅</div>
        </div>
        <div class="stat-value"><?= count($appointments) ?></div>
        <div style="font-size: 12px; color: var(--color-info); margin-top: 8px;">Scheduled property walkthroughs</div>
    </div>
</div>

<div class="table-card">
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">My Inspection Bookings</h3>
        <a href="<?= url('properties') ?>" class="btn btn-primary btn-sm">Browse More Properties</a>
    </div>

    <?php if (empty($appointments)): ?>
        <div style="padding: 48px 32px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">📅</div>
            <h4>You have no booked inspections yet</h4>
            <p style="margin-bottom: 20px;">Explore our signature luxury properties and request a private accompanied walkthrough.</p>
            <a href="<?= url('properties') ?>" class="btn btn-accent">Find a Property to Tour</a>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Property</th>
                    <th>Agent Contact</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $app): ?>
                    <tr>
                        <td>
                            <strong><?= date('D, M d, Y', strtotime($app['appointment_date'])) ?></strong><br>
                            <small><?= date('h:i A', strtotime($app['appointment_time'])) ?></small>
                        </td>
                        <td>
                            <a href="<?= url('properties/' . $app['property_slug']) ?>" target="_blank" style="color: var(--color-primary); font-weight: 600;">
                                <?= e($app['property_title']) ?>
                            </a><br>
                            <small style="color: var(--color-text-muted);"><?= e($app['address']) ?></small>
                        </td>
                        <td>
                            <strong><?= e($app['agent_name']) ?></strong><br>
                            <a href="tel:<?= e($app['agent_phone']) ?>" style="color: var(--color-accent); font-size: 12px;">
                                📞 <?= e($app['agent_phone']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-verified"><?= strtoupper($app['status']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
