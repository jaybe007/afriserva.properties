<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px;">Tenancy Management & Asset Administration</h2>
    <p style="color: var(--color-text-muted);">Manage statutory residential and commercial leases, rental ledgers, and property maintenance requests.</p>
</div>

<!-- Active Leases Table -->
<div class="table-card">
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">Active Tenancy Leases & Contracts</h3>
        <span class="badge badge-verified">Lagos Tenancy Law (2011) Compliant</span>
    </div>

    <?php if (empty($leases)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">📜</div>
            <h4>No Active Leases on Record</h4>
            <p>Executed residential and commercial lease contracts will be indexed here.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Tenant Details</th>
                    <th>Landlord</th>
                    <th>Lease Tenure</th>
                    <th>Annual Rent</th>
                    <th>Caution Deposit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leases as $l): ?>
                    <tr>
                        <td>
                            <strong><?= e($l['property_title']) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= e($l['address']) ?></small>
                        </td>
                        <td>
                            <strong><?= e($l['tenant_name']) ?></strong><br>
                            <small><?= e($l['tenant_phone']) ?></small>
                        </td>
                        <td>
                            <strong><?= e($l['landlord_name']) ?></strong><br>
                            <small><?= e($l['landlord_phone']) ?></small>
                        </td>
                        <td>
                            <?= date('M d, Y', strtotime($l['lease_start_date'])) ?> to<br>
                            <strong><?= date('M d, Y', strtotime($l['lease_end_date'])) ?></strong>
                        </td>
                        <td><strong><?= format_money($l['rent_amount']) ?></strong></td>
                        <td><?= format_money($l['deposit_amount']) ?></td>
                        <td><span class="badge badge-verified"><?= strtoupper($l['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Rent Payment Ledger -->
<div class="table-card">
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">Rent Payment Ledger & Receipts</h3>
    </div>

    <?php if (empty($rentPayments)): ?>
        <div style="padding: 32px; text-align: center; color: var(--color-text-muted);">
            No payment history recorded yet.
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Receipt Ref</th>
                    <th>Property</th>
                    <th>Amount Paid</th>
                    <th>Due Date</th>
                    <th>Payment Date</th>
                    <th>Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rentPayments as $rp): ?>
                    <tr>
                        <td><code><?= e($rp['receipt_reference'] ?? 'REC-PENDING') ?></code></td>
                        <td><?= e($rp['property_title']) ?></td>
                        <td><strong><?= format_money($rp['amount']) ?></strong></td>
                        <td><?= date('M d, Y', strtotime($rp['due_date'])) ?></td>
                        <td><?= $rp['paid_date'] ? date('M d, Y', strtotime($rp['paid_date'])) : '—' ?></td>
                        <td><?= strtoupper(str_replace('_', ' ', $rp['payment_method'])) ?></td>
                        <td><span class="badge badge-verified"><?= strtoupper($rp['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Maintenance Requests -->
<div class="table-card">
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">Maintenance & Artisan Work Orders</h3>
    </div>

    <?php if (empty($maintenanceRequests)): ?>
        <div style="padding: 32px; text-align: center; color: var(--color-text-muted);">
            Zero open maintenance tickets. All property assets functioning optimally.
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date Logged</th>
                    <th>Property</th>
                    <th>Issue Title</th>
                    <th>Priority</th>
                    <th>Estimated Cost</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($maintenanceRequests as $mr): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($mr['created_at'])) ?></td>
                        <td><?= e($mr['property_title']) ?></td>
                        <td>
                            <strong><?= e($mr['title']) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= e($mr['description']) ?></small>
                        </td>
                        <td>
                            <span class="badge <?= $mr['priority'] === 'emergency' ? 'badge-primary' : 'badge-gold' ?>">
                                <?= strtoupper($mr['priority']) ?>
                            </span>
                        </td>
                        <td><strong><?= format_money($mr['cost']) ?></strong></td>
                        <td><span class="badge badge-verified"><?= strtoupper($mr['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
