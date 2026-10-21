<!-- Schema.org Article Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "<?= e($article['title']) ?>",
  "description": "<?= e($article['excerpt']) ?>",
  "image": "<?= e($article['image_url']) ?>",
  "datePublished": "<?= date('c', strtotime($article['published_at'])) ?>",
  "dateModified": "<?= date('c', strtotime($article['published_at'])) ?>",
  "author": {
    "@type": "Person",
    "name": "<?= e($article['author_name']) ?>",
    "jobTitle": "<?= e($article['author_role']) ?>"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Afriserva Real Estate",
    "url": "https://afriserva.com",
    "parentOrganization": {
      "@type": "Organization",
      "name": "Cloud Current International Limited",
      "url": "https://cloudcurrentng.com"
    },
    "logo": {
      "@type": "ImageObject",
      "url": "<?= e(url('/assets/images/logo.png')) ?>"
    }
  },
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "<?= e(url('/market-insights/' . $article['slug'])) ?>"
  }
}
</script>

<div class="container" style="padding: 40px 0 80px; max-width: 960px;">
    <!-- Breadcrumb -->
    <nav style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 24px;">
        <a href="<?= url('/') ?>" style="color: var(--color-secondary); text-decoration: none;">Home</a> &rsaquo;
        <a href="<?= url('/market-insights') ?>" style="color: var(--color-secondary); text-decoration: none;">Market Insights</a> &rsaquo;
        <span style="color: var(--color-primary); font-weight: 600;"><?= e($article['category']) ?></span>
    </nav>

    <!-- Header & Title -->
    <header style="margin-bottom: 32px;">
        <div style="display: inline-block; background: rgba(200, 150, 62, 0.15); border: 1px solid var(--color-accent); color: var(--color-accent); font-size: 11px; text-transform: uppercase; font-weight: 700; padding: 4px 10px; border-radius: 4px; margin-bottom: 16px;">
            <?= e($article['category']) ?>
        </div>
        <h1 style="font-size: 36px; font-weight: 800; line-height: 1.25; color: var(--color-primary); font-family: var(--font-heading); margin-bottom: 20px;">
            <?= e($article['title']) ?>
        </h1>
        
        <!-- Author Byline Bar -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding: 16px 0; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-primary); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px;">
                    <?= strtoupper(substr($article['author_name'], 0, 1)) ?>
                </div>
                <div>
                    <div style="font-size: 14px; font-weight: 700; color: var(--color-primary);"><?= e($article['author_name']) ?></div>
                    <div style="font-size: 12px; color: var(--color-text-muted);"><?= e($article['author_role']) ?></div>
                </div>
            </div>
            <div style="font-size: 13px; color: var(--color-text-muted); display: flex; gap: 16px;">
                <span>📅 Published: <?= date('F j, Y', strtotime($article['published_at'])) ?></span>
                <span>⏱️ <?= e($article['read_time'] ?? '5 min read') ?></span>
            </div>
        </div>
    </header>

    <!-- Cover Image -->
    <div style="margin-bottom: 36px; border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md);">
        <img src="<?= e($article['image_url']) ?>" alt="<?= e($article['title']) ?>" style="width: 100%; max-height: 480px; object-fit: cover; display: block;">
    </div>

    <!-- Article Content -->
    <div style="display: grid; grid-template-columns: 1fr 280px; gap: 40px; align-items: start;">
        <main>
            <!-- Lead Paragraph -->
            <div style="font-size: 18px; line-height: 1.7; color: var(--color-secondary); font-weight: 500; margin-bottom: 28px; padding-left: 16px; border-left: 3px solid var(--color-accent);">
                <?= e($article['excerpt']) ?>
            </div>

            <!-- Body text with paragraphs -->
            <div style="font-size: 15px; line-height: 1.8; color: #2D3748; margin-bottom: 40px;">
                <?php 
                $paragraphs = explode("\n", $article['content']);
                foreach ($paragraphs as $p):
                    $p = trim($p);
                    if (!empty($p)):
                ?>
                    <p style="margin-bottom: 20px;"><?= nl2br(e($p)) ?></p>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>

            <!-- Regulatory Advisory Notice -->
            <div style="background: #F8FAFC; border: 1px solid var(--color-border); border-left: 4px solid var(--color-primary); padding: 20px; border-radius: 4px; margin-bottom: 40px;">
                <h4 style="font-size: 14px; font-weight: 700; color: var(--color-primary); margin-bottom: 6px;">
                    ⚖️ Statutory Conveyancing Advisory
                </h4>
                <p style="font-size: 12px; color: var(--color-secondary); line-height: 1.6; margin: 0;">
                    This analysis is provided for market intelligence purposes in accordance with the Nigerian Land Use Act (1978) and applicable State Real Estate Regulatory Authority regulations. Investors are advised to engage licensed legal counsel for formal search reports at the appropriate State Lands Bureau.
                </p>
            </div>

            <!-- Share and Navigation -->
            <div style="border-top: 1px solid var(--color-border); padding-top: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <a href="<?= url('/market-insights') ?>" class="btn btn-outline" style="font-size: 13px;">
                    &larr; Back to Market Insights
                </a>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <span style="font-size: 13px; font-weight: 600; color: var(--color-secondary);">Share Guide:</span>
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['title'] . ' ' . url('/market-insights/' . $article['slug'])) ?>" target="_blank" class="btn btn-outline" style="padding: 4px 10px; font-size: 12px; color: #25D366; border-color: #25D366;">
                        WhatsApp
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(url('/market-insights/' . $article['slug'])) ?>" target="_blank" class="btn btn-outline" style="padding: 4px 10px; font-size: 12px; color: #0A66C2; border-color: #0A66C2;">
                        LinkedIn
                    </a>
                </div>
            </div>
        </main>

        <!-- Sidebar -->
        <aside>
            <!-- Consultation Card -->
            <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px; text-align: center;">
                <div style="font-size: 28px; margin-bottom: 8px;">🏛️</div>
                <h4 style="font-size: 16px; font-weight: 700; color: var(--color-primary); margin-bottom: 8px;">Advisory Services</h4>
                <p style="font-size: 12px; color: var(--color-secondary); line-height: 1.5; margin-bottom: 16px;">
                    Require bespoke title verification, diaspora escrow management, or corporate leasing representation?
                </p>
                <a href="<?= url('/agents') ?>" class="btn btn-primary" style="width: 100%; font-size: 13px; padding: 10px;">Connect with Advisors</a>
            </div>

            <!-- Related Articles -->
            <?php if (!empty($related)): ?>
                <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-sm);">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--color-primary); margin-bottom: 16px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
                        Related Intelligence
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php foreach ($related as $rel): ?>
                            <div>
                                <a href="<?= url('/market-insights/' . e($rel['slug'])) ?>" style="font-size: 13px; font-weight: 600; color: var(--color-primary); text-decoration: none; line-height: 1.4; display: block; margin-bottom: 4px;">
                                    <?= e($rel['title']) ?>
                                </a>
                                <span style="font-size: 11px; color: var(--color-text-muted);"><?= e($rel['read_time'] ?? '4 min read') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
