<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Afriserva Real Estate | Nigeria’s Premier Property Ecosystem') ?></title>
    <meta name="description" content="<?= e($metaDescription ?? 'Discover luxury waterfront residences, commercial towers, verified residential plots, and off-plan estates across Lagos, Abuja, and Port Harcourt.') ?>">
    <meta name="robots" content="<?= e($robotsDirective ?? 'index, follow') ?>">
    <link rel="canonical" href="<?= e($canonicalUrl ?? (url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)))) ?>">
    
    <!-- OpenGraph & WhatsApp Social Previews -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Afriserva Real Estate">
    <meta property="og:title" content="<?= e($title ?? 'Afriserva Real Estate | Nigeria’s Premier Property Ecosystem') ?>">
    <meta property="og:description" content="<?= e($metaDescription ?? 'Discover luxury waterfront residences, commercial towers, verified residential plots, and off-plan estates across Lagos, Abuja, and Port Harcourt.') ?>">
    <meta property="og:url" content="<?= e(url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH))) ?>">
    <meta property="og:image" content="<?= e($ogImage ?? 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80') ?>">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Schema.org Institutional Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "RealEstateAgent",
      "name": "Afriserva Real Estate",
      "url": "https://afriserva.org",
      "logo": "<?= e(url('/assets/images/logo.png')) ?>",
      "description": "Nigeria's premier institutional real estate marketplace and property asset management ecosystem.",
      "parentOrganization": {
        "@type": "Organization",
        "name": "Cloud Current International Limited",
        "url": "https://cloudcurrentng.com"
      },
      "telephone": "+234 1 800 2374",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Heritage Towers, Victoria Island",
        "addressLocality": "Lagos",
        "addressRegion": "Lagos State",
        "addressCountry": "NG"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 6.4281,
        "longitude": 3.4219
      },
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
        "opens": "08:00",
        "closes": "18:00"
      }
    }
    </script>

    <!-- CSS Design System -->
    <link rel="stylesheet" href="<?= asset('css/variables.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    
    <!-- Leaflet Map Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>
