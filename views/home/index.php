<?php if (!empty($topBanner)): ?>
    <div style="background: #03080F; border-bottom: 1px solid rgba(197, 160, 89, 0.35); padding: 12px 0; text-align: center;" class="print-hide">
        <div class="container" style="display: flex; align-items: center; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <span class="badge badge-gold" style="font-size: 11px; padding: 4px 10px;">★ SPONSORED SHOWCASE</span>
            <span style="color: #FFFFFF; font-size: 13.5px; font-weight: 600; letter-spacing: 0.2px;">
                <?= e($topBanner['title']) ?> — <span style="color: var(--color-gold-bright);"><?= e($topBanner['advertiser_name']) ?></span>
            </span>
            <a href="<?= url('banners/' . $topBanner['id'] . '/click') ?>" target="_blank" class="btn btn-accent btn-sm" style="padding: 4px 14px; font-size: 12px;">
                Explore Asset ➔
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- 1. Sovereign Hero Showcase Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-eyebrow-pill">
            <span>🛡</span> 100% STATUTORY TITLE VERIFIED &bull; INSTITUTIONAL ADVISORY
        </div>
        
        <h1 class="hero-title">
            Discover Nigeria’s Finest <span class="gold-gradient-text">Luxury Residences, Waterfront Enclaves & Verified Land</span>
        </h1>
        
        <p class="hero-subtitle">
            Curated, legally authenticated residential acquisitions, commercial towers, executive short-lets, and off-plan investment portfolios across Lagos, Abuja, and Port Harcourt.
        </p>

        <!-- Glassmorphic Search Terminal -->
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
                        <label class="form-label">Search Enclave, Corridor, or Keyword</label>
                        <input type="text" name="q" class="form-control" placeholder="e.g. Banana Island, Ikoyi, Maitama, Eko Atlantic">
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
                        <label class="form-label">Max Price Budget</label>
                        <select name="max_price" class="form-control">
                            <option value="">Any Budget</option>
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

        <!-- Curated Discovery Pills -->
        <div class="hero-tags">
            <span style="font-weight: 600; color: #E2E8F0;">Trending Curations:</span>
            <a href="<?= url('properties?q=Banana+Island') ?>" class="hero-tag-link">Banana Island Waterfront</a>
            <a href="<?= url('properties?q=Eko+Atlantic') ?>" class="hero-tag-link">Eko Atlantic Sky Residences</a>
            <a href="<?= url('properties?q=Maitama') ?>" class="hero-tag-link">Maitama Diplomatic Zone</a>
            <a href="<?= url('properties?q=Ikoyi') ?>" class="hero-tag-link">Ikoyi Penthouses</a>
            <a href="<?= url('properties?type=land') ?>" class="hero-tag-link">Verified Plots with C of O</a>
        </div>
    </div>
</section>

<!-- 2. Genuine Database Statistics Bar -->
<section class="metrics-bar">
    <div class="container">
        <div class="metrics-grid">
            <div>
                <div class="metric-value"><?= number_format($stats['properties_listed']) ?></div>
                <div class="metric-label">Published Prime Listings</div>
            </div>
            <div>
                <div class="metric-value"><?= number_format($stats['verified_agents']) ?></div>
                <div class="metric-label">Accredited Brokers & Agencies</div>
            </div>
            <div>
                <div class="metric-value"><?= number_format($stats['prime_locations']) ?></div>
                <div class="metric-label">Mapped Prime Enclaves</div>
            </div>
            <div>
                <div class="metric-value" style="color: var(--color-accent);"><?= number_format($stats['active_states']) ?> States</div>
                <div class="metric-label">Sovereign Territory Coverage</div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Why Afriserva: The Sovereign Gold Standard -->
