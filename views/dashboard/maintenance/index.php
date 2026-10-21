<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end;">
    <div>
        <h2 style="font-size: 24px;">Property Maintenance & Repairs</h2>
        <p style="color: var(--color-text-muted);">Coordinate routine and emergency repairs, assign vetted contractors, and track maintenance expenditure.</p>
    </div>
    <?php if (!$isLandlord && !empty($leases)): ?>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('newTicketModal').style.display='flex'">
            + Report Maintenance Issue
        </button>
    <?php endif; ?>
</div>

<div class="table-card">
    <?php if (empty($requests)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">🔧</div>
            <h4>No Maintenance Requests</h4>
            <p>Maintenance repair tickets and contractor dispatch logs will appear here.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date Logged</th>
                    <th>Property Title</th>
                    <th>Issue Description</th>
                    <th>Priority</th>
                    <th>Assigned Contractor & Cost</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td>
                            <strong><?= date('M d, Y', strtotime($req['created_at'])) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= date('h:i A', strtotime($req['created_at'])) ?></small>
                        </td>
                        <td>
                            <strong><?= e($req['property_title']) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= e($req['property_address'] ?? 'Private') ?></small>
                        </td>
                        <td style="max-width: 250px;">
                            <strong><?= e($req['title']) ?></strong>
                            <div style="font-size: 12px; color: var(--color-secondary); margin-top: 2px;">
                                <?= e(substr($req['description'], 0, 90)) ?>...
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $req['priority'] === 'emergency' ? 'badge-danger' : ($req['priority'] === 'high' ? 'badge-gold' : 'badge-primary') ?>" style="<?= $req['priority'] === 'emergency' ? 'background: #EF4444; color: #FFF;' : '' ?>">
                                <?= strtoupper($req['priority']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($req['assigned_contractor_name'])): ?>
                                <strong><?= e($req['assigned_contractor_name']) ?></strong><br>
                                <small>📞 <?= e($req['assigned_contractor_phone']) ?></small><br>
                                <small style="color: var(--color-primary); font-weight: 600;">Cost: <?= format_money($req['cost']) ?></small>
                            <?php else: ?>
                                <span style="color: var(--color-text-muted); font-size: 12px;">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $req['status'] === 'resolved' ? 'badge-verified' : ($req['status'] === 'in_progress' ? 'badge-gold' : 'badge-outline') ?>">
                                <?= strtoupper(str_replace('_', ' ', $req['status'])) ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <?php if ($isLandlord && $req['status'] !== 'resolved'): ?>
                                <button type="button" class="btn btn-outline btn-sm" onclick="openAssignModal(<?= $req['id'] ?>, '<?= e(addslashes($req['assigned_contractor_name'] ?? '')) ?>', '<?= e(addslashes($req['assigned_contractor_phone'] ?? '')) ?>', '<?= (float)($req['cost'] ?? 0) ?>')">
                                    ⚙️ Assign / Cost
                                </button>
                                <form action="<?= url('dashboard/maintenance/' . $req['id'] . '/resolve') ?>" method="POST" style="display: inline-block; margin: 0 0 0 4px;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-primary btn-sm" style="background: var(--color-success); border-color: var(--color-success);">
                                        ✓ Resolve
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="font-size: 12px; color: var(--color-text-muted);">
                                    <?= $req['status'] === 'resolved' ? '✓ Completed' : 'Pending Review' ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Report Ticket Modal for Tenant -->
<?php if (!$isLandlord && !empty($leases)): ?>
<div id="newTicketModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-md); width: 100%; max-width: 550px; padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 20px; font-weight: 700;">Report Maintenance Issue</h3>
            <button type="button" onclick="document.getElementById('newTicketModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer;">✕</button>
        </div>
        <form action="<?= url('dashboard/maintenance/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Rental Residence *</label>
                <select name="property_id" class="form-control" required>
                    <?php foreach ($leases as $l): ?>
                        <option value="<?= $l['property_id'] ?>"><?= e($l['property_title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Issue Headline *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Master bedroom AC leaking water" required>
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Priority Level *</label>
                <select name="priority" class="form-control" required>
                    <option value="low">Low - Routine cosmetic or minor item</option>
                    <option value="medium" selected>Medium - Normal convenience issue</option>
                    <option value="high">High - Plumbing leak or electrical fault</option>
                    <option value="emergency">Emergency - Urgent health or security hazard</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label">Detailed Description *</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Describe the problem, when it started, and specific access instructions..." required></textarea>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('newTicketModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Ticket</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Assign Contractor Modal for Landlord -->
<div id="assignModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-md); width: 100%; max-width: 500px; padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 20px; font-weight: 700;">Assign Contractor & Log Cost</h3>
            <button type="button" onclick="document.getElementById('assignModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer;">✕</button>
        </div>
        <form id="assignForm" method="POST">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Contractor / Artisan Name *</label>
                <input type="text" name="assigned_contractor_name" id="contractorName" class="form-control" required placeholder="e.g. Apex HVAC Engineering Ltd">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Contractor Contact Phone *</label>
                <input type="text" name="assigned_contractor_phone" id="contractorPhone" class="form-control" required placeholder="+234 803 111 2222">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Estimated / Actual Cost (₦)</label>
                    <input type="number" step="0.01" name="cost" id="contractorCost" class="form-control" placeholder="45000">
                </div>
                <div class="form-group">
                    <label class="form-label">Scheduled Date</label>
                    <input type="date" name="scheduled_date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label">Progress Status</label>
                <select name="status" class="form-control">
                    <option value="in_progress">In Progress / Contractor Dispatched</option>
                    <option value="resolved">Work Completed & Verified</option>
                </select>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('assignModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Assignment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignModal(reqId, name, phone, cost) {
    document.getElementById('assignForm').action = '<?= url("dashboard/maintenance/") ?>' + reqId + '/assign';
    document.getElementById('contractorName').value = name;
    document.getElementById('contractorPhone').value = phone;
    document.getElementById('contractorCost').value = cost > 0 ? cost : '';
    document.getElementById('assignModal').style.display = 'flex';
}
</script>
