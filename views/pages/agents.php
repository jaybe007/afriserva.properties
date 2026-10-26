<div class="container section" style="padding-top: 40px;">
    <!-- Header -->
    <div style="text-align: center; max-width: 800px; margin: 0 auto 48px;">
        <span class="badge badge-gold" style="margin-bottom: 12px;">★ Accredited Real Estate Advisory</span>
        <h1 style="font-size: 38px; margin-bottom: 16px;">Verified Real Estate Agents & Brokers</h1>
        <p style="font-size: 16px; color: var(--color-text-muted); line-height: 1.7;">
            Connect with certified professionals accredited by NIESV, ESVARBON, and LASRERA for luxury residential acquisitions, commercial leasing, and land conveyancing.
        </p>
    </div>

    <!-- Agents Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px;">
        <?php foreach ($agents as $agent): ?>
            <div style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 32px; text-align: center; box-shadow: var(--shadow-card); display: flex; flex-direction: column;">
                <img src="<?= e($agent['avatar_url'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80') ?>" alt="<?= e($agent['full_name']) ?>" style="width: 96px; height: 96px; border-radius: var(--radius-full); object-fit: cover; margin: 0 auto 16px; border: 3px solid var(--color-accent-light);">

                <h3 style="font-size: 20px; font-weight: 700; color: var(--color-primary); margin-bottom: 4px;">
                    <?= e($agent['full_name']) ?>
                </h3>

                <div style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 8px;">
                    <?= e($agent['agency_name'] ?? 'Independent Advisory') ?>
                </div>

                <div style="font-size: 13px; color: var(--color-accent); font-weight: 700; margin-bottom: 16px;">
                    ★ <?= $agent['rating'] ?> Rating (<?= $agent['total_reviews'] ?> Verified Reviews)
                </div>

                <p style="font-size: 14px; color: var(--color-secondary); line-height: 1.6; margin-bottom: 24px; min-height: 66px;">
                    <?= e($agent['specialization'] ?? 'Luxury Residential & Commercial Real Estate Advisory') ?>
                </p>

                <div style="display: flex; gap: 8px; justify-content: center; margin-bottom: 24px;">
                    <span class="badge badge-verified">✓ NIESV / LASRERA</span>
                    <span class="badge badge-primary"><?= $agent['total_listings'] ?> Active Listings</span>
                </div>

                <div style="margin-top: auto; display: flex; flex-direction: column; gap: 10px;">
                    <a href="<?= url('properties?agent=' . $agent['id']) ?>" class="btn btn-primary" style="width: 100%;">
                        🏛️ View Portfolio Listings
                    </a>
                    <a href="<?= url('properties') ?>" class="btn btn-outline btn-sm" style="width: 100%;">
                        🛡️ Inquire via Afriserva Desk
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
