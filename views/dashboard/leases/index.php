<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end;">
    <div>
        <h2 style="font-size: 24px;">Lease Management & Rent Ledgers</h2>
        <p style="color: var(--color-text-muted);">Manage residential and commercial tenancy agreements, monitor arrears, track renewals, and record payment remittances.</p>
    </div>
    <?php if ($isLandlord && !empty($properties)): ?>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('newLeaseModal').style.display='flex'">
            + Create Tenancy Record
        </button>
    <?php endif; ?>
</div>

<!-- Arrears & Expiry Surveillance Bar -->
<?php if ($isLandlord): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 18px;">
            <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase;">Outstanding Rent Arrears</div>
            <div style="font-size: 26px; font-weight: 700; color: <?= $arrearsSummary['total_arrears'] > 0 ? 'var(--color-danger)' : 'var(--color-success)' ?>; margin-top: 4px;">
                <?= format_money($arrearsSummary['total_arrears']) ?>
            </div>
            <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">
                <?= $arrearsSummary['overdue_count'] ?> active lease<?= $arrearsSummary['overdue_count'] === 1 ? '' : 's' ?> in overdue status
            </div>
        </div>

        <div style="background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 18px;">
            <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase;">Upcoming Renewals (60 Days)</div>
            <div style="font-size: 26px; font-weight: 700; color: #D97706; margin-top: 4px;">
                <?= count($expiringLeases) ?>
            </div>
            <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">
                Require renewal negotiations or vacancy preparation
            </div>
        </div>

        <div style="background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 18px;">
            <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase;">Total Active Leases</div>
            <div style="font-size: 26px; font-weight: 700; color: var(--color-primary); margin-top: 4px;">
                <?= count($leases) ?>
            </div>
            <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">
                Under active management across portfolio
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Expiring Leases Highlight Banner -->
<?php if (!empty($expiringLeases)): ?>
    <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 24px;">
        <strong style="color: #92400E;">⚠️ Tenancy Expiry Notice:</strong>
        <span style="color: #78350F; font-size: 13px;">
            The following leases are expiring soon:
            <?php foreach ($expiringLeases as $exp): ?>
                <strong><?= e($exp['property_title']) ?></strong> (<?= e($exp['tenant_name']) ?>, <?= $exp['days_remaining'] ?> days remaining); 
            <?php endforeach; ?>
        </span>
    </div>
<?php endif; ?>