<section class="section" style="background: #FFFFFF;">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Institutional Governance</span>
            <h2 class="section-title">The Afriserva Assurance</h2>
            <p class="section-desc">Pioneering security, conveyancing integrity, and frictionless asset acquisition for domestic and global investors.</p>
        </div>

        <div class="trust-grid">
            <div class="trust-card">
                <div class="trust-icon-box">🏛</div>
                <h3 class="trust-card-title">100% Land Registry Title Verification</h3>
                <p class="trust-card-desc">
                    Every asset undergoes statutory cross-examination against Lands Bureau archives (Alausa, AGIS, and Survey Registries). Zero land-grabbers, zero defective titles.
                </p>
            </div>

            <div class="trust-card">
                <div class="trust-icon-box">🔒</div>
                <h3 class="trust-card-title">Regulated Escrow & Milestone Security</h3>
                <p class="trust-card-desc">
                    Financial transactions are protected in licensed escrow vaults and disbursed strictly upon verified milestone conveyance and governor's consent endorsement.
                </p>
            </div>

            <div class="trust-card">
                <div class="trust-icon-box">🚗</div>
                <h3 class="trust-card-title">Private Wealth Desk & Chauffeured Tours</h3>
                <p class="trust-card-desc">
                    Discreet private inspections, accompanied walkthroughs with dedicated conveyancing attorneys, and virtual 3D tour options for remote decision makers.
                </p>
            </div>

            <div class="trust-card">
                <div class="trust-icon-box">🌐</div>
                <h3 class="trust-card-title">Global Diaspora Investment Desk</h3>
                <p class="trust-card-desc">
                    Seamless multi-currency settlement (GBP, USD, EUR, NGN) with automated NDPR-compliant data rooms and digital lease execution across 40+ countries.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 4. Featured Signature Properties -->
<section class="section" style="background: var(--color-bg); border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Curated Portfolio</span>
            <h2 class="section-title">Signature Prime Residences</h2>
            <p class="section-desc">Handpicked architectural masterworks and institutional commercial assets with verified legal titles.</p>
        </div>

        <div class="property-grid">
            <?php foreach ($featured as $prop): ?>
                <article class="property-card">
                    <div class="card-media">
                        <img src="<?= e($prop['cover_image']) ?>" alt="<?= e($prop['title']) ?>" loading="lazy">
                        <div class="card-badges">
                            <?php if ($prop['is_luxury']): ?>
                                <span class="badge badge-luxury">★ Luxury Enclave</span>
                            <?php endif; ?>
                            <span class="badge badge-verified">✓ Statutory Verified</span>
                            <span class="badge badge-primary"><?= strtoupper($prop['transaction_type']) ?></span>
                        </div>
                        <div class="card-price-tag">
                            <?= format_money($prop['price'], $prop['currency']) ?>
                            <?php if (!empty($prop['price_suffix'])): ?>
                                <span style="font-size: 12px; font-weight: normal; color: #CBD5E1;"><?= e($prop['price_suffix']) ?></span>
                            <?php endif; ?>
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
                                    <div class="agent-snippet-agency"><?= e($prop['agency_name'] ?? 'Afriserva Verified') ?></div>
                                </div>
                            </div>

                            <a href="<?= url('properties/' . $prop['slug']) ?>" class="btn btn-outline btn-sm">
                                Explore Asset ➔
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 52px;">
            <a href="<?= url('properties') ?>" class="btn btn-primary btn-lg">
                Explore Complete Inventory (<?= number_format($stats['properties_listed']) ?> Assets) →
            </a>
        </div>
    </div>
</section>

<!-- 5. Prime Locations / High-Yield Enclaves Showcase -->
<section class="section" style="background: #FFFFFF;">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">High-Yield Geographical Hubs</span>
            <h2 class="section-title">Explore Sovereign Enclaves</h2>
            <p class="section-desc">Prestigious residential enclaves, diplomatic quarters, and high-velocity commercial corridors.</p>
        </div>

        <div class="enclaves-grid">
            <a href="<?= url('properties?q=Banana+Island') ?>" class="enclave-card">
                <img src="https://images.unsplash.com/photo-1613977257363-707ba9348227?auto=format&fit=crop&w=800&q=80" alt="Banana Island, Ikoyi">
                <div class="enclave-content">
                    <div class="enclave-state">Lagos State</div>
                    <h3 class="enclave-title">Banana Island & Ikoyi</h3>
                    <div class="enclave-meta">
                        <span>Waterfront Mansions & Marinas</span>
                        <span style="font-weight: 700; color: var(--color-gold-bright);">Prime Luxury ➔</span>
                    </div>
                </div>
            </a>

            <a href="<?= url('properties?q=Maitama') ?>" class="enclave-card">
                <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80" alt="Maitama, Abuja">
                <div class="enclave-content">
                    <div class="enclave-state">Abuja FCT</div>
                    <h3 class="enclave-title">Maitama & Asokoro</h3>
                    <div class="enclave-meta">
                        <span>Diplomatic Enclaves & Hillsides</span>
                        <span style="font-weight: 700; color: var(--color-gold-bright);">High Security ➔</span>
                    </div>
                </div>
            </a>

            <a href="<?= url('properties?q=Eko+Atlantic') ?>" class="enclave-card">
                <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80" alt="Eko Atlantic City">
                <div class="enclave-content">
                    <div class="enclave-state">Victoria Island, Lagos</div>
                    <h3 class="enclave-title">Eko Atlantic City</h3>
                    <div class="enclave-meta">
                        <span>Oceanfront Coastal Sky Towers</span>
                        <span style="font-weight: 700; color: var(--color-gold-bright);">Future Ready ➔</span>
                    </div>
                </div>
            </a>

            <a href="<?= url('properties?q=Lekki') ?>" class="enclave-card">
                <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80" alt="Lekki Phase 1">
                <div class="enclave-content">
                    <div class="enclave-state">Lagos State</div>
                    <h3 class="enclave-title">Lekki Phase 1 & VI</h3>
                    <div class="enclave-meta">
                        <span>Commercial Hubs & Penthouses</span>
                        <span style="font-weight: 700; color: var(--color-gold-bright);">High Yield ➔</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- 6. Newly Listed Real Estate -->
