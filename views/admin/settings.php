<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 700; color: var(--color-primary); margin: 0 0 4px;">Global Platform Settings & Operational Policies</h2>
            <p style="color: var(--color-text-muted); margin: 0;">Configure corporate identity, payment gateway switches, listing compliance rules, and regional localization.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <span class="badge badge-gold" style="font-size: 11px; padding: 6px 12px;">
                ● Live Production Parameters
            </span>
        </div>
    </div>

    <form action="<?= url('admin/settings') ?>" method="POST">
        <?= csrf_field() ?>

        <?php 
        $grouped = [];
        foreach ($settings as $s) {
            $grouped[$s['group']][] = $s;
        }

        $groupMeta = [
            'general' => ['title' => 'Brand & Identity Configuration', 'icon' => '🏢', 'desc' => 'Primary branding, public marketplace title, and corporate slogans.'],
            'contact' => ['title' => 'Support & Communication Channels', 'icon' => '📞', 'desc' => 'Hotlines, corporate office location, and customer service email dispatches.'],
            'localization' => ['title' => 'Currency & Regional Parameters', 'icon' => '🗺️', 'desc' => 'Monetary representation, currency codes, and decimal formatting.'],
            'payment' => ['title' => 'Payment Gateway Infrastructure', 'icon' => '💳', 'desc' => 'Enable or disable Paystack and Flutterwave payment processing channels.'],
            'moderation' => ['title' => 'Listing Verification & Auto-Approval', 'icon' => '🛡️', 'desc' => 'Enforce mandatory statutory legal document review before public discovery.']
        ];
        ?>

        <?php foreach ($grouped as $groupKey => $groupSettings): 
            $meta = $groupMeta[$groupKey] ?? ['title' => ucfirst($groupKey) . ' Settings', 'icon' => '⚙️', 'desc' => ''];
        ?>
            <div class="table-card" style="margin-bottom: 24px; padding: 24px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                    <div style="font-size: 20px;"><?= $meta['icon'] ?></div>
                    <div>
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--color-primary); margin: 0;"><?= e($meta['title']) ?></h3>
                        <?php if (!empty($meta['desc'])): ?>
                            <div style="font-size: 12px; color: var(--color-text-muted);"><?= e($meta['desc']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr; gap: 16px;">
                    <?php foreach ($groupSettings as $s): ?>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <span style="font-weight: 600; font-size: 13px; color: var(--color-primary);">
                                    <?= e(ucwords(str_replace('_', ' ', $s['key']))) ?>
                                </span>
                                <?php if (!empty($s['description'])): ?>
                                    <span style="font-size: 11px; color: var(--color-text-muted); font-weight: normal;">
                                        <?= e($s['description']) ?>
                                    </span>
                                <?php endif; ?>
                            </label>

                            <?php if (in_array($s['key'], ['listing_auto_approve', 'paystack_active', 'flutterwave_active'])): ?>
                                <select name="<?= e($s['key']) ?>" class="form-control" style="font-weight: 600;">
                                    <option value="1" <?= $s['value'] == '1' ? 'selected' : '' ?> style="color: #10B981;">✓ Enabled / Active</option>
                                    <option value="0" <?= $s['value'] == '0' ? 'selected' : '' ?> style="color: #EF4444;">✕ Disabled / Inactive</option>
                                </select>
                            <?php else: ?>
                                <input type="text" name="<?= e($s['key']) ?>" class="form-control" value="<?= e($s['value']) ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div style="position: sticky; bottom: 20px; background: rgba(255,255,255,0.95); backdrop-filter: blur(8px); padding: 16px 24px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-luxury); display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 12px; color: var(--color-text-muted);">
                Changes take effect across the live platform immediately upon submission.
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 10px 28px; font-weight: 700;">
                💾 Save Global Configurations
            </button>
        </div>
    </form>
</div>
