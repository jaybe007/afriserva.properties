<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px;">Client Enquiries & Prospective Leads CRM</h2>
    <p style="color: var(--color-text-muted);">Manage inbound buyer and tenant inquiries, track qualification stages, monitor response times, and convert leads into closed transactions.</p>
</div>

<!-- Lead Pipeline & Response Analytics Metrics Bar -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 16px; text-align: center;">
        <div style="font-size: 26px; font-weight: 700; color: var(--color-primary);"><?= $counts['total'] ?? 0 ?></div>
        <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase;">Total Leads</div>
    </div>
    <div style="background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 16px; text-align: center;">
        <div style="font-size: 26px; font-weight: 700; color: var(--color-info);"><?= $counts['new'] ?? 0 ?></div>
        <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase;">New Inquiries</div>
    </div>
    <div style="background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 16px; text-align: center;">
        <div style="font-size: 26px; font-weight: 700; color: #D97706;"><?= $counts['qualified'] ?? 0 ?></div>
        <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase;">Qualified Buyers</div>
    </div>
    <div style="background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 16px; text-align: center;">
        <div style="font-size: 26px; font-weight: 700; color: var(--color-success);"><?= $counts['converted'] ?? 0 ?></div>
        <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase;">Closed / Converted</div>
    </div>
    <div style="background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 16px; text-align: center;">
        <div style="font-size: 26px; font-weight: 700; color: #0284C7;"><?= $analytics['avg_formatted'] ?? 'Under 1 hour' ?></div>
        <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase;">Avg Response Speed</div>
    </div>
</div>

<div class="table-card">
    <?php if (empty($enquiries)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">✉️</div>
            <h4>No Inbound Enquiries Yet</h4>
            <p>When prospective buyers or tenants submit inquiries on your listings, they will appear here in real-time.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date Received</th>
                    <th>Prospective Client</th>
                    <th>Associated Property</th>
                    <th>Stage & Lead Status</th>
                    <th>Message Details</th>
                    <th>Privacy & Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enquiries as $enq): ?>
                    <tr>
                        <td>
                            <?= date('M d, Y', strtotime($enq['created_at'])) ?><br>
                            <small style="color: var(--color-text-muted);"><?= date('h:i A', strtotime($enq['created_at'])) ?></small>
                        </td>
                        <td>
                            <strong><?= e($enq['name']) ?></strong><br>
                            <?php if (empty($enq['contact_unlocked'])): ?>
                                <small style="color: #64748B; font-family: monospace; background: #F1F5F9; padding: 2px 6px; border-radius: 4px;">
                                    🔒 <?= e($enq['masked_email']) ?>
                                </small><br>
                                <small style="color: #64748B; font-family: monospace; background: #F1F5F9; padding: 2px 6px; border-radius: 4px;">
                                    🔒 <?= e($enq['masked_phone']) ?>
                                </small>
                            <?php else: ?>
                                <small style="color: var(--color-primary); font-weight: 600;">✉️ <?= e($enq['email']) ?></small><br>
                                <small style="color: #0F172A; font-weight: 600;">📞 <?= e($enq['phone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= url('properties/' . $enq['property_slug']) ?>" target="_blank" style="color: var(--color-primary); font-weight: 600;">
                                <?= e($enq['property_title']) ?>
                            </a><br>
                            <small style="color: var(--color-text-muted);"><?= format_money($enq['price']) ?></small>
                        </td>
                        <td>
                            <form action="<?= url('dashboard/enquiries/' . $enq['id'] . '/status') ?>" method="POST" style="margin: 0;">
                                <?= csrf_field() ?>
                                <select name="status" class="form-control" style="font-size: 12px; height: 34px; padding: 4px 8px; font-weight: 600;" onchange="this.form.submit()">
                                    <option value="new" <?= $enq['status'] === 'new' ? 'selected' : '' ?>>🔵 New Lead</option>
                                    <option value="contacted" <?= $enq['status'] === 'contacted' ? 'selected' : '' ?>>🟡 Contacted</option>
                                    <option value="qualified" <?= $enq['status'] === 'qualified' ? 'selected' : '' ?>>⭐ Qualified Buyer</option>
                                    <option value="viewing_scheduled" <?= $enq['status'] === 'viewing_scheduled' ? 'selected' : '' ?>>📅 Viewing Scheduled</option>
                                    <option value="converted" <?= $enq['status'] === 'converted' ? 'selected' : '' ?>>🟢 Converted / Won</option>
                                    <option value="closed" <?= $enq['status'] === 'closed' ? 'selected' : '' ?>>⚪ Closed / Lost</option>
                                    <option value="spam" <?= $enq['status'] === 'spam' ? 'selected' : '' ?>>🔴 Spam</option>
                                </select>
                            </form>
                        </td>
                        <td style="max-width: 260px;">
                            <div style="font-size: 13px; color: var(--color-secondary); line-height: 1.5;">
                                "<?= e($enq['message']) ?>"
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <?php if (empty($enq['contact_unlocked'])): ?>
                                    <form action="<?= url('dashboard/enquiries/' . $enq['id'] . '/unlock') ?>" method="POST" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; font-size: 11px;">
                                            🔓 Unlock Contact
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $enq['phone']) ?>?text=<?= urlencode('Hello ' . $enq['name'] . ', regarding your enquiry for ' . $enq['property_title'] . ' on Afriserva Real Estate...') ?>" target="_blank" class="btn btn-accent btn-sm" style="font-size: 11px; text-align: center;">
                                        💬 WhatsApp
                                    </a>
                                <?php endif; ?>
                                <a href="<?= url('dashboard/messages') ?>" class="btn btn-outline btn-sm" style="font-size: 11px; text-align: center;">
                                    💬 Chat Thread
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