<?php if (!empty($newlyListed)): ?>
<section class="section" style="background: var(--color-bg); border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
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

<!-- 7. Affordable Housing & Land Opportunities + Recently Reduced Prices -->
<section class="section" style="background: #FFFFFF;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 40px; align-items: start;">
            <!-- Affordable Land & Entry Housing -->
            <div>
                <div style="margin-bottom: 24px;">
                    <span class="section-tag">Value & Expansion</span>
                    <h3 style="font-size: 26px; font-family: var(--font-heading); color: var(--color-primary);">Affordable Housing & Land</h3>
                    <p style="font-size: 14px; color: var(--color-text-muted);">Serviced residential plots and suburban starter apartments with high growth trajectory.</p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($affordable as $aff): ?>
                        <div style="display: flex; gap: 16px; background: var(--color-bg); border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 14px; box-shadow: var(--shadow-subtle); transition: all var(--transition-fast);" onmouseover="this.style.borderColor='var(--color-accent)'" onmouseout="this.style.borderColor='var(--color-border)'">
                            <img src="<?= e($aff['cover_image']) ?>" alt="<?= e($aff['title']) ?>" style="width: 110px; height: 85px; border-radius: var(--radius-sm); object-fit: cover;">
                            <div style="flex-grow: 1;">
                                <div style="font-size: 11.5px; color: var(--color-accent); font-weight: 700; text-transform: uppercase;">
                                    <?= e($aff['price_prefix'] ?? 'Affordable Entry') ?>
                                </div>
                                <h4 style="font-size: 15px; font-weight: 700; margin: 3px 0;">
                                    <a href="<?= url('properties/' . $aff['slug']) ?>"><?= e($aff['title']) ?></a>
                                </h4>
                                <div style="font-size: 16px; font-weight: 800; color: var(--color-primary);">
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
                        <div style="display: flex; gap: 16px; background: var(--color-bg); border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 14px; box-shadow: var(--shadow-subtle); transition: all var(--transition-fast);" onmouseover="this.style.borderColor='var(--color-accent)'" onmouseout="this.style.borderColor='var(--color-border)'">
                            <img src="<?= e($red['cover_image']) ?>" alt="<?= e($red['title']) ?>" style="width: 110px; height: 85px; border-radius: var(--radius-sm); object-fit: cover;">
                            <div style="flex-grow: 1;">
                                <span class="badge badge-gold" style="font-size: 10px; margin-bottom: 4px;">🏷 Price Reduced</span>
                                <h4 style="font-size: 15px; font-weight: 700; margin: 3px 0;">
                                    <a href="<?= url('properties/' . $red['slug']) ?>"><?= e($red['title']) ?></a>
                                </h4>
                                <div style="font-size: 16px; font-weight: 800; color: var(--color-success);">
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

