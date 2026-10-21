<div class="container section" style="padding-top: 40px;">
    <!-- Header -->
    <div style="text-align: center; max-width: 800px; margin: 0 auto 48px;">
        <span class="badge badge-gold" style="margin-bottom: 12px;">★ Licensed Real Estate Brokerages</span>
        <h1 style="font-size: 38px; margin-bottom: 16px;">Corporate Agencies & Advisory Firms</h1>
        <p style="font-size: 16px; color: var(--color-text-muted); line-height: 1.7;">
            Discover accredited real estate firms with verified CAC registrations, institutional track records, and dedicated advisory teams.
        </p>
    </div>

    <!-- Agencies Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 32px;">
        <?php foreach ($agencies as $agency): ?>
            <div style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 36px; box-shadow: var(--shadow-card); display: flex; flex-direction: column;">
                <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 20px;">
                    <div style="width: 56px; height: 56px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%); display: flex; align-items: center; justify-content: center; color: var(--color-accent); font-size: 24px; font-weight: 700;">
                        🏢
                    </div>
                    <div>
                        <h3 style="font-size: 20px; font-weight: 700; color: var(--color-primary); line-height: 1.3;">
                            <?= e($agency['name']) ?>
                        </h3>
                        <div style="font-size: 12px; color: var(--color-text-muted);">
                            📍 <?= e($agency['city']) ?>, <?= e($agency['state']) ?>
                        </div>
                    </div>
                </div>

                <p style="font-size: 14px; color: var(--color-secondary); line-height: 1.6; margin-bottom: 20px; min-height: 66px;">
                    <?= e($agency['description'] ?? 'Premier institutional brokerage specializing in prime residential sales and commercial acquisitions.') ?>
                </p>

                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px;">
                    <span class="badge badge-verified">✓ CAC: <?= e($agency['cac_number'] ?? 'RC-Verified') ?></span>
                    <span class="badge badge-gold">LASRERA Registered</span>
                    <span class="badge badge-primary"><?= $agency['total_listings'] ?> Portfolios</span>
                </div>

                <div style="margin-top: auto; border-top: 1px solid var(--color-border); padding-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 13px; color: var(--color-text-muted);">
                        📞 <?= e($agency['phone']) ?>
                    </div>
                    <a href="<?= url('properties?keyword=' . urlencode($agency['name'])) ?>" class="btn btn-outline btn-sm">
                        View Portfolio ↗
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
