<?php
$isTenant = $isTenant ?? has_role('tenant');
$appointments = $appointments ?? [];
$leases = $leases ?? [];
$applications = $applications ?? [];
$maintenanceRequests = $maintenanceRequests ?? [];
$savedCount = $savedCount ?? 0;
$enquiriesCount = $enquiriesCount ?? 0;
?>

<!-- Portal Welcome Banner -->
<div style="background: linear-gradient(135deg, var(--color-primary, #0A192F) 0%, #172A45 100%); border-radius: var(--radius-lg, 12px); padding: 28px 32px; color: #FFFFFF; margin-bottom: 28px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1); padding: 4px 12px; border-radius: 20px; font-size: 12px; margin-bottom: 12px; font-weight: 600; color: var(--color-accent, #C5A059);">
            <span>🛡</span> <?= $isTenant ? 'Tenant Resident Hub' : 'Prime Property Buyer & Investor' ?>
        </div>
        <h2 style="font-size: 24px; font-weight: 700; color: #FFFFFF; margin: 0 0 6px 0;">Welcome, <?= e($user['full_name'] ?? 'Client') ?></h2>
        <p style="color: rgba(255, 255, 255, 0.7); margin: 0; font-size: 14px;">
            <?= $isTenant 
                ? 'Manage active leases, monitor rent statements, track application status, and dispatch maintenance tickets.' 
                : 'Acquire verified Nigerian real estate, book accompanied private walkthroughs, and monitor your saved acquisitions.' ?>
        </p>
    </div>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="<?= url('properties') ?>" class="btn btn-accent" style="box-shadow: 0 4px 12px rgba(197, 160, 89, 0.3);">
            <span>🔍</span> Browse Properties
        </a>
        <?php if ($isTenant): ?>
            <a href="<?= url('dashboard/maintenance') ?>" class="btn btn-outline" style="border-color: rgba(255, 255, 255, 0.3); color: #FFFFFF;">
                <span>🔧</span> Report Maintenance
            </a>
        <?php else: ?>
            <a href="<?= url('dashboard/saved') ?>" class="btn btn-outline" style="border-color: rgba(255, 255, 255, 0.3); color: #FFFFFF;">
                <span>❤️</span> Saved Shortlist (<?= $savedCount ?>)
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Primary Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 28px;">
    <?php if ($isTenant): ?>
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Active Tenancies</span>
                <div class="stat-icon">📜</div>
            </div>
            <div class="stat-value"><?= count($leases) ?></div>
            <div style="font-size: 12px; color: var(--color-accent); margin-top: 8px;">
                <a href="<?= url('dashboard/leases') ?>" style="color: inherit; text-decoration: underline;">View Rent Ledgers →</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Rental Applications</span>
                <div class="stat-icon">📑</div>
            </div>
            <div class="stat-value"><?= count($applications) ?></div>
            <div style="font-size: 12px; color: var(--color-info); margin-top: 8px;">
                <a href="<?= url('dashboard/rental-applications') ?>" style="color: inherit; text-decoration: underline;">Review Status →</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Maintenance Tickets</span>
                <div class="stat-icon">🔧</div>
            </div>
            <div class="stat-value"><?= count($maintenanceRequests) ?></div>
            <div style="font-size: 12px; color: var(--color-warning); margin-top: 8px;">
                <a href="<?= url('dashboard/maintenance') ?>" style="color: inherit; text-decoration: underline;">Track Repairs →</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Booked Tours</span>
                <div class="stat-icon">📅</div>
            </div>
            <div class="stat-value"><?= count($appointments) ?></div>
            <div style="font-size: 12px; color: var(--color-success); margin-top: 8px;">Scheduled inspections</div>
        </div>
    <?php else: ?>
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Saved Shortlist</span>
                <div class="stat-icon">❤️</div>
            </div>
            <div class="stat-value"><?= (int)$savedCount ?></div>
            <div style="font-size: 12px; color: var(--color-accent); margin-top: 8px;">
                <a href="<?= url('dashboard/saved') ?>" style="color: inherit; text-decoration: underline;">Explore Shortlist →</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Booked Inspections</span>
                <div class="stat-icon">📅</div>
            </div>
            <div class="stat-value"><?= count($appointments) ?></div>
            <div style="font-size: 12px; color: var(--color-info); margin-top: 8px;">Accompanied walkthroughs</div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Enquiries Lodged</span>
                <div class="stat-icon">💬</div>
            </div>
            <div class="stat-value"><?= (int)$enquiriesCount ?></div>
            <div style="font-size: 12px; color: var(--color-success); margin-top: 8px;">
                <a href="<?= url('dashboard/messages') ?>" style="color: inherit; text-decoration: underline;">Open Messages →</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Verified Inventory</span>
                <div class="stat-icon">🛡</div>
            </div>
            <div class="stat-value">100%</div>
            <div style="font-size: 12px; color: var(--color-primary); margin-top: 8px;">C of O / Governor's Consent</div>
        </div>
    <?php endif; ?>
