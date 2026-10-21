<!-- Agent & Landlord KPI Metric Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Active Listings</span>
            <div class="stat-icon">🏢</div>
        </div>
        <div class="stat-value"><?= $stats['listings'] ?></div>
        <div style="font-size: 12px; color: var(--color-success); margin-top: 8px;">✓ Published on public portal</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Inbound Leads</span>
            <div class="stat-icon">✉️</div>
        </div>
        <div class="stat-value"><?= $stats['enquiries'] ?></div>
        <div style="font-size: 12px; color: var(--color-info); margin-top: 8px;">Prospective buyer enquiries</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Pending Tours</span>
            <div class="stat-icon">📅</div>
        </div>
        <div class="stat-value"><?= $stats['pending_viewings'] ?></div>
        <div style="font-size: 12px; color: var(--color-warning); margin-top: 8px;">Inspection requests awaiting confirmation</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Portfolio Views</span>
            <div class="stat-icon">👁</div>
        </div>
        <div class="stat-value"><?= number_format($stats['total_views']) ?></div>
        <div style="font-size: 12px; color: var(--color-accent); margin-top: 8px;">Investor impressions</div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div style="display: flex; gap: 16px; margin-bottom: 32px; flex-wrap: wrap;">
    <a href="<?= url('dashboard/listings/create') ?>" class="btn btn-accent">
        ➕ List New Property
    </a>
    <a href="<?= url('dashboard/enquiries') ?>" class="btn btn-primary">
        ✉️ Manage Client Leads
    </a>
    <a href="<?= url('dashboard/subscription') ?>" class="btn btn-outline">
        ⭐ Upgrade Membership Plan
    </a>
</div>

<!-- Recent Leads & Enquiries Table -->
<div class="table-card">
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">Recent Client Inquiries & Leads</h3>
        <a href="<?= url('dashboard/enquiries') ?>" class="btn btn-outline btn-sm">View All Leads →</a>
    </div>

    <?php if (empty($recentEnquiries)): ?>
        <div style="padding: 32px; text-align: center; color: var(--color-text-muted);">
            No inbound enquiries yet. New client inquiries will appear here immediately upon submission.
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Prospective Client</th>
                    <th>Property Listing</th>
                    <th>Phone / WhatsApp</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentEnquiries as $enq): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($enq['created_at'])) ?></td>
                        <td>
                            <strong><?= e($enq['name']) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= e($enq['email']) ?></small>
                        </td>
                        <td>
                            <a href="<?= url('properties/' . $enq['property_slug']) ?>" target="_blank" style="color: var(--color-primary); font-weight: 500;">
                                <?= e($enq['property_title']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $enq['phone']) ?>" target="_blank" style="color: var(--color-success); font-weight: 600;">
                                💬 <?= e($enq['phone']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-primary"><?= strtoupper($enq['status']) ?></span>
                        </td>
                        <td>
                            <a href="mailto:<?= e($enq['email']) ?>" class="btn btn-outline btn-sm">Reply</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Upcoming Inspection Appointments -->
<div class="table-card">
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">Scheduled Inspection Walkthroughs</h3>
        <a href="<?= url('dashboard/viewings') ?>" class="btn btn-outline btn-sm">View Calendar →</a>
    </div>

    <?php if (empty($upcomingAppointments)): ?>
        <div style="padding: 32px; text-align: center; color: var(--color-text-muted);">
            No property tours currently booked.
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Appointment Date</th>
                    <th>Client Name</th>
                    <th>Property Title</th>
                    <th>Inspection Format</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcomingAppointments as $app): ?>
                    <tr>
                        <td>
                            <strong><?= date('D, M d, Y', strtotime($app['appointment_date'])) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= date('h:i A', strtotime($app['appointment_time'])) ?></small>
                        </td>
                        <td>
                            <strong><?= e($app['client_name']) ?></strong><br>
                            <small><?= e($app['client_phone']) ?></small>
                        </td>
                        <td><?= e($app['property_title']) ?></td>
                        <td>
                            <span class="badge <?= $app['viewing_type'] === 'physical' ? 'badge-primary' : 'badge-gold' ?>">
                                <?= $app['viewing_type'] === 'physical' ? '🏢 In-Person' : '📹 Guided Video' ?>
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
