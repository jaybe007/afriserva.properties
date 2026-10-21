<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <h2 style="font-size: 24px;">Global Platform Settings & Configuration</h2>
        <p style="color: var(--color-text-muted);">Manage branding, operational policies, gateway toggles, and institutional contacts.</p>
    </div>

    <form action="<?= url('admin/settings') ?>" method="POST" style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 40px; box-shadow: var(--shadow-subtle);">
        <?= csrf_field() ?>

        <?php 
        $currentGroup = '';
        foreach ($settings as $s): 
            if ($s['group'] !== $currentGroup):
                $currentGroup = $s['group'];
        ?>
            <h3 style="font-size: 16px; text-transform: uppercase; letter-spacing: 1px; color: var(--color-accent); margin: 28px 0 16px; border-bottom: 1px solid var(--color-border); padding-bottom: 6px;">
                <?= ucfirst($currentGroup) ?> Configuration
            </h3>
        <?php endif; ?>

            <div class="form-group">
                <label class="form-label">
                    <?= e(ucwords(str_replace('_', ' ', $s['key']))) ?>
                    <?php if (!empty($s['description'])): ?>
                        <small style="font-weight: normal; color: var(--color-text-muted);">— <?= e($s['description']) ?></small>
                    <?php endif; ?>
                </label>

                <?php if (in_array($s['key'], ['listing_auto_approve', 'paystack_active', 'flutterwave_active'])): ?>
                    <select name="<?= e($s['key']) ?>" class="form-control">
                        <option value="1" <?= $s['value'] == '1' ? 'selected' : '' ?>>Enabled / Active</option>
                        <option value="0" <?= $s['value'] == '0' ? 'selected' : '' ?>>Disabled / Inactive</option>
                    </select>
                <?php else: ?>
                    <input type="text" name="<?= e($s['key']) ?>" class="form-control" value="<?= e($s['value']) ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-accent btn-lg" style="width: 100%; margin-top: 24px;">
            Save Global System Settings ➔
        </button>
    </form>
</div>