<!-- 8. Interactive Geospatial Discovery Engine -->
<section class="section" style="background: linear-gradient(135deg, #03080F 0%, #0A192F 100%); color: #FFFFFF; position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(197, 160, 89, 0.15) 0%, transparent 70%); pointer-events: none;"></div>
    
    <div class="container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: center; position: relative; z-index: 2;">
        <div>
            <span class="badge badge-gold" style="margin-bottom: 18px;">Geospatial Intelligence Engine</span>
            <h2 style="font-size: 42px; color: #FFFFFF; margin-bottom: 22px; line-height: 1.2; font-family: var(--font-heading);">
                Explore Nigeria’s Prime Enclaves on our Geospatial Map
            </h2>
            <p style="color: #CBD5E1; font-size: 16px; line-height: 1.75; margin-bottom: 36px;">
                Seamlessly visualize real estate pricing, schools, transport arterials, waterfront corridors, and diplomatic enclaves using our interactive geospatial discovery engine.
            </p>
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <a href="<?= url('properties/map') ?>" class="btn btn-accent btn-lg">
                    Launch Interactive Map 🗺
                </a>
                <a href="<?= url('properties') ?>" class="btn btn-outline btn-lg" style="color: #FFFFFF; border-color: rgba(255, 255, 255, 0.35);">
                    Browse Grid Inventory
                </a>
            </div>
        </div>

        <div>
            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(197, 160, 89, 0.3); border-radius: var(--radius-xl); padding: 18px; overflow: hidden; box-shadow: var(--shadow-luxury); backdrop-filter: blur(12px);">
                <img src="https://images.unsplash.com/photo-1524813686514-a57563d77d61?auto=format&fit=crop&w=1200&q=80" alt="Map Explorer" style="border-radius: var(--radius-lg); width: 100%; height: 320px; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

<!-- 9. Verified Real Estate Professionals (Agents & Agencies) -->
<section class="section" style="background: #FFFFFF;">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Accredited Advisory</span>
            <h2 class="section-title">Verified Real Estate Professionals</h2>
            <p class="section-desc">Licensed NIESV, ESVARBON, and REDAN accredited brokers with proven track records.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 32px;">
            <?php foreach ($topAgents as $agent): ?>
                <div style="background: var(--color-bg); border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 32px 24px; text-align: center; box-shadow: var(--shadow-card); transition: all var(--transition-base);" onmouseover="this.style.transform='translateY(-6px)'; this.style.borderColor='var(--color-border-gold)';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--color-border)';">
                    <img src="<?= e($agent['avatar_url'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80') ?>" alt="<?= e($agent['full_name']) ?>" style="width: 90px; height: 90px; border-radius: var(--radius-full); object-fit: cover; margin: 0 auto 16px; border: 3px solid var(--color-accent); box-shadow: 0 4px 14px rgba(197, 160, 89, 0.3);">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--color-primary); margin-bottom: 4px;">
                        <?= e($agent['full_name']) ?>
                    </h3>
                    <div style="font-size: 12px; color: var(--color-accent); font-weight: 700; margin-bottom: 12px;">
                        ★ <?= $agent['rating'] ?> Rating (<?= $agent['total_reviews'] ?> Reviews)
                    </div>
                    <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 22px; line-height: 1.5;">
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

