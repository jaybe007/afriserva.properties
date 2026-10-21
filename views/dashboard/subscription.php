<div style="margin-bottom: 32px; text-align: center; max-width: 680px; margin-left: auto; margin-right: auto;">
    <span class="badge badge-gold" style="margin-bottom: 12px;">Institutional Monetization</span>
    <h2 style="font-size: 32px; margin-bottom: 12px;">Professional Memberships & Monetization Plans</h2>
    <p style="color: var(--color-text-muted);">
        Maximize your portfolio visibility, receive verified badge endorsement, and access high-intent institutional buyers.
    </p>

    <?php if ($currentSub): ?>
        <div class="alert alert-success" style="margin-top: 24px; text-align: left;">
            ✓ You have an active <strong><?= e($currentSub['plan_name']) ?></strong> membership running until <strong><?= date('M d, Y', strtotime($currentSub['ends_at'])) ?></strong>.
        </div>
    <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
    <?php foreach ($plans as $plan): ?>
        <div style="background: #FFFFFF; border-radius: var(--radius-xl); border: 2px solid <?= ($plan['slug'] === 'professional-agent') ? 'var(--color-accent)' : 'var(--color-border)' ?>; padding: 32px; box-shadow: var(--shadow-card); display: flex; flex-direction: column; position: relative;">
            <?php if ($plan['slug'] === 'professional-agent'): ?>
                <span class="badge badge-gold" style="position: absolute; top: -12px; right: 24px;">MOST POPULAR</span>
            <?php endif; ?>

            <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 8px;"><?= e($plan['name']) ?></h3>
            <p style="font-size: 13px; color: var(--color-text-muted); min-height: 40px; margin-bottom: 20px;">
                <?= e($plan['description']) ?>
            </p>

            <div style="margin-bottom: 24px;">
                <span style="font-size: 36px; font-weight: 700; color: var(--color-primary); font-family: var(--font-heading);">
                    <?= format_money($plan['price']) ?>
                </span>
                <span style="font-size: 13px; color: var(--color-text-muted);">/ <?= e($plan['billing_interval']) ?></span>
            </div>

            <ul style="list-style: none; margin-bottom: 32px; font-size: 14px; line-height: 2;">
                <li>✓ <strong><?= $plan['listing_allowance'] ?></strong> Active Listing Slots</li>
                <li>✓ <strong><?= $plan['featured_allowance'] ?></strong> Featured Priority Badges</li>
                <li>✓ <strong><?= $plan['team_members_limit'] ?></strong> Team / Staff Account</li>
                <li>✓ WhatsApp & Direct Call Buttons</li>
                <li>✓ Verified Title & Legal Document Review</li>
                <li>✓ Dedicated Account Manager</li>
            </ul>

            <div style="margin-top: auto;">
                <a href="<?= url('checkout/' . $plan['slug']) ?>" class="btn <?= ($plan['slug'] === 'professional-agent') ? 'btn-accent' : 'btn-outline' ?>" style="width: 100%;">
                    Select <?= e($plan['name']) ?> ➔
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
