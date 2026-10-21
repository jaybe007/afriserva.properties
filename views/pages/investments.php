<div class="container section" style="padding-top: 40px;">
    <!-- Header -->
    <div style="text-align: center; max-width: 800px; margin: 0 auto 48px;">
        <span class="badge badge-gold" style="margin-bottom: 12px;">★ Wealth Preservation & Yield Growth</span>
        <h1 style="font-size: 38px; margin-bottom: 16px;">Real Estate Investment Opportunities</h1>
        <p style="font-size: 16px; color: var(--color-text-muted); line-height: 1.7;">
            High-conviction real estate investment assets across Nigeria’s most resilient commercial corridors and prime residential markets.
        </p>
    </div>

    <!-- Investment Pillars Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 56px;">
        <div style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 32px; box-shadow: var(--shadow-subtle);">
            <div style="font-size: 36px; margin-bottom: 16px;">🏢</div>
            <h3 style="font-size: 18px; margin-bottom: 8px;">Grade-A Commercial Leasing</h3>
            <p style="font-size: 14px; color: var(--color-text-muted); line-height: 1.6;">
                Long-term corporate leases to multinational banks, oil & gas conglomerates, and diplomatic missions in Victoria Island, Ikoyi, and Maitama.
            </p>
        </div>

        <div style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 32px; box-shadow: var(--shadow-subtle);">
            <div style="font-size: 36px; margin-bottom: 16px;">🗺️</div>
            <h3 style="font-size: 18px; margin-bottom: 8px;">Strategic Land Banking</h3>
            <p style="font-size: 14px; color: var(--color-text-muted); line-height: 1.6;">
                Acquiring high-growth acreage with Gazette and Certificate of Occupancy titles along the Lekki-Epe Expressway and Abuja expansion axes.
            </p>
        </div>

        <div style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 32px; box-shadow: var(--shadow-subtle);">
            <div style="font-size: 36px; margin-bottom: 16px;">🔑</div>
            <h3 style="font-size: 18px; margin-bottom: 8px;">Executive Short-Let Hospitality</h3>
            <p style="font-size: 14px; color: var(--color-text-muted); line-height: 1.6;">
                Generating 18% to 26% net annualized cash flow through professionally serviced corporate suites in Old Ikoyi and Eko Atlantic.
            </p>
        </div>
    </div>

    <!-- Curated Investment Properties -->
    <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 26px;">Featured Investment Assets</h2>
        <a href="<?= url('properties') ?>" class="btn btn-outline btn-sm">All Investment Properties →</a>
    </div>

    <div class="property-grid" style="margin-top: 0;">
        <?php foreach ($properties as $prop): ?>
            <article class="property-card">
                <div class="card-media">
                    <img src="<?= e($prop['cover_image']) ?>" alt="<?= e($prop['title']) ?>" loading="lazy">
                    <div class="card-badges">
                        <span class="badge badge-gold">Yield Asset</span>
                        <span class="badge badge-verified">✓ Verified</span>
                    </div>
                    <div class="card-price-tag">
                        <?= format_money($prop['price'], $prop['currency']) ?>
                    </div>
                </div>

                <div class="card-content">
                    <div class="card-location">
                        📍 <?= e($prop['neighbourhood_name'] ?? $prop['city_name']) ?>, <?= e($prop['state_name']) ?>
                    </div>

                    <h3 class="card-title">
                        <a href="<?= url('properties/' . $prop['slug']) ?>">
                            <?= e($prop['title']) ?>
                        </a>
                    </h3>

                    <div class="card-features">
                        <div class="card-feature-item">📐 <?= format_sqm($prop['building_size_sqm'] ?? $prop['land_size_sqm']) ?></div>
                        <div class="card-feature-item">📜 <?= e($prop['title_document'] ?? 'C of O') ?></div>
                    </div>

                    <div class="card-footer">
                        <span style="font-size: 12px; font-weight: 700; color: var(--color-success);">Est. Yield: 9% - 14%</span>
                        <a href="<?= url('properties/' . $prop['slug']) ?>" class="btn btn-outline btn-sm">
                            Analyze Deal →
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