<!-- 10. Market Insights & Institutional Guides -->
<?php if (!empty($marketArticles)): ?>
<section class="section" style="background: var(--color-bg); border-top: 1px solid var(--color-border);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Market Intelligence</span>
            <h2 class="section-title">Institutional Insights & Guides</h2>
            <p class="section-desc">Authoritative conveyancing counsel, legal title analysis, and macro real estate research.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 32px;">
            <?php foreach ($marketArticles as $art): ?>
                <article style="background: #FFFFFF; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border); display: flex; flex-direction: column; box-shadow: var(--shadow-card); transition: all var(--transition-base);" onmouseover="this.style.transform='translateY(-6px)'; this.style.borderColor='var(--color-border-gold)';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--color-border)';">
                    <a href="<?= url('/market-insights/' . e($art['slug'])) ?>" style="text-decoration: none; display: block; overflow: hidden; height: 210px;">
                        <img src="<?= e($art['image_url']) ?>" alt="<?= e($art['title']) ?>" style="height: 100%; width: 100%; object-fit: cover; transition: transform 0.5s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    </a>
                    <div style="padding: 24px; display: flex; flex-direction: column; flex-grow: 1;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px;">
                            <span style="font-weight: 700; color: var(--color-accent);"><?= e($art['category']) ?></span>
                            <span style="color: var(--color-text-muted);"><?= e($art['read_time']) ?></span>
                        </div>
                        <h3 style="font-size: 18px; font-weight: 700; line-height: 1.4; margin-bottom: 10px;">
                            <a href="<?= url('/market-insights/' . e($art['slug'])) ?>" style="color: var(--color-primary); text-decoration: none;">
                                <?= e($art['title']) ?>
                            </a>
                        </h3>
                        <p style="font-size: 13.5px; color: var(--color-secondary); line-height: 1.6; margin-bottom: 20px; flex-grow: 1;">
                            <?= e($art['excerpt']) ?>
                        </p>
                        <div style="border-top: 1px solid var(--color-border); padding-top: 14px; font-size: 12px; color: var(--color-text-muted); display: flex; justify-content: space-between; align-items: center;">
                            <span>By <strong><?= e($art['author_name']) ?></strong></span>
                            <a href="<?= url('/market-insights/' . e($art['slug'])) ?>" style="color: var(--color-accent); font-weight: 700; text-decoration: none;">Read Counsel &rarr;</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 48px;">
            <a href="<?= url('/market-insights') ?>" class="btn btn-outline" style="padding: 12px 28px; font-weight: 600;">
                Explore All Market Intelligence & Guides &rarr;
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 11. Authorized Client Testimonials -->
<?php if (!empty($reviews)): ?>
<section class="section" style="background: #FFFFFF; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Documented Experience</span>
            <h2 class="section-title">Verified Client Testimonials</h2>
            <p class="section-desc">Experiences documented by institutional buyers, diaspora investors, and commercial landlords.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px;">
            <?php foreach ($reviews as $rev): ?>
                <div style="background: var(--color-bg); border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 36px 30px; box-shadow: var(--shadow-card); display: flex; flex-direction: column;">
                    <div style="font-size: 18px; color: var(--color-accent); margin-bottom: 16px;">
                        <?= str_repeat('★', (int)$rev['rating']) ?>
                    </div>
                    <blockquote style="font-size: 14.5px; line-height: 1.75; color: var(--color-secondary); font-style: italic; margin-bottom: 24px; flex-grow: 1;">
                        “<?= e($rev['comment']) ?>”
                    </blockquote>
                    <div style="display: flex; align-items: center; gap: 14px; border-top: 1px solid var(--color-border); padding-top: 18px;">
                        <img src="<?= e($rev['avatar_url'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80') ?>" alt="<?= e($rev['full_name']) ?>" style="width: 48px; height: 48px; border-radius: var(--radius-full); object-fit: cover; border: 2px solid var(--color-border-gold);">
                        <div>
                            <div style="font-size: 14.5px; font-weight: 700; color: var(--color-primary);"><?= e($rev['full_name']) ?></div>
                            <div style="font-size: 11.5px; color: var(--color-success); font-weight: 600;">✓ Verified Transaction</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 12. Off-Market Deal Flow Newsletter -->
<section style="background: #FFFFFF; padding: 70px 0; border-bottom: 1px solid var(--color-border);">
    <div class="container" style="max-width: 820px; text-align: center;">
        <span class="badge badge-gold" style="margin-bottom: 14px;">Private Real Estate Briefings</span>
        <h3 style="font-size: 32px; margin-bottom: 14px; font-family: var(--font-heading);">Receive Exclusive Off-Market & Pre-Launch Deal Flow</h3>
        <p style="font-size: 15px; color: var(--color-text-muted); margin-bottom: 32px; line-height: 1.7;">
            Join over 2,400 HNIs and corporate real estate directors receiving our bi-weekly intelligence bulletin on title updates, currency trends, and high-yield listings.
        </p>

        <form action="<?= url('newsletter/subscribe') ?>" method="POST" style="display: flex; gap: 12px; max-width: 560px; margin: 0 auto; flex-wrap: wrap;">
            <?= csrf_field() ?>
            <input type="email" name="email" class="form-control" placeholder="Enter corporate or personal email" required style="flex-grow: 1; min-width: 260px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">Subscribe ➔</button>
        </form>
    </div>
</section>

<!-- 13. Owner / Developer Call to Action -->
<section style="background: linear-gradient(135deg, #03080F 0%, #0E233D 100%); color: #FFFFFF; padding: 90px 0; position: relative;">
    <div class="container" style="text-align: center; max-width: 800px;">
        <span class="badge badge-gold" style="margin-bottom: 18px;">For Property Owners, Developers & Brokerages</span>
        <h2 style="font-size: 40px; color: #FFFFFF; margin-bottom: 18px; line-height: 1.25; font-family: var(--font-heading);">
            Convey Your High-Value Asset with Absolute Discretion & Maximum Reach
        </h2>
        <p style="font-size: 16.5px; color: #CBD5E1; margin-bottom: 36px; line-height: 1.75;">
            Connect directly with verified domestic and diaspora investors seeking authenticated residential and commercial real estate assets.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= url('register?role=seller') ?>" class="btn btn-accent btn-lg">
                List Your Property Today 🚀
            </a>
            <a href="<?= url('register?role=agent') ?>" class="btn btn-outline btn-lg" style="color: #FFFFFF; border-color: rgba(255, 255, 255, 0.4);">
                Register as an Accredited Agent
            </a>
        </div>
    </div>
</section>
