<div class="container section" style="padding-top: 40px;">
    <!-- Header -->
    <div style="text-align: center; max-width: 800px; margin: 0 auto 48px;">
        <span class="badge badge-gold" style="margin-bottom: 12px;">★ Prime Construction & Off-Plan Offerings</span>
        <h1 style="font-size: 38px; margin-bottom: 16px;">New Developments & Off-Plan Estates</h1>
        <p style="font-size: 16px; color: var(--color-text-muted); line-height: 1.7;">
            Secure institutional pre-construction pricing with structured milestone payment plans on master-planned luxury coastal towers, gated suburban estates, and commercial hubs.
        </p>
    </div>

    <!-- Development Benefits Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 48px;">
        <div style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 24px; text-align: center; box-shadow: var(--shadow-subtle);">
            <div style="font-size: 32px; margin-bottom: 12px;">📈</div>
            <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 8px;">25% - 35% Capital Appreciation</h4>
            <p style="font-size: 13px; color: var(--color-text-muted);">Anticipated equity growth between foundation phase and keys handover.</p>
        </div>
        <div style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 24px; text-align: center; box-shadow: var(--shadow-subtle);">
            <div style="font-size: 32px; margin-bottom: 12px;">📅</div>
            <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 8px;">Milestone Payment Plans</h4>
            <p style="font-size: 13px; color: var(--color-text-muted);">Spread payments over 12 to 36 months tied strictly to verified engineering milestones.</p>
        </div>
        <div style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 24px; text-align: center; box-shadow: var(--shadow-subtle);">
            <div style="font-size: 32px; margin-bottom: 12px;">🛡️</div>
            <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 8px;">Approved Building Permits</h4>
            <p style="font-size: 13px; color: var(--color-text-muted);">All showcased developments possess approved building plans and EIA environmental audits.</p>
        </div>
    </div>

    <!-- Developments Grid -->
    <div class="property-grid" style="margin-top: 0;">
        <?php foreach ($developments as $dev): ?>
            <article class="property-card">
                <div class="card-media">
                    <img src="<?= e($dev['cover_image']) ?>" alt="<?= e($dev['title']) ?>" loading="lazy">
                    <div class="card-badges">
                        <span class="badge badge-gold">🏗 Under Construction</span>
                        <span class="badge badge-verified">✓ Title Verified</span>
                    </div>
                    <div class="card-price-tag">
                        <?= format_money($dev['price'], $dev['currency']) ?>
                        <span style="font-size: 12px; font-weight: normal;"><?= e($dev['price_suffix']) ?></span>
                    </div>
                </div>

                <div class="card-content">
                    <div class="card-location">
                        📍 <?= e($dev['neighbourhood_name'] ?? $dev['city_name']) ?>, <?= e($dev['state_name']) ?>
                    </div>

                    <h3 class="card-title">
                        <a href="<?= url('properties/' . $dev['slug']) ?>">
                            <?= e($dev['title']) ?>
                        </a>
                    </h3>

                    <div class="card-features">
                        <?php if ($dev['bedrooms'] > 0): ?>
                            <div class="card-feature-item">🛏 <?= $dev['bedrooms'] ?> Beds</div>
                        <?php endif; ?>
                        <?php if ($dev['bathrooms'] > 0): ?>
                            <div class="card-feature-item">🚿 <?= $dev['bathrooms'] ?> Baths</div>
                        <?php endif; ?>
                        <?php if ($dev['building_size_sqm'] > 0): ?>
                            <div class="card-feature-item">📐 <?= format_sqm($dev['building_size_sqm']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer">
                        <span style="font-size: 12px; font-weight: 600; color: var(--color-accent);">Handover: 2026/2027</span>
                        <a href="<?= url('properties/' . $dev['slug']) ?>" class="btn btn-outline btn-sm">
                            Inspect Project →
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