<body>

    <!-- Accessible Skip to Content Link (WCAG 2.2 AA) -->
    <a href="#main-content" class="skip-to-content">Skip to main content</a>

    <!-- Header Navigation -->
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= url('/') ?>" class="brand-logo">
                <div class="brand-icon">A</div>
                <div class="brand-text">
                    <span class="brand-name">Afriserva</span>
                    <span class="brand-tag">Real Estate</span>
                </div>
            </a>

            <nav>
                <ul class="nav-menu">
                    <li><a href="<?= url('properties?type=sale') ?>" class="nav-link">Buy</a></li>
                    <li><a href="<?= url('properties?type=rent') ?>" class="nav-link">Rent</a></li>
                    <li><a href="<?= url('properties?type=short_let') ?>" class="nav-link">Short Lets</a></li>
                    <li><a href="<?= url('properties?type=commercial') ?>" class="nav-link">Commercial</a></li>
                    <li><a href="<?= url('properties?type=land') ?>" class="nav-link">Land & Plots</a></li>
                    <li><a href="<?= url('new-developments') ?>" class="nav-link" style="color: var(--color-accent); font-weight: 600;">Developments</a></li>
                    <li><a href="<?= url('investments') ?>" class="nav-link">Invest</a></li>
                    <li><a href="<?= url('property-management') ?>" class="nav-link">Management</a></li>
                    <li><a href="<?= url('agents') ?>" class="nav-link">Advisors</a></li>
                    <li><a href="<?= url('properties/map') ?>" class="nav-link">Map 🗺</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <!-- International Currency Switcher -->
                <?php $curr = \App\Core\Session::get('user_currency', 'NGN'); ?>
                <div style="display: flex; gap: 4px; background: #F1F5F9; padding: 3px; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                    <a href="<?= url('switch-currency?currency=NGN') ?>" style="padding: 3px 8px; font-size: 11px; font-weight: 700; border-radius: 4px; <?= $curr === 'NGN' ? 'background: #0B192C; color: #FFF;' : 'color: #64748B;' ?>">₦ NGN</a>
                    <a href="<?= url('switch-currency?currency=USD') ?>" style="padding: 3px 8px; font-size: 11px; font-weight: 700; border-radius: 4px; <?= $curr === 'USD' ? 'background: #0B192C; color: #FFF;' : 'color: #64748B;' ?>">$ USD</a>
                    <a href="<?= url('switch-currency?currency=GBP') ?>" style="padding: 3px 8px; font-size: 11px; font-weight: 700; border-radius: 4px; <?= $curr === 'GBP' ? 'background: #0B192C; color: #FFF;' : 'color: #64748B;' ?>">£ GBP</a>
                </div>

                <?php if (is_logged_in()): ?>
                    <a href="<?= url('dashboard') ?>" class="btn btn-outline btn-sm">Portal</a>
                    <?php if (has_role('super_admin') || has_role('moderator')): ?>
                        <a href="<?= url('admin') ?>" class="btn btn-accent btn-sm">Admin</a>
                    <?php endif; ?>
                    <a href="<?= url('logout') ?>" class="btn btn-outline btn-sm">Exit</a>
                <?php else: ?>
                    <a href="<?= url('login') ?>" class="btn btn-outline btn-sm">Sign In</a>
                    <a href="<?= url('register?role=agent') ?>" class="btn btn-accent btn-sm">List Property</a>
                <?php endif; ?>

                <!-- Mobile Hamburger Toggle -->
                <button type="button" class="mobile-menu-btn" id="mobile-menu-toggle" aria-label="Toggle navigation">
                    ☰
                </button>
            </div>
        </div>

        <!-- Off-Canvas Mobile Drawer -->
        <div id="mobile-nav-drawer" class="mobile-nav-drawer">
            <div class="mobile-drawer-content">
                <div class="mobile-drawer-header">
                    <div class="brand-logo">
                        <div class="brand-icon">A</div>
                        <div class="brand-text">
                            <span class="brand-name">Afriserva</span>
                            <span class="brand-tag" style="color: var(--color-accent);">Real Estate</span>
                        </div>
                    </div>
                    <button type="button" id="mobile-drawer-close" style="background: none; border: none; font-size: 24px; cursor: pointer;">✕</button>
                </div>

                <ul class="mobile-nav-links">
                    <li><a href="<?= url('properties?type=sale') ?>">Properties for Sale</a></li>
                    <li><a href="<?= url('properties?type=rent') ?>">Residential Rentals</a></li>
                    <li><a href="<?= url('properties?type=short_let') ?>">Executive Short-Lets</a></li>
                    <li><a href="<?= url('properties?type=commercial') ?>">Commercial Offices & Plazas</a></li>
                    <li><a href="<?= url('properties?type=land') ?>">Land, Plots & Layouts</a></li>
                    <li><a href="<?= url('new-developments') ?>">New Developments & Off-Plan</a></li>
                    <li><a href="<?= url('investments') ?>">Investment Syndicates & ROI</a></li>
                    <li><a href="<?= url('property-management') ?>">Property Management Services</a></li>
                    <li><a href="<?= url('agents') ?>">Verified Advisors & Brokers</a></li>
                    <li><a href="<?= url('properties/map') ?>">Interactive Map Explorer 🗺</a></li>
                </ul>

                <div style="border-top: 1px solid var(--color-border); padding-top: 16px; margin-top: auto;">
                    <?php if (is_logged_in()): ?>
                        <a href="<?= url('dashboard') ?>" class="btn btn-primary" style="width: 100%; margin-bottom: 8px;">My Account Dashboard</a>
                        <a href="<?= url('logout') ?>" class="btn btn-outline" style="width: 100%;">Sign Out</a>
                    <?php else: ?>
                        <a href="<?= url('login') ?>" class="btn btn-outline" style="width: 100%; margin-bottom: 8px;">Sign In to Account</a>
                        <a href="<?= url('register?role=agent') ?>" class="btn btn-accent" style="width: 100%;">List Property</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="container" style="margin-top: 20px;">
        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success">✓ <?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('danger')): ?>
            <div class="alert alert-danger">✕ <?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('warning')): ?>
            <div class="alert alert-warning">⚠ <?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('info')): ?>
            <div class="alert alert-info">ℹ <?= e($msg) ?></div>
        <?php endif; ?>
    </div>

    <!-- Main View Content -->
    <main id="main-content" tabindex="-1">
        <?= $content ?>
    </main>

    <!-- Global Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="brand-logo" style="margin-bottom: 16px;">
                        <div class="brand-icon">A</div>
                        <div class="brand-text">
                            <span class="brand-name" style="color: #FFFFFF;">Afriserva</span>
                            <span class="brand-tag">Real Estate</span>
                        </div>
                    </div>
                    <p style="font-size: 14px; line-height: 1.7; color: #94A3B8; margin-bottom: 20px;">
                        Nigeria’s premier institutional real estate marketplace and proptech ecosystem. Operated by <a href="https://cloudcurrentng.com" target="_blank" rel="noopener" style="color: var(--color-accent); font-weight: 600; text-decoration: none;">Cloud Current International Limited</a>. Part of the <a href="https://afriserva.org" target="_blank" rel="noopener" style="color: #FFFFFF; font-weight: 600; text-decoration: none;">Afriserva</a> ecosystem. Connecting verified property owners, licensed brokerages, and prospective investors across Lagos, Abuja, Port Harcourt, and international diaspora markets.
                    </p>
                    <div style="font-size: 13px; color: #CBD5E1; line-height: 1.8;">
                        <p><strong>Main Website:</strong> <a href="https://afriserva.org" target="_blank" rel="noopener" style="color: var(--color-accent); text-decoration: none; font-weight: 600;">afriserva.org</a></p>
                        <p><strong>Corporate Operator:</strong> <a href="https://cloudcurrentng.com" target="_blank" rel="noopener" style="color: #CBD5E1; text-decoration: none;">Cloud Current International Limited (cloudcurrentng.com)</a></p>
                        <p><strong>Hotline:</strong> +234 1 800 2374 &bull; <strong>Support:</strong> support@afriserva.org</p>
                        <p><strong>Corporate HQ:</strong> Heritage Towers, Victoria Island, Lagos, Nigeria</p>
                    </div>
                </div>

                <div>
                    <h4 class="footer-heading">Prime Markets</h4>
                    <ul class="footer-links">
                        <li><a href="<?= url('properties?state=1&neighbourhood=1') ?>">Ikoyi Luxury Real Estate</a></li>
                        <li><a href="<?= url('properties?state=1&neighbourhood=2') ?>">Banana Island Waterfronts</a></li>
                        <li><a href="<?= url('properties?state=1&neighbourhood=4') ?>">Lekki Phase 1 Residences</a></li>
                        <li><a href="<?= url('properties?state=2&neighbourhood=6') ?>">Maitama Diplomatic Zone</a></li>
                        <li><a href="<?= url('properties?state=2&neighbourhood=7') ?>">Asokoro Hilltop Estates</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-heading">Portals & Services</h4>
                    <ul class="footer-links">
                        <li><a href="<?= url('market-insights') ?>">Market Insights & Guides</a></li>
                        <li><a href="<?= url('login') ?>">Agent & Agency Login</a></li>
                        <li><a href="<?= url('register?role=agent') ?>">Become a Verified Agent</a></li>
                        <li><a href="<?= url('register?role=landlord') ?>">Private Landlord Portal</a></li>
                        <li><a href="<?= url('properties/map') ?>">Interactive Map Explorer</a></li>
                        <li><a href="<?= url('dashboard/subscription') ?>">Pricing & Memberships</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-heading">Institutional Trust</h4>
                    <p style="font-size: 13px; color: #94A3B8; margin-bottom: 16px;">
                        All displayed listings undergo rigorous legal documentation validation before public indexation.
                    </p>
                    <div class="badge badge-gold" style="padding: 8px 14px; font-size: 13px; margin-bottom: 12px;">
                        🛡 Verified C of O & Clear Titles
                    </div>
                    <div style="font-size: 12px; color: #94A3B8;">
                        Backed by Cloud Current International Limited institutional governance.
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    &copy; <?= date('Y') ?> <strong>Afriserva Real Estate</strong> &bull; Operated by <strong><a href="https://cloudcurrentng.com" target="_blank" rel="noopener" style="color: inherit; text-decoration: none;">Cloud Current International Limited</a></strong>. All rights reserved.
                </div>
                <div>
                    <a href="https://afriserva.org" target="_blank" rel="noopener" style="color: var(--color-accent); margin-right: 16px; font-weight: 600; text-decoration: none;">afriserva.org</a>
                    <a href="https://cloudcurrentng.com" target="_blank" rel="noopener" style="color: #94A3B8; margin-right: 16px; text-decoration: none;">cloudcurrentng.com</a>
                    <span style="margin-right: 16px;">Terms of Advisory</span>
                    <span style="margin-right: 16px;">Privacy Policy</span>
                    <span>Anti-Fraud Compliance</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Core App JS -->
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