</div>

<?php if ($isTenant && !empty($leases)): ?>
    <!-- Active Leases Widget for Tenant -->
    <div class="table-card" style="margin-bottom: 28px;">
        <div class="table-header">
            <div>
                <h3 style="font-size: 18px; font-weight: 600; margin: 0 0 4px 0;">My Active Leases & Rent Schedules</h3>
                <p style="font-size: 13px; color: var(--color-text-muted); margin: 0;">Verified lease records registered with your landlord.</p>
            </div>
            <a href="<?= url('dashboard/leases') ?>" class="btn btn-outline btn-sm">Full Lease Ledger</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Term Window</th>
                    <th>Agreed Rent</th>
                    <th>Next Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($leases, 0, 3) as $l): ?>
                    <tr>
                        <td>
                            <strong><?= e($l['property_title'] ?? 'Residential Asset') ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= e($l['property_address'] ?? 'Lagos, Nigeria') ?></small>
                        </td>
                        <td>
                            <?= date('M d, Y', strtotime($l['lease_start_date'])) ?> &rarr; <?= date('M d, Y', strtotime($l['lease_end_date'])) ?>
                        </td>
                        <td>
                            <strong><?= format_money($l['rent_amount']) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= ucfirst($l['payment_frequency'] ?? 'annual') ?></small>
                        </td>
                        <td>
                            <?= !empty($l['next_rent_due_date']) ? date('M d, Y', strtotime($l['next_rent_due_date'])) : '—' ?>
                        </td>
                        <td>
                            <span class="badge badge-verified"><?= strtoupper($l['status'] ?? 'ACTIVE') ?></span>
                        </td>
                        <td>
                            <a href="<?= url('dashboard/leases/' . $l['id'] . '/statement') ?>" target="_blank" class="btn btn-outline btn-sm" style="font-size: 11px;">
                                Statement ↗
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Inspection Bookings Table -->
<div class="table-card">
    <div class="table-header">
        <div>
            <h3 style="font-size: 18px; font-weight: 600; margin: 0 0 4px 0;">My Inspection Bookings</h3>
            <p style="font-size: 13px; color: var(--color-text-muted); margin: 0;">Confirmed and pending accompanied physical or virtual inspections.</p>
        </div>
        <a href="<?= url('properties') ?>" class="btn btn-primary btn-sm">Browse More Properties</a>
    </div>

    <?php if (empty($appointments)): ?>
        <div style="padding: 48px 32px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">📅</div>
            <h4>You have no booked inspections yet</h4>
            <p style="margin-bottom: 20px; max-width: 500px; margin-left: auto; margin-right: auto;">Explore our signature luxury properties and request a private accompanied walkthrough at your preferred date and time.</p>
            <a href="<?= url('properties') ?>" class="btn btn-accent">Find a Property to Tour</a>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Property</th>
                    <th>Agent Contact</th>
                    <th>Tour Type</th>
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
                            <span style="font-size: 12px; text-transform: capitalize; color: var(--color-text-muted);">
                                <?= e($app['tour_type'] ?? 'Physical Accompanied') ?>
                            </span>
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

<!-- Buyer / Investor Security Guidance Note -->
<div style="margin-top: 28px; background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 20px 24px; display: flex; align-items: flex-start; gap: 16px;">
    <div style="font-size: 24px; color: var(--color-accent); line-height: 1;">🛡</div>
    <div>
        <h4 style="font-size: 14px; font-weight: 700; margin: 0 0 4px 0; color: var(--color-primary);">Afriserva Verified Real Estate Guarantee</h4>
        <p style="font-size: 13px; color: var(--color-text-muted); margin: 0; line-height: 1.5;">
            Every listing verified on Afriserva undergoes rigorous statutory due diligence against official Land Registry archives, confirming valid title (Certificate of Occupancy, Governor’s Consent, Gazette or Registered Survey) prior to escrow and contract execution.
        </p>
    </div>
</div>