<div class="table-card">
    <?php if (empty($leases)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">📜</div>
            <h4>No Active Tenancy Leases</h4>
            <p>Tenancy agreements and rent ledger schedules will be catalogued here.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Property & Address</th>
                    <th><?= $isLandlord ? 'Tenant Information' : 'Landlord / Manager' ?></th>
                    <th>Term Window</th>
                    <th>Annual / Period Rent</th>
                    <th>Financial Position</th>
                    <th style="text-align: right;">Ledger Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leases as $lease): ?>
                    <tr>
                        <td>
                            <a href="<?= url('properties/' . $lease['property_slug']) ?>" target="_blank" style="color: var(--color-primary); font-weight: 600;">
                                <?= e($lease['property_title']) ?>
                            </a><br>
                            <small style="color: var(--color-text-muted);"><?= e($lease['property_address'] ?? 'Undisclosed') ?></small>
                        </td>
                        <td>
                            <?php if ($isLandlord): ?>
                                <strong><?= e($lease['tenant_name']) ?></strong><br>
                                <small>📞 <?= e($lease['tenant_phone']) ?></small><br>
                                <small>✉️ <?= e($lease['tenant_email']) ?></small>
                            <?php else: ?>
                                <strong><?= e($lease['landlord_name']) ?></strong><br>
                                <small>📞 <?= e($lease['landlord_phone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= date('M d, Y', strtotime($lease['lease_start_date'])) ?></strong> to<br>
                            <strong><?= date('M d, Y', strtotime($lease['lease_end_date'])) ?></strong><br>
                            <?php if ($lease['days_until_expiry'] < 0): ?>
                                <span class="badge badge-gold" style="font-size: 10px;">EXPIRED</span>
                            <?php elseif ($lease['days_until_expiry'] <= 60): ?>
                                <span class="badge badge-gold" style="font-size: 10px;"><?= $lease['days_until_expiry'] ?> DAYS LEFT</span>
                            <?php else: ?>
                                <small style="color: var(--color-text-muted); font-size: 11px;"><?= $lease['days_until_expiry'] ?> days left</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= format_money($lease['rent_amount']) ?></strong><br>
                            <small style="color: var(--color-text-muted); text-transform: uppercase;"><?= e($lease['payment_frequency']) ?></small>
                        </td>
                        <td>
                            <?php if (!empty($lease['total_arrears']) && $lease['total_arrears'] > 0): ?>
                                <span style="color: var(--color-danger); font-weight: 700;">
                                    Arrears: <?= format_money($lease['total_arrears']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--color-success); font-weight: 600;">
                                    ✓ Settled to Date
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                <a href="<?= url('dashboard/leases/' . $lease['id'] . '/statement') ?>" target="_blank" class="btn btn-outline btn-sm" title="Statement of Account">
                                    📄 Statement
                                </a>
                                <?php if ($isLandlord): ?>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openPaymentModal(<?= $lease['id'] ?>, '<?= format_money($lease['rent_amount']) ?>')">
                                        + Log Payment
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Statutory Legal Disclaimer Box -->
<div style="margin-top: 24px; padding: 14px 18px; background: #F8FAFC; border: 1px solid var(--color-border); border-radius: var(--radius-md); font-size: 12px; color: var(--color-text-muted); line-height: 1.5;">
    <strong>Statutory Compliance & Operational Disclaimer:</strong> Tenancy schedules, payment receipts, and inspection records maintained within this module represent operational property management administrative ledgers. They do not constitute formal judicial certification of title or substituted statutory tenancy agreements without requisite jurisdiction-specific legal review.
</div>

<!-- Create Lease Modal -->
<?php if ($isLandlord && !empty($properties)): ?>
<div id="newLeaseModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-md); width: 100%; max-width: 600px; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 20px; font-weight: 700;">Create Tenancy Agreement Record</h3>
            <button type="button" onclick="document.getElementById('newLeaseModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer;">✕</button>
        </div>
        <form action="<?= url('dashboard/leases/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Rental Property *</label>
                <select name="property_id" class="form-control" required>
                    <?php foreach ($properties as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= e($p['title']) ?> (<?= format_money($p['price']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Tenant Account *</label>
                <select name="tenant_id" class="form-control" required>
                    <?php foreach ($tenants as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= e($t['full_name']) ?> (<?= e($t['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Lease Start Date *</label>
                    <input type="date" name="lease_start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Lease End Date *</label>
                    <input type="date" name="lease_end_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 year')) ?>">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div class="form-group">
                    <label class="form-label">Agreed Rent (₦) *</label>
                    <input type="number" step="0.01" name="rent_amount" class="form-control" required placeholder="5000000">
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Frequency *</label>
                    <select name="payment_frequency" class="form-control" required>
                        <option value="annually">Annually</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('newLeaseModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Tenancy Agreement</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Log Payment Modal -->
<div id="paymentModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-md); width: 100%; max-width: 500px; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 20px; font-weight: 700;">Record Rent Payment</h3>
            <button type="button" onclick="document.getElementById('paymentModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer;">✕</button>
        </div>
        <form id="paymentForm" method="POST">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Amount Paid (₦) *</label>
                <input type="number" step="0.01" name="amount" id="payAmount" class="form-control" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Payment Due Date *</label>
                    <input type="date" name="due_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Date Received *</label>
                    <input type="date" name="paid_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label">Remittance Method *</label>
                <select name="payment_method" class="form-control" required>
                    <option value="bank_transfer">Direct Bank Transfer</option>
                    <option value="paystack">Paystack Online Payment</option>
                    <option value="flutterwave">Flutterwave Gateway</option>
                    <option value="cheque">Manager's Cheque</option>
                </select>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('paymentModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Record Payment Entry</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal(leaseId, rentAmount) {
    document.getElementById('paymentForm').action = '<?= url("dashboard/leases/") ?>' + leaseId + '/record-payment';
    document.getElementById('payAmount').value = '';
    document.getElementById('paymentModal').style.display = 'flex';
}
</script>
