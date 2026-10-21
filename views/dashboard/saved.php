<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">My Saved Properties & Shortlist</h1>
        <p class="dashboard-subtitle">Monitor price updates, review inspection notes, and revisit your shortlisted luxury residences.</p>
    </div>
    <div>
        <a href="<?= url('properties') ?>" class="btn btn-primary">Browse Marketplace ➔</a>
    </div>
</div>

<?php if (empty($savedProperties)): ?>
    <div class="dashboard-card" style="text-align: center; padding: 64px 24px;">
        <div style="font-size: 54px; margin-bottom: 16px;">🤍</div>
        <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 8px;">Your Shortlist is Currently Empty</h3>
        <p style="font-size: 14px; color: var(--color-text-muted); max-width: 480px; margin: 0 auto 24px;">
            Save luxury residences, commercial office towers, or short-let apartments to compare amenities and track status changes.
        </p>
        <a href="<?= url('properties') ?>" class="btn btn-accent">Explore Verified Listings</a>
    </div>
<?php else: ?>
    <div class="property-grid" style="margin-top: 0;">
        <?php foreach ($savedProperties as $prop): ?>
            <article class="property-card">
                <div class="card-media">
                    <img src="<?= e($prop['primary_image'] ?? 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= e($prop['title']) ?>" loading="lazy">
                    <div class="card-badges">
                        <span class="badge badge-primary"><?= strtoupper($prop['transaction_type']) ?></span>
                        <?php if ($prop['is_luxury']): ?>
                            <span class="badge badge-gold">★ Luxury Signature</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-price-tag">
                        <?= format_money($prop['price'], $prop['currency']) ?>
                        <?php if (!empty($prop['price_suffix'])): ?>
                            <span style="font-size: 11px; font-weight: normal; opacity: 0.8;"><?= e($prop['price_suffix']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-content">
                    <div class="card-location">
                        📍 <?= e($prop['city_name'] ?? '') ?>, <?= e($prop['state_name'] ?? '') ?>
                    </div>

                    <a href="<?= url('properties/' . $prop['slug']) ?>">
                        <h3 class="card-title"><?= e($prop['title']) ?></h3>
                    </a>

                    <div class="card-features">
                        <?php if ($prop['bedrooms'] > 0): ?>
                            <div class="card-feature-item">🛏 <?= $prop['bedrooms'] ?> Beds</div>
                        <?php endif; ?>
                        <?php if ($prop['bathrooms'] > 0): ?>
                            <div class="card-feature-item">🚿 <?= $prop['bathrooms'] ?> Baths</div>
                        <?php endif; ?>
                        <?php if ($prop['building_size_sqm'] > 0): ?>
                            <div class="card-feature-item">📐 <?= format_sqm($prop['building_size_sqm']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer" style="padding-top: 16px;">
                        <form action="<?= url('properties/' . $prop['id'] . '/favorite') ?>" method="POST" style="margin: 0;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline btn-sm" style="color: var(--color-danger); border-color: #FECACA;">
                                ✕ Remove
                            </button>
                        </form>

                        <a href="<?= url('properties/' . $prop['slug']) ?>" class="btn btn-primary btn-sm">
                            View Details ➔
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
