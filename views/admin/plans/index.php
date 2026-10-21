<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px;">Monetization & Subscription Plans Management</h2>
    <p style="color: var(--color-text-muted);">Configure multi-tier pricing, listing allowances, featured slots, team limits, grace periods, lead pricing, and renewal rules.</p>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Plan Name & Tier</th>
                <th>Price & Currency</th>
                <th>Interval</th>
                <th>Listing Allowance</th>
                <th>Featured Slots</th>
                <th>Grace Period</th>
                <th>Analytics Level</th>
                <th>Lead Price (₦)</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plans as $plan): ?>
                <tr>
                    <td>
                        <strong><?= e($plan['name']) ?></strong><br>
                        <small style="color: var(--color-text-muted); font-family: monospace;"><?= e($plan['slug']) ?></small>
                    </td>
                    <td>
                        <strong style="color: var(--color-primary); font-size: 15px;">
                            <?= format_money($plan['price']) ?>
                        </strong>
                    </td>
                    <td>
                        <span class="badge badge-outline" style="text-transform: capitalize;">
                            <?= e($plan['billing_interval']) ?>
                        </span>
                    </td>
                    <td><?= $plan['listing_allowance'] ?> active</td>
                    <td><?= $plan['featured_allowance'] ?> slots</td>
                    <td><?= $plan['grace_period_days'] ?? 7 ?> days</td>
                    <td>
                        <span class="badge badge-primary" style="text-transform: capitalize;">
                            <?= e($plan['analytics_level'] ?? 'basic') ?>
                        </span>
                    </td>
                    <td><?= format_money($plan['lead_price'] ?? 0) ?></td>
                    <td>
                        <span class="badge <?= !empty($plan['is_active']) ? 'badge-verified' : 'badge-gold' ?>">
                            <?= !empty($plan['is_active']) ? 'ACTIVE' : 'INACTIVE' ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-outline btn-sm" onclick="editPlan(<?= htmlspecialchars(json_encode($plan), ENT_QUOTES, 'UTF-8') ?>)">
                            ✏️ Edit Terms
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Edit Plan Modal -->
<div id="planModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-md); width: 100%; max-width: 600px; padding: 28px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 20px; font-weight: 700;">Edit Subscription Plan Terms</h3>
            <button type="button" onclick="document.getElementById('planModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer;">✕</button>
        </div>
        <form id="planForm" method="POST">
            <?= csrf_field() ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Plan Display Name *</label>
                    <input type="text" name="name" id="pName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Price (₦) *</label>
                    <input type="number" step="0.01" name="price" id="pPrice" class="form-control" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Billing Interval *</label>
                    <select name="billing_interval" id="pInterval" class="form-control">
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Listing Limit *</label>
                    <input type="number" name="listing_allowance" id="pListLimit" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Featured Limit *</label>
                    <input type="number" name="featured_allowance" id="pFeatLimit" class="form-control" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Team Members Limit</label>
                    <input type="number" name="team_members_limit" id="pTeamLimit" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Grace Period (Days)</label>
                    <input type="number" name="grace_period_days" id="pGraceDays" class="form-control" value="7">
                </div>
                <div class="form-group">
                    <label class="form-label">Listing Duration (Days)</label>
                    <input type="number" name="listing_duration_days" id="pDurDays" class="form-control" value="30">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label">Analytics Level</label>
                    <select name="analytics_level" id="pAnalytics" class="form-control">
                        <option value="basic">Basic Analytics</option>
                        <option value="standard">Standard Analytics</option>
                        <option value="advanced">Advanced Agency Analytics</option>
                        <option value="realtime">Enterprise Real-Time Stream</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Lead Pricing (₦ per unlocked lead)</label>
                    <input type="number" step="0.01" name="lead_price" id="pLeadPrice" class="form-control" value="0.00">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                    <input type="checkbox" name="is_active" id="pActive" value="1">
                    Plan is Active for Customer Subscription
                </label>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('planModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Plan Configuration</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPlan(plan) {
    document.getElementById('planForm').action = '<?= url("admin/plans/") ?>' + plan.id + '/update';
    document.getElementById('pName').value = plan.name;
    document.getElementById('pPrice').value = plan.price;
    document.getElementById('pInterval').value = plan.billing_interval;
    document.getElementById('pListLimit').value = plan.listing_allowance;
    document.getElementById('pFeatLimit').value = plan.featured_allowance;
    document.getElementById('pTeamLimit').value = plan.team_members_limit || 1;
    document.getElementById('pGraceDays').value = plan.grace_period_days || 7;
    document.getElementById('pDurDays').value = plan.listing_duration_days || 30;
    document.getElementById('pAnalytics').value = plan.analytics_level || 'basic';
    document.getElementById('pLeadPrice').value = plan.lead_price || 0;
    document.getElementById('pActive').checked = (plan.is_active == 1);
    document.getElementById('planModal').style.display = 'flex';
}
</script>
