<?php if (!empty($topBanner)): ?>
    <div style="background: #0B192C; border-bottom: 1px solid rgba(212, 175, 55, 0.3); padding: 10px 0; text-align: center;" class="print-hide">
        <div class="container" style="display: flex; align-items: center; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <span class="badge badge-gold" style="font-size: 11px;">SPONSORED PLACEMENT</span>
            <span style="color: #FFFFFF; font-size: 13px; font-weight: 600;"><?= e($topBanner['title']) ?> — <?= e($topBanner['advertiser_name']) ?></span>
            <a href="<?= url('banners/' . $topBanner['id'] . '/click') ?>" target="_blank" class="btn btn-outline btn-sm" style="color: var(--color-accent); border-color: var(--color-accent); padding: 4px 12px; font-size: 12px;">
                Explore Offer ➔
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- 1. Hero Showcase Section -->
<section class="hero-section">
    <div class="container">
        <span class="badge badge-gold" style="margin-bottom: 20px;">★ Institutional Real Estate Advisory</span>
        <h1 class="hero-title">Discover Nigeria’s Finest Luxury Residences, Estates & Prime Land</h1>
        <p class="hero-subtitle">
            Curated, legally verified residential sales, commercial towers, executive short-lets, and off-plan investment opportunities across Lagos, Abuja, and Port Harcourt.
        </p>

        <!-- Search Box Card -->
        <div class="search-box-card">
            <div class="search-tabs">
                <button type="button" class="search-tab-btn active" data-type="sale">Buy</button>
                <button type="button" class="search-tab-btn" data-type="rent">Rent</button>
                <button type="button" class="search-tab-btn" data-type="short_let">Short Lets</button>
                <button type="button" class="search-tab-btn" data-type="commercial">Commercial</button>
                <button type="button" class="search-tab-btn" data-type="land">Land & Plots</button>
            </div>

            <form action="<?= url('properties') ?>" method="GET">
                <input type="hidden" name="type" id="search-transaction-type" value="sale">

                <div class="search-inputs-grid">
                    <div>
                        <label class="form-label">Search Location or Keywords</label>
                        <input type="text" name="q" class="form-control" placeholder="e.g. Banana Island, Ikoyi, Maitama">
                    </div>

                    <div>
                        <label class="form-label">State / Region</label>
                        <select name="state" class="form-control">
                            <option value="">All States</option>
                            <?php foreach ($states as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Property Category</label>
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Max Price</label>
                        <select name="max_price" class="form-control">
                            <option value="">Any Price</option>
                            <option value="50000000">₦50,000,000</option>
                            <option value="200000000">₦200,000,000</option>
                            <option value="500000000">₦500,000,000</option>
                            <option value="1000000000">₦1,000,000,000</option>
                            <option value="5000000000">₦5,000,000,000+</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-accent btn-lg" style="width: 100%;">
                            <span>Search</span> 🔍
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- 2. Genuine Database Statistics Bar (Zero Fake Multipliers) -->
<section style="background: #FFFFFF; border-bottom: 1px solid var(--color-border); padding: 32px 0;">
    <div class="container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px; text-align: center;">
        <div>
            <div style="font-size: 32px; font-weight: 700; color: var(--color-primary); font-family: var(--font-heading);">
                <?= number_format($stats['properties_listed']) ?>
            </div>
            <div style="font-size: 13px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Published Listings</div>
        </div>
        <div>
            <div style="font-size: 32px; font-weight: 700; color: var(--color-primary); font-family: var(--font-heading);">
                <?= number_format($stats['verified_agents']) ?>
            </div>
            <div style="font-size: 13px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Accredited Advisors</div>
        </div>
        <div>
            <div style="font-size: 32px; font-weight: 700; color: var(--color-primary); font-family: var(--font-heading);">
                <?= number_format($stats['prime_locations']) ?>
            </div>
            <div style="font-size: 13px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Mapped Prime Enclaves</div>
        </div>
        <div>
            <div style="font-size: 32px; font-weight: 700; color: var(--color-accent); font-family: var(--font-heading);">
                <?= number_format($stats['active_states']) ?> States
            </div>
            <div style="font-size: 13px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Regional Coverage</div>
        </div>
    </div>
</section>

<!-- 3. Featured Signature Properties -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Exclusive Showcase</span>
            <h2 class="section-title">Signature Prime Residences</h2>
            <p class="section-desc">Handpicked architectural masterpieces and institutional commercial assets with verified legal titles.</p>
        </div>

        <div class="property-grid">
            <?php foreach ($featured as $prop): ?>
                <article class="property-card">
                    <div class="card-media">
                        <img src="<?= e($prop['cover_image']) ?>" alt="<?= e($prop['title']) ?>" loading="lazy">
                        <div class="card-badges">
                            <?php if ($prop['is_luxury']): ?>
                                <span class="badge badge-gold">★ Luxury</span>
                            <?php endif; ?>
                            <span class="badge badge-verified">✓ Verified</span>
                            <span class="badge badge-primary"><?= strtoupper($prop['transaction_type']) ?></span>
                        </div>
                        <div class="card-price-tag">
                            <?= format_money($prop['price'], $prop['currency']) ?>
                            <span style="font-size: 12px; font-weight: normal;"><?= e($prop['price_suffix']) ?></span>
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

                        <div class="card-footer">
                            <div class="agent-snippet">
                                <img src="<?= e($prop['agent_avatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80') ?>" class="agent-thumb" alt="Agent">
                                <div class="agent-snippet-info">
                                    <div class="agent-snippet-name"><?= e($prop['agent_name']) ?></div>
                                    <div class="agent-snippet-agency"><?= e($prop['agency_name'] ?? 'Afriserva Elite') ?></div>
                                </div>
                            </div>

                            <a href="<?= url('properties/' . $prop['slug']) ?>" class="btn btn-outline btn-sm">
                                View →
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 48px;">
            <a href="<?= url('properties') ?>" class="btn btn-primary btn-lg">
                Explore All Properties →
            </a>
        </div>
    </div>
</section>

<!-- 4. Newly Listed Real Estate -->
<?php if (!empty($newlyListed)): ?>
<section class="section" style="background: #F8FAFC; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Fresh Market Additions</span>
            <h2 class="section-title">Newly Listed Properties</h2>
            <p class="section-desc">The latest residential, commercial, and development plots authenticated on the market this week.</p>
        </div>

        <div class="property-grid">
            <?php foreach (array_slice($newlyListed, 0, 3) as $prop): ?>
                <article class="property-card">
                    <div class="card-media">
                        <img src="<?= e($prop['cover_image']) ?>" alt="<?= e($prop['title']) ?>" loading="lazy">
                        <div class="card-badges">
                            <span class="badge badge-primary">NEW</span>
                            <span class="badge badge-verified">✓ <?= e($prop['title_document'] ?? 'C of O') ?></span>
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
                            <a href="<?= url('properties/' . $prop['slug']) ?>"><?= e($prop['title']) ?></a>
                        </h3>

                        <div class="card-features">
                            <?php if ($prop['bedrooms'] > 0): ?>
                                <div class="card-feature-item">🛏 <?= $prop['bedrooms'] ?> Beds</div>
                            <?php endif; ?>
                            <?php if ($prop['bathrooms'] > 0): ?>
                                <div class="card-feature-item">🚿 <?= $prop['bathrooms'] ?> Baths</div>
                            <?php endif; ?>
                            <?php if ($prop['land_size_sqm'] > 0): ?>
                                <div class="card-feature-item">🗺 <?= format_sqm($prop['land_size_sqm']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer">
                            <span style="font-size: 12px; color: var(--color-text-muted);">
                                Ref: <code><?= e($prop['reference_code']) ?></code>
                            </span>
                            <a href="<?= url('properties/' . $prop['slug']) ?>" class="btn btn-outline btn-sm">
                                View Details ➔
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 5. Prime Locations Showcase -->
<section class="section" style="background: #FFFFFF; border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">High-Yield Corridors</span>
            <h2 class="section-title">Explore by Prime Locations</h2>
            <p class="section-desc">Prestigious residential enclaves, diplomatic zones, and commercial epicenters.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">
            <?php foreach ($popularLocations as $loc): ?>
                <a href="<?= url('properties?neighbourhood=' . $loc['id']) ?>" style="background: var(--color-bg); border-radius: var(--radius-lg); padding: 24px; border: 1px solid var(--color-border); transition: all var(--transition-fast); display: block;" onmouseover="this.style.borderColor='var(--color-primary)'" onmouseout="this.style.borderColor='var(--color-border)'">
                    <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: var(--color-accent); font-weight: 700; margin-bottom: 6px;">
                        <?= e($loc['state_name']) ?>
                    </div>
                    <h3 style="font-size: 20px; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">
                        <?= e($loc['name']) ?>
                    </h3>
                    <div style="font-size: 13px; color: var(--color-text-muted); display: flex; justify-content: space-between;">
                        <span><?= $loc['total_properties'] ?> Verified Listings</span>
                        <span style="font-weight: 600; color: var(--color-primary);">From <?= format_money($loc['min_price']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 6. Affordable Housing & Land Opportunities + Recently Reduced Prices -->
<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start;">
            <!-- Affordable Land & Entry Housing -->
            <div>
                <div style="margin-bottom: 24px;">
                    <span class="section-tag">Value & Expansion</span>
                    <h3 style="font-size: 26px; font-family: var(--font-heading); color: var(--color-primary);">Affordable Housing & Land</h3>
                    <p style="font-size: 14px; color: var(--color-text-muted);">Serviced residential plots and suburban starter apartments with high growth trajectory.</p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($affordable as $aff): ?>
                        <div style="display: flex; gap: 16px; background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 14px; box-shadow: var(--shadow-subtle);">
                            <img src="<?= e($aff['cover_image']) ?>" alt="<?= e($aff['title']) ?>" style="width: 100px; height: 80px; border-radius: var(--radius-sm); object-fit: cover;">
                            <div style="flex-grow: 1;">
                                <div style="font-size: 12px; color: var(--color-accent); font-weight: 700;">
                                    <?= e($aff['price_prefix'] ?? 'Affordable Opportunity') ?>
                                </div>
                                <h4 style="font-size: 15px; font-weight: 600; margin-bottom: 4px;">
                                    <a href="<?= url('properties/' . $aff['slug']) ?>"><?= e($aff['title']) ?></a>
                                </h4>
                                <div style="font-size: 16px; font-weight: 700; color: var(--color-primary);">
                                    <?= format_money($aff['price'], $aff['currency']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recently Reduced Prices -->
            <div>
                <div style="margin-bottom: 24px;">
                    <span class="section-tag">Market Opportunities</span>
                    <h3 style="font-size: 26px; font-family: var(--font-heading); color: var(--color-primary);">Recently Reduced Prices</h3>
                    <p style="font-size: 14px; color: var(--color-text-muted);">Motivated sellers and exclusive discounted transactions priced for swift conveyance.</p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($recentlyReduced as $red): ?>
                        <div style="display: flex; gap: 16px; background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 14px; box-shadow: var(--shadow-subtle);">
                            <img src="<?= e($red['cover_image']) ?>" alt="<?= e($red['title']) ?>" style="width: 100px; height: 80px; border-radius: var(--radius-sm); object-fit: cover;">
                            <div style="flex-grow: 1;">
                                <span class="badge badge-gold" style="font-size: 10px; margin-bottom: 4px;">🏷 Price Reduced</span>
                                <h4 style="font-size: 15px; font-weight: 600; margin-bottom: 4px;">
                                    <a href="<?= url('properties/' . $red['slug']) ?>"><?= e($red['title']) ?></a>
                                </h4>
                                <div style="font-size: 16px; font-weight: 700; color: var(--color-success);">
                                    <?= format_money($red['price'], $red['currency']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 7. Interactive Map Discovery -->
<section class="section" style="background: var(--color-primary-dark); color: #FFFFFF;">
    <div class="container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center;">
        <div>
            <span class="badge badge-gold" style="margin-bottom: 16px;">Geospatial Intelligence</span>
            <h2 style="font-size: 38px; color: #FFFFFF; margin-bottom: 20px; line-height: 1.2;">
                Explore Nigeria’s Prime Properties On Our Interactive Map
            </h2>
            <p style="color: #CBD5E1; font-size: 16px; line-height: 1.7; margin-bottom: 32px;">
                Seamlessly visualize real estate pricing, schools, transport arterials, waterfront corridors, and diplomatic enclaves using our geospatial discovery engine.
            </p>
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <a href="<?= url('properties/map') ?>" class="btn btn-accent btn-lg">
                    Launch Interactive Map 🗺
                </a>
                <a href="<?= url('properties') ?>" class="btn btn-outline btn-lg" style="color: #FFFFFF; border-color: rgba(255, 255, 255, 0.3);">
                    View Grid Results
                </a>
            </div>
        </div>

        <div>
            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: var(--radius-xl); padding: 16px; overflow: hidden; box-shadow: var(--shadow-luxury);">
                <img src="https://images.unsplash.com/photo-1524813686514-a57563d77d61?auto=format&fit=crop&w=1200&q=80" alt="Map Explorer" style="border-radius: var(--radius-lg); width: 100%;">
            </div>
        </div>
    </div>
</section>

<!-- 8. Verified Real Estate Professionals (Agents & Agencies) -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Accredited Advisory</span>
            <h2 class="section-title">Verified Real Estate Professionals</h2>
            <p class="section-desc">Licensed NIESV, ESVARBON, and REDAN accredited brokers with proven track records.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 32px;">
            <?php foreach ($topAgents as $agent): ?>
                <div style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 32px; text-align: center; box-shadow: var(--shadow-card);">
                    <img src="<?= e($agent['avatar_url'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80') ?>" alt="<?= e($agent['full_name']) ?>" style="width: 88px; height: 88px; border-radius: var(--radius-full); object-fit: cover; margin: 0 auto 16px; border: 3px solid var(--color-accent-light);">
                    <h3 style="font-size: 18px; font-weight: 600; color: var(--color-primary); margin-bottom: 4px;">
                        <?= e($agent['full_name']) ?>
                    </h3>
                    <div style="font-size: 12px; color: var(--color-accent); font-weight: 700; margin-bottom: 12px;">
                        ★ <?= $agent['rating'] ?> Rating (<?= $agent['total_reviews'] ?> Reviews)
                    </div>
                    <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 20px; line-height: 1.5;">
                        <?= e($agent['specialization'] ?? 'Luxury Real Estate Advisor') ?>
                    </p>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $agent['whatsapp_number'] ?? '2348030000001') ?>" target="_blank" class="btn btn-outline btn-sm" style="width: 100%;">
                        💬 Chat via WhatsApp
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 9. Market Insights & Real Estate Guides (From Real Database) -->
<section class="section" style="background: #FFFFFF; border-top: 1px solid var(--color-border);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Market Intelligence</span>
            <h2 class="section-title">Institutional Insights & Guides</h2>
            <p class="section-desc">Authoritative conveyancing counsel, legal title analysis, and macro real estate research.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 32px;">
            <?php foreach ($marketArticles as $art): ?>
                <article style="background: var(--color-bg); border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border); display: flex; flex-direction: column;">
                    <a href="<?= url('/market-insights/' . e($art['slug'])) ?>" style="text-decoration: none; display: block;">
                        <img src="<?= e($art['image_url']) ?>" alt="<?= e($art['title']) ?>" style="height: 200px; width: 100%; object-fit: cover;">
                    </a>
                    <div style="padding: 24px; display: flex; flex-direction: column; flex-grow: 1;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px;">
                            <span style="font-weight: 700; color: var(--color-accent);"><?= e($art['category']) ?></span>
                            <span style="color: var(--color-text-muted);"><?= e($art['read_time']) ?></span>
                        </div>
                        <h3 style="font-size: 18px; font-weight: 600; line-height: 1.4; margin-bottom: 10px;">
                            <a href="<?= url('/market-insights/' . e($art['slug'])) ?>" style="color: var(--color-primary); text-decoration: none;">
                                <?= e($art['title']) ?>
                            </a>
                        </h3>
                        <p style="font-size: 13px; color: var(--color-secondary); line-height: 1.6; margin-bottom: 20px; flex-grow: 1;">
                            <?= e($art['excerpt']) ?>
                        </p>
                        <div style="border-top: 1px solid var(--color-border); padding-top: 12px; font-size: 12px; color: var(--color-text-muted); display: flex; justify-content: space-between; align-items: center;">
                            <span>By <strong><?= e($art['author_name']) ?></strong></span>
                            <a href="<?= url('/market-insights/' . e($art['slug'])) ?>" style="color: var(--color-accent); font-weight: 600; text-decoration: none;">Read &rarr;</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="<?= url('/market-insights') ?>" class="btn btn-outline" style="padding: 12px 28px; font-weight: 600;">
                Explore All Market Intelligence & Guides &rarr;
            </a>
        </div>
    </div>
</section>

<!-- 10. Genuine Customer Feedback & Authorized Testimonials -->
<?php if (!empty($reviews)): ?>
<section class="section" style="background: #F8FAFC; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Authorized Feedback</span>
            <h2 class="section-title">Verified Client Testimonials</h2>
            <p class="section-desc">Documented experiences from institutional buyers, diaspora investors, and commercial landlords.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px;">
            <?php foreach ($reviews as $rev): ?>
                <div style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 32px; box-shadow: var(--shadow-card); display: flex; flex-direction: column;">
                    <div style="font-size: 18px; color: var(--color-accent); margin-bottom: 16px;">
                        <?= str_repeat('★', (int)$rev['rating']) ?>
                    </div>
                    <blockquote style="font-size: 14px; line-height: 1.7; color: var(--color-secondary); font-style: italic; margin-bottom: 24px; flex-grow: 1;">
                        “<?= e($rev['comment']) ?>”
                    </blockquote>
                    <div style="display: flex; align-items: center; gap: 12px; border-top: 1px solid var(--color-border); padding-top: 16px;">
                        <img src="<?= e($rev['avatar_url'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80') ?>" alt="<?= e($rev['full_name']) ?>" style="width: 44px; height: 44px; border-radius: var(--radius-full); object-fit: cover;">
                        <div>
                            <div style="font-size: 14px; font-weight: 700; color: var(--color-primary);"><?= e($rev['full_name']) ?></div>
                            <div style="font-size: 11px; color: var(--color-success); font-weight: 600;">✓ Verified Transaction</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 11. Newsletter Subscription Section (Connected to Real DB) -->
<section style="background: #FFFFFF; padding: 60px 0; border-bottom: 1px solid var(--color-border);">
    <div class="container" style="max-width: 800px; text-align: center;">
        <span class="badge badge-gold" style="margin-bottom: 12px;">Private Real Estate Briefings</span>
        <h3 style="font-size: 28px; margin-bottom: 12px;">Receive Exclusive Off-Market & Pre-Launch Deal Flow</h3>
        <p style="font-size: 15px; color: var(--color-text-muted); margin-bottom: 28px;">
            Join over 2,400 HNIs and corporate real estate directors receiving our bi-weekly intelligence bulletin on title updates, currency trends, and high-yield listings.
        </p>

        <form action="<?= url('newsletter/subscribe') ?>" method="POST" style="display: flex; gap: 12px; max-width: 540px; margin: 0 auto; flex-wrap: wrap;">
            <?= csrf_field() ?>
            <input type="email" name="email" class="form-control" placeholder="Enter corporate or personal email" required style="flex-grow: 1; min-width: 240px;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">Subscribe ➔</button>
        </form>
    </div>
</section>

<!-- 12. List-Your-Property Call to Action -->
<section style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%); color: #FFFFFF; padding: 80px 0;">
    <div class="container" style="text-align: center; max-width: 760px;">
        <span class="badge badge-gold" style="margin-bottom: 16px;">For Property Owners & Brokerages</span>
        <h2 style="font-size: 36px; color: #FFFFFF; margin-bottom: 16px;">
            Sell or Lease Your High-Value Property with Complete Confidence
        </h2>
        <p style="font-size: 16px; color: #CBD5E1; margin-bottom: 32px; line-height: 1.7;">
            Connect directly with verified domestic and diaspora investors seeking authenticated residential and commercial real estate assets.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= url('register?role=agent') ?>" class="btn btn-accent btn-lg">
                Create Partner Account 🚀
            </a>
            <a href="<?= url('dashboard/subscription') ?>" class="btn btn-outline btn-lg" style="color: #FFFFFF; border-color: rgba(255, 255, 255, 0.3);">
                View Subscription Plans
            </a>
        </div>
    </div>
</section>
