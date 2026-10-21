<div style="background: linear-gradient(135deg, var(--color-primary) 0%, #1a2a40 100%); color: #ffffff; padding: 60px 0 40px;">
    <div class="container">
        <div style="max-width: 800px;">
            <div style="display: inline-block; background: rgba(200, 150, 62, 0.2); border: 1px solid var(--color-accent); padding: 4px 12px; border-radius: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; color: var(--color-accent); margin-bottom: 12px;">
                Institutional Research & Advisory
            </div>
            <h1 style="font-size: 36px; font-weight: 800; line-height: 1.2; margin-bottom: 16px; font-family: var(--font-heading);">
                Market Intelligence & Property Insights
            </h1>
            <p style="font-size: 16px; color: rgba(255, 255, 255, 0.8); line-height: 1.6; margin-bottom: 24px;">
                Macroeconomic indices, conveyancing law, title perfection protocols, and commercial yield forecasts across Nigerian real estate corridors.
            </p>

            <!-- Search Bar -->
            <form action="<?= url('/market-insights') ?>" method="GET" style="display: flex; gap: 12px; background: rgba(255, 255, 255, 0.1); padding: 6px; border-radius: var(--radius-md); border: 1px solid rgba(255, 255, 255, 0.2);">
                <input type="text" name="q" value="<?= e($searchQuery ?? '') ?>" placeholder="Search legal guides, yields, LASRERA mandates..." style="flex: 1; background: transparent; border: none; color: #ffffff; padding: 10px 14px; font-size: 14px; outline: none;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: 13px;">Search Insights</button>
            </form>
        </div>
    </div>
</div>

<div class="container" style="padding: 40px 0 80px;">
    <!-- Category Tabs -->
    <div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 16px; margin-bottom: 32px; border-bottom: 1px solid var(--color-border);">
        <a href="<?= url('/market-insights') ?>" 
           style="padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.2s; <?= empty($selectedCategory) ? 'background: var(--color-primary); color: #ffffff;' : 'background: #F1F5F9; color: var(--color-secondary);' ?>">
            All Topics
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= url('/market-insights?category=' . urlencode($cat)) ?>" 
               style="padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.2s; <?= ($selectedCategory === $cat) ? 'background: var(--color-primary); color: #ffffff;' : 'background: #F1F5F9; color: var(--color-secondary);' ?>">
                <?= e($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Articles Grid -->
    <?php if (empty($articles)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border);">
            <div style="font-size: 40px; margin-bottom: 16px;">📰</div>
            <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 8px; color: var(--color-primary);">No Articles Found</h3>
            <p style="color: var(--color-secondary); font-size: 14px; margin-bottom: 20px;">No published guides match your search or selected category filter.</p>
            <a href="<?= url('/market-insights') ?>" class="btn btn-primary" style="font-size: 13px;">View All Insights</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 32px;">
            <?php foreach ($articles as $art): ?>
                <article style="background: #FFFFFF; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;">
                    <a href="<?= url('/market-insights/' . e($art['slug'])) ?>" style="text-decoration: none; display: block; position: relative;">
                        <img src="<?= e($art['image_url']) ?>" alt="<?= e($art['title']) ?>" style="height: 220px; width: 100%; object-fit: cover; display: block;">
                        <span style="position: absolute; top: 12px; left: 12px; background: rgba(10, 25, 47, 0.85); backdrop-filter: blur(4px); color: #C8963E; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 4px; text-transform: uppercase;">
                            <?= e($art['category']) ?>
                        </span>
                    </a>
                    <div style="padding: 24px; display: flex; flex-direction: column; flex-grow: 1;">
                        <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 8px; display: flex; justify-content: space-between;">
                            <span><?= e($art['read_time'] ?? '5 min read') ?></span>
                            <span><?= date('M j, Y', strtotime($art['published_at'])) ?></span>
                        </div>
                        <h2 style="font-size: 18px; font-weight: 700; line-height: 1.4; margin-bottom: 12px;">
                            <a href="<?= url('/market-insights/' . e($art['slug'])) ?>" style="color: var(--color-primary); text-decoration: none;">
                                <?= e($art['title']) ?>
                            </a>
                        </h2>
                        <p style="font-size: 13px; color: var(--color-secondary); line-height: 1.6; margin-bottom: 20px; flex-grow: 1;">
                            <?= e($art['excerpt']) ?>
                        </p>
                        <div style="border-top: 1px solid var(--color-border); padding-top: 14px; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <span style="font-size: 12px; font-weight: 700; color: var(--color-primary);"><?= e($art['author_name']) ?></span>
                                <div style="font-size: 11px; color: var(--color-text-muted);"><?= e($art['author_role']) ?></div>
                            </div>
                            <a href="<?= url('/market-insights/' . e($art['slug'])) ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px;">Read Full Guide &rarr;</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
