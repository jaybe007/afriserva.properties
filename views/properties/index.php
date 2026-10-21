<div class="container" style="padding-top: 40px; padding-bottom: 80px;">
    <!-- Search Bar & Filters Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; margin-bottom: 8px;">Explore Verified Properties in Nigeria</h1>
        <p style="color: var(--color-text-muted);">
            Showing <?= number_format($totalCount) ?> verified residential, commercial, short-let, and land listings.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 300px 1fr; gap: 32px; align-items: start;">
        <!-- Filters Sidebar -->
        <aside style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 24px; box-shadow: var(--shadow-subtle);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 16px; font-weight: 700;">Filter Properties</h3>
                <a href="<?= url('properties') ?>" style="font-size: 12px; color: var(--color-text-muted);">Clear All</a>
            </div>

            <form action="<?= url('properties') ?>" method="GET">
                <div class="form-group">
                    <label class="form-label">Keyword / Street</label>
                    <input type="text" name="q" class="form-control" value="<?= e($filters['keyword'] ?? '') ?>" placeholder="e.g. Waterfront, Penthouse">
                </div>

                <div class="form-group">
                    <label class="form-label">Transaction Type</label>
                    <select name="type" class="form-control">
                        <option value="">All Transactions</option>
                        <option value="sale" <?= ($filters['transaction_type'] ?? '') === 'sale' ? 'selected' : '' ?>>For Sale</option>
                        <option value="rent" <?= ($filters['transaction_type'] ?? '') === 'rent' ? 'selected' : '' ?>>For Rent</option>
                        <option value="short_let" <?= ($filters['transaction_type'] ?? '') === 'short_let' ? 'selected' : '' ?>>Short Let</option>
                        <option value="commercial" <?= ($filters['transaction_type'] ?? '') === 'commercial' ? 'selected' : '' ?>>Commercial</option>
                        <option value="land" <?= ($filters['transaction_type'] ?? '') === 'land' ? 'selected' : '' ?>>Land & Plots</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">State / Region</label>
                    <select name="state" class="form-control">
                        <option value="">All States</option>
                        <?php foreach ($states as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($filters['state_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                <?= e($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Min Bedrooms</label>
                    <select name="beds" class="form-control">
                        <option value="">Any</option>
                        <option value="1" <?= ($filters['bedrooms'] ?? '') == '1' ? 'selected' : '' ?>>1+ Bedroom</option>
                        <option value="2" <?= ($filters['bedrooms'] ?? '') == '2' ? 'selected' : '' ?>>2+ Bedrooms</option>
                        <option value="3" <?= ($filters['bedrooms'] ?? '') == '3' ? 'selected' : '' ?>>3+ Bedrooms</option>
                        <option value="4" <?= ($filters['bedrooms'] ?? '') == '4' ? 'selected' : '' ?>>4+ Bedrooms</option>
                        <option value="5" <?= ($filters['bedrooms'] ?? '') == '5' ? 'selected' : '' ?>>5+ Bedrooms</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Min Bathrooms</label>
                    <select name="baths" class="form-control">
                        <option value="">Any</option>
                        <option value="1" <?= ($filters['bathrooms'] ?? '') == '1' ? 'selected' : '' ?>>1+ Bathroom</option>
                        <option value="2" <?= ($filters['bathrooms'] ?? '') == '2' ? 'selected' : '' ?>>2+ Bathrooms</option>
                        <option value="3" <?= ($filters['bathrooms'] ?? '') == '3' ? 'selected' : '' ?>>3+ Bathrooms</option>
                        <option value="4" <?= ($filters['bathrooms'] ?? '') == '4' ? 'selected' : '' ?>>4+ Bathrooms</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Min Price (₦)</label>
                    <input type="number" name="min_price" class="form-control" value="<?= e($filters['min_price'] ?? '') ?>" placeholder="e.g. 50000000">
                </div>

                <div class="form-group">
                    <label class="form-label">Max Price (₦)</label>
                    <input type="number" name="max_price" class="form-control" value="<?= e($filters['max_price'] ?? '') ?>" placeholder="e.g. 1000000000">
                </div>

                <div class="form-group">
                    <label class="form-label">Furnishing Status</label>
                    <select name="furnishing" class="form-control">
                        <option value="">Any</option>
                        <option value="fully_furnished" <?= ($filters['furnishing_status'] ?? '') === 'fully_furnished' ? 'selected' : '' ?>>Fully Furnished</option>
                        <option value="semi_furnished" <?= ($filters['furnishing_status'] ?? '') === 'semi_furnished' ? 'selected' : '' ?>>Semi Furnished</option>
                        <option value="unfurnished" <?= ($filters['furnishing_status'] ?? '') === 'unfurnished' ? 'selected' : '' ?>>Unfurnished</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Title Document</label>
                    <input type="text" name="title_doc" class="form-control" value="<?= e($filters['title_doc'] ?? '') ?>" placeholder="e.g. C of O, Governor's Consent">
                </div>

                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <select name="sort" class="form-control">
                        <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newly Listed</option>
                        <option value="price_asc" <?= ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="views" <?= ($filters['sort'] ?? '') === 'views' ? 'selected' : '' ?>>Most Popular</option>
                    </select>
                </div>

                <div style="margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="verified" value="1" <?= !empty($filters['verified']) ? 'checked' : '' ?>>
                        <span>Verified Legal Title Only</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="luxury" value="1" <?= !empty($filters['luxury']) ? 'checked' : '' ?>>
                        <span>Luxury Signature Only</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Apply Filters
                </button>
            </form>
        </aside>

        <!-- Listings Content Area -->
        <main>
            <!-- Active Filter Chips -->
            <?php
            $activeChips = [];
            if (!empty($filters['keyword'])) $activeChips['q'] = 'Keyword: ' . $filters['keyword'];
            if (!empty($filters['transaction_type'])) $activeChips['type'] = 'Type: ' . ucfirst($filters['transaction_type']);
            if (!empty($filters['state_id'])) $activeChips['state'] = 'State Selected';
            if (!empty($filters['bedrooms'])) $activeChips['beds'] = $filters['bedrooms'] . '+ Beds';
            if (!empty($filters['min_price'])) $activeChips['min_price'] = 'Min: ₦' . number_format((float)$filters['min_price']);
            if (!empty($filters['max_price'])) $activeChips['max_price'] = 'Max: ₦' . number_format((float)$filters['max_price']);
            ?>

            <?php if (!empty($activeChips)): ?>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 24px; padding: 12px 16px; background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    <span style="font-size: 12px; font-weight: 700; color: var(--color-secondary); text-transform: uppercase;">Active Filters:</span>
                    <?php foreach ($activeChips as $param => $label): ?>
                        <?php
                        $chipParams = $filters;
                        unset($chipParams[$param]);
                        ?>
                        <a href="<?= url('properties?' . http_build_query($chipParams)) ?>" class="badge badge-primary" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; font-size: 12px;">
                            <?= e($label) ?> ✕
                        </a>
                    <?php endforeach; ?>
                    <a href="<?= url('properties') ?>" style="font-size: 12px; color: var(--color-danger); margin-left: auto; font-weight: 600;">
                        Clear All
                    </a>
                </div>
            <?php endif; ?>

            <?php if (empty($properties)): ?>
                <div style="background: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 64px 32px; text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 16px;">🔍</div>
                    <h3 style="font-size: 20px; margin-bottom: 8px;">No Listings Match Your Query</h3>
                    <p style="color: var(--color-text-muted); margin-bottom: 24px;">
                        Try adjusting your price threshold, reducing filter criteria, or exploring another state.
                    </p>
                    <a href="<?= url('properties') ?>" class="btn btn-outline">Reset Search Filters</a>
                </div>
            <?php else: ?>
                <div class="property-grid" style="margin-top: 0;">
                    <?php foreach ($properties as $prop): ?>
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
                                        <div class="card-feature-item">
                                            🛏 <?= $prop['bedrooms'] ?> Beds
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($prop['bathrooms'] > 0): ?>
                                        <div class="card-feature-item">
                                            🚿 <?= $prop['bathrooms'] ?> Baths
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($prop['building_size_sqm'] > 0): ?>
                                        <div class="card-feature-item">
                                            📐 <?= format_sqm($prop['building_size_sqm']) ?>
                                        </div>
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

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div style="display: flex; justify-content: center; gap: 8px; margin-top: 48px;">
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <a href="<?= url('properties?' . http_build_query(array_merge($filters, ['page' => $p]))) ?>" class="btn <?= $p == $page ? 'btn-primary' : 'btn-outline' ?>" style="min-width: 44px;">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>
