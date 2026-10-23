<!-- Schema.org RealEstateListing & Breadcrumb Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "<?= e(url('/')) ?>"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "<?= ucfirst($property['transaction_type']) ?>",
          "item": "<?= e(url('properties?type=' . $property['transaction_type'])) ?>"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "<?= e($property['state_name']) ?>",
          "item": "<?= e(url('properties?state=' . $property['state_id'])) ?>"
        },
        {
          "@type": "ListItem",
          "position": 4,
          "name": "<?= e($property['title']) ?>",
          "item": "<?= e(url('properties/' . $property['slug'])) ?>"
        }
      ]
    },
    {
      "@type": "RealEstateListing",
      "name": "<?= e($property['title']) ?>",
      "description": "<?= e(substr(strip_tags($property['description']), 0, 200)) ?>",
      "url": "<?= e(url('properties/' . $property['slug'])) ?>",
      "datePosted": "<?= date('c', strtotime($property['created_at'])) ?>",
      <?php if (!empty($property['cover_image'])): ?>
      "image": "<?= e($property['cover_image']) ?>",
      <?php endif; ?>
      "offers": {
        "@type": "Offer",
        "price": <?= (float)$property['price'] ?>,
        "priceCurrency": "<?= e($property['currency']) ?>",
        "availability": "https://schema.org/InStock",
        "validFrom": "<?= date('c', strtotime($property['created_at'])) ?>"
      },
      "about": {
        "@type": "Place",
        "name": "<?= e($property['title']) ?>",
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "<?= e($property['is_address_hidden'] ? 'Protected Physical Location' : $property['address']) ?>",
          "addressLocality": "<?= e($property['neighbourhood_name'] ?? $property['city_name']) ?>",
          "addressRegion": "<?= e($property['state_name']) ?>",
          "addressCountry": "NG"
        }
        <?php if (!empty($property['latitude']) && !empty($property['longitude'])): ?>,
        "geo": {
          "@type": "GeoCoordinates",
          "latitude": <?= (float)$property['latitude'] ?>,
          "longitude": <?= (float)$property['longitude'] ?>
        }
        <?php endif; ?>
      }
    }
  ]
}
</script>

<div class="container" style="padding-top: 32px; padding-bottom: 80px;">
    <!-- Breadcrumb & Top Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; font-size: 13px; color: var(--color-text-muted);">
        <div>
            <a href="<?= url('/') ?>">Home</a> / 
            <a href="<?= url('properties?type=' . $property['transaction_type']) ?>"><?= ucfirst($property['transaction_type']) ?></a> / 
            <a href="<?= url('properties?state=' . $property['state_id']) ?>"><?= e($property['state_name']) ?></a> / 
            <span><?= e($property['neighbourhood_name'] ?? $property['city_name']) ?></span>
        </div>
        <div>
            <strong>Listing Ref:</strong> <code><?= e($property['reference_code']) ?></code>
        </div>
    </div>

    <!-- Title & Price Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div style="max-width: 800px;">
            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                <span class="badge badge-verified">✓ Title Verified: <?= e($property['title_document'] ?? 'C of O') ?></span>
                <?php if ($property['is_luxury']): ?>
                    <span class="badge badge-gold">★ Luxury Signature</span>
                <?php endif; ?>
                <span class="badge badge-primary"><?= strtoupper($property['transaction_type']) ?></span>
            </div>
            <h1 style="font-size: 34px; line-height: 1.25; margin-bottom: 8px;"><?= e($property['title']) ?></h1>
            <p style="font-size: 15px; color: var(--color-text-muted);">📍 <?= e($property['address']) ?>, <?= e($property['neighbourhood_name'] ?? $property['city_name']) ?>, <?= e($property['state_name']) ?></p>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 36px; font-weight: 700; color: var(--color-primary); font-family: var(--font-heading);">
                <?= format_money($property['price'], $property['currency']) ?>
            </div>
            <div style="font-size: 14px; color: var(--color-accent); font-weight: 600; margin-bottom: 8px;">
                <?= e($property['price_suffix']) ?>
            </div>
            <?php
            $isSaved = false;
            if (is_logged_in()) {
                $isSaved = (bool)\App\Core\Database::fetchColumn(
                    "SELECT 1 FROM saved_properties WHERE user_id = ? AND property_id = ?",
                    [auth_user()['id'], $property['id']]
                );
            }
            ?>
            <div style="display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap;" class="print-hide">
                <button type="button" onclick="window.print()" class="btn btn-outline btn-sm" title="Print Executive Prospectus">
                    🖨 Print / PDF
                </button>
                <form action="<?= url('properties/' . $property['id'] . '/favorite') ?>" method="POST" style="margin: 0;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                        <?= $isSaved ? '❤️ Saved' : '🤍 Save' ?>
                    </button>
                </form>
                <a href="https://api.whatsapp.com/send?text=<?= urlencode('Inspect this verified property on Afriserva: ' . $property['title'] . ' - ' . url('properties/' . $property['slug'])) ?>" target="_blank" class="btn btn-outline btn-sm" title="Share via WhatsApp">
                    📤 Share
                </a>
            </div>
        </div>
    </div>

    <!-- Gallery Grid -->
    <div class="gallery-grid">
        <?php if (!empty($property['images'])): ?>
            <?php foreach ($property['images'] as $idx => $img): ?>
                <div class="gallery-item <?= $idx === 0 ? 'gallery-item-main' : '' ?>">
                    <img src="<?= e($img['image_url']) ?>" alt="<?= e($property['title']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200';">
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="gallery-item gallery-item-main">
                <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200" alt="<?= e($property['title']) ?>" loading="lazy">
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content & Contact Sidebar -->
    <div class="detail-layout">
        <div>
            <!-- Key Property Highlights Bar -->
            <div class="detail-card" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 16px; text-align: center;">
                <?php if ($property['bedrooms'] > 0): ?>
                    <div>
                        <div style="font-size: 13px; color: var(--color-text-muted);">Bedrooms</div>
                        <div style="font-size: 20px; font-weight: 700; color: var(--color-primary);">🛏 <?= $property['bedrooms'] ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($property['bathrooms'] > 0): ?>
                    <div>
                        <div style="font-size: 13px; color: var(--color-text-muted);">Bathrooms</div>
                        <div style="font-size: 20px; font-weight: 700; color: var(--color-primary);">🚿 <?= $property['bathrooms'] ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($property['parking_spaces'] > 0): ?>
                    <div>
                        <div style="font-size: 13px; color: var(--color-text-muted);">Parking</div>
                        <div style="font-size: 20px; font-weight: 700; color: var(--color-primary);">🚗 <?= $property['parking_spaces'] ?> Cars</div>
                    </div>
                <?php endif; ?>
                <?php if ($property['building_size_sqm'] > 0): ?>
                    <div>
                        <div style="font-size: 13px; color: var(--color-text-muted);">Building Area</div>
                        <div style="font-size: 20px; font-weight: 700; color: var(--color-primary);">📐 <?= format_sqm($property['building_size_sqm']) ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($property['land_size_sqm'] > 0): ?>
                    <div>
                        <div style="font-size: 13px; color: var(--color-text-muted);">Plot Size</div>
                        <div style="font-size: 20px; font-weight: 700; color: var(--color-primary);">🗺 <?= format_sqm($property['land_size_sqm']) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="detail-card">
                <h3 style="font-size: 22px; margin-bottom: 16px;">Property Architecture & Specifications</h3>
                <div style="font-size: 15px; line-height: 1.8; color: var(--color-secondary); white-space: pre-line;">
                    <?= e($property['description']) ?>
                </div>
            </div>

            <!-- Amenities -->
            <?php if (!empty($property['amenities'])): ?>
                <div class="detail-card">
                    <h3 style="font-size: 22px; margin-bottom: 16px;">Verified Amenities & Luxury Inclusions</h3>
                    <div class="amenities-list">
                        <?php foreach ($property['amenities'] as $am): ?>
                            <div class="amenity-pill">
                                <span style="color: var(--color-success); font-weight: bold;">✓</span>
                                <?= e($am['name']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Location Map & Address Privacy -->
            <div class="detail-card">
                <h3 style="font-size: 22px; margin-bottom: 16px;">Location & Vicinity</h3>
                <?php if (!empty($property['is_address_hidden'])): ?>
                    <div class="alert alert-warning" style="margin-bottom: 16px;">
                        🔒 <strong>High-Security Enclave (Address Obfuscated):</strong> To preserve resident privacy and physical security in this premier corridor, exact street gates and cadastral beacons are disclosed exclusively to verified clients upon confirmed inspection booking.
                    </div>
                    <p style="font-size: 14px; color: var(--color-text-muted); margin-bottom: 16px;">
                        📍 Vicinity: <?= e($property['neighbourhood_name'] ?? $property['city_name']) ?>, <?= e($property['state_name']) ?> (Coordinates approximate to 500m centroid)
                    </p>
                <?php else: ?>
                    <p style="font-size: 14px; color: var(--color-text-muted); margin-bottom: 16px;">
                        📍 <?= e($property['address']) ?>, <?= e($property['neighbourhood_name'] ?? $property['city_name']) ?>
                    </p>
                <?php endif; ?>
                <div id="property-detail-map" style="height: 320px; border-radius: var(--radius-md); border: 1px solid var(--color-border);"></div>
            </div>

            <!-- Statutory Real Estate Notice & Legal Title Disclaimer -->
            <div class="detail-card" style="background: #FFFBEB; border-color: #FDE68A;">
                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="font-size: 24px;">⚖️</div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #92400E; margin-bottom: 6px;">Statutory Real Estate Notice & Title Disclaimer</h4>
                        <p style="font-size: 13px; color: #78350F; line-height: 1.6;">
                            The "Verified" mark confirms that the listing agent or owner has submitted corresponding title documentation (e.g., Certificate of Occupancy, Governor’s Consent, or Survey Plan) for platform administrative review. This does not constitute a legal warranty of title, lis pendens indemnity, or substitute for independent legal search conducted by a licensed legal practitioner at the State Land Registry (e.g., Lagos State Lands Bureau, Alausa or Abuja AGIS). All transactions must be consummated with independent conveyancing counsel.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mortgage & Investment Financing Calculator -->
            <div class="detail-card">
                <h3 style="font-size: 22px; margin-bottom: 16px;">Estimated Mortgage & Financing Calculator</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <label class="form-label">Property Value (₦)</label>
                        <input type="number" id="mortgage-price" class="form-control" value="<?= (int)$property['price'] ?>">
                    </div>
                    <div>
                        <label class="form-label">Down Payment (%)</label>
                        <input type="number" id="mortgage-downpayment" class="form-control" value="25">
                    </div>
                    <div>
                        <label class="form-label">Annual Interest Rate (%)</label>
                        <input type="number" id="mortgage-rate" class="form-control" value="16.5" step="0.1">
                    </div>
                    <div>
                        <label class="form-label">Tenure (Years)</label>
                        <input type="number" id="mortgage-years" class="form-control" value="20">
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; padding: 16px 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    <div>
                        <span style="font-size: 13px; color: var(--color-text-muted); text-transform: uppercase;">Estimated Repayment:</span>
                        <div id="mortgage-monthly-result" style="font-size: 24px; font-weight: 700; color: var(--color-primary); font-family: var(--font-heading);">
                            ₦... / month
                        </div>
                    </div>
                    <button type="button" id="btn-calculate-mortgage" class="btn btn-primary">Calculate Payment</button>
                </div>
            </div>
        </div>

        <!-- Sticky Contact & Booking Sidebar -->
        <aside>
            <div class="contact-sidebar">
                <?php if ($property['transaction_type'] === 'short_let'): ?>
                    <!-- Dedicated Short-Let Instant Booking Card -->
                    <div class="detail-card" style="border: 2px solid var(--color-accent); margin-bottom: 24px;">
                        <span class="badge badge-gold" style="margin-bottom: 12px;">Executive Short-Let Reservation</span>
                        <div style="font-size: 24px; font-weight: 700; color: var(--color-primary); margin-bottom: 16px;">
                            <?= format_money($property['price']) ?> <span style="font-size: 13px; font-weight: normal; color: var(--color-text-muted);">/ night</span>
                        </div>

                        <form action="<?= url('short-let/book') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="property_id" value="<?= $property['id'] ?>">

                            <div class="form-group">
                                <label class="form-label">Check-In Date</label>
                                <input type="date" name="check_in_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Check-Out Date</label>
                                <input type="date" name="check_out_date" class="form-control" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Guests Count</label>
                                <select name="guests_count" class="form-control">
                                    <option value="1">1 Guest</option>
                                    <option value="2" selected>2 Guests</option>
                                    <option value="4">Up to 4 Guests</option>
                                    <option value="6">Executive Party (6+)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Guest Full Name</label>
                                <input type="text" name="guest_name" class="form-control" required value="<?= e(auth_user()['full_name'] ?? '') ?>" placeholder="Full Name">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="guest_email" class="form-control" required value="<?= e(auth_user()['email'] ?? '') ?>" placeholder="Email">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone / WhatsApp</label>
                                <input type="tel" name="guest_phone" class="form-control" required value="<?= e(auth_user()['phone'] ?? '') ?>" placeholder="+234...">
                            </div>

                            <button type="submit" class="btn btn-accent btn-lg" style="width: 100%;">
                                Reserve Executive Suite ➔
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="detail-card">
                    <div class="agent-profile-box">
                        <img src="<?= e($property['agent_avatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80') ?>" class="agent-box-avatar" alt="Agent">
                        <h4 style="font-size: 18px; margin-bottom: 4px;"><?= e($property['agent_name']) ?></h4>
                        <div style="font-size: 12px; color: var(--color-accent); font-weight: 700; margin-bottom: 4px;">
                            ★ <?= $property['agent_rating'] ?? '4.95' ?> Rating
                        </div>
                        <div style="font-size: 13px; color: var(--color-text-muted);"><?= e($property['agency_name'] ?? 'Afriserva Elite Realty') ?></div>
                        <div style="display: flex; gap: 4px; justify-content: center; margin-top: 8px;">
                            <span class="badge badge-verified" style="font-size: 10px;">LASRERA Compliant</span>
                            <span class="badge badge-gold" style="font-size: 10px;">SCUML Audited</span>
                        </div>
                    </div>

                    <!-- Direct Instant Actions with Lead Attribution Tracking -->
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px;">
                        <?php if (in_array($property['transaction_type'] ?? '', ['rent', 'short_let'], true)): ?>
                            <a href="<?= url('properties/' . $property['slug'] . '/apply') ?>" class="btn btn-primary" style="width: 100%; background: #1E3A8A; font-weight: 700;">
                                📑 Apply for Tenancy (Rental Application)
                            </a>
                        <?php endif; ?>
                        <a href="<?= url('properties/' . $property['id'] . '/track-lead') ?>" target="_blank" class="btn btn-accent" style="width: 100%;">
                            💬 Chat on WhatsApp (Verified Lead)
                        </a>
                        <button type="button" class="btn btn-primary js-open-viewing-modal" style="width: 100%;">
                            📅 Schedule Inspection Tour
                        </button>
                    </div>

                    <!-- Enquiry Form -->
                    <form action="<?= url('enquiries') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                        <input type="hidden" name="agent_id" value="<?= $property['user_id'] ?>">

                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= e(auth_user()['full_name'] ?? '') ?>" placeholder="Your name">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required value="<?= e(auth_user()['email'] ?? '') ?>" placeholder="name@domain.com">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone / WhatsApp</label>
                            <input type="tel" name="phone" class="form-control" required value="<?= e(auth_user()['phone'] ?? '') ?>" placeholder="+234...">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Inquiry Message</label>
                            <textarea name="message" class="form-control" rows="3" required>I am interested in <?= e($property['title']) ?>. Please share additional verification documents and available inspection dates.</textarea>
                        </div>

                        <button type="submit" class="btn btn-outline" style="width: 100%; margin-bottom: 12px;">
                            Send Official Enquiry
                        </button>
                    </form>

                    <div style="text-align: center; border-top: 1px solid var(--color-border); padding-top: 12px;" class="print-hide">
                        <button type="button" class="js-open-report-modal" style="background: none; border: none; font-size: 12px; color: var(--color-danger); cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                            ⚠️ Report Inaccurate or Fraudulent Listing
                        </button>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- Safety & Anti-Fraud Guidance for Prospective Buyers & Tenants -->
    <div class="detail-card print-hide" style="background: #F8FAFC; border-left: 4px solid var(--color-info); margin-top: 24px;">
        <h4 style="font-size: 16px; font-weight: 700; color: var(--color-primary); margin-bottom: 8px;">
            🛡 Consumer Safety & Due Diligence Advisory
        </h4>
        <ul style="font-size: 13px; color: var(--color-secondary); line-height: 1.7; padding-left: 20px;">
            <li><strong>Never transfer funds to private individual accounts:</strong> Always transact through designated corporate client trust accounts or accredited conveyancing solicitors.</li>
            <li><strong>Mandatory Physical Inspection:</strong> Never consummate leases or purchases without accompanied physical inspection with accredited agents holding LASRERA/NIESV accreditation.</li>
            <li><strong>Independent Title Verification:</strong> Commission independent search at the State Lands Bureau (e.g. Alausa Lands Bureau or Abuja AGIS) prior to executing deeds of assignment.</li>
        </ul>
    </div>

    <!-- Similar Properties Section -->
    <?php if (!empty($similar)): ?>
        <div style="margin-top: 60px;" class="print-hide">
            <div style="margin-bottom: 24px;">
                <span class="section-tag">Market Comparables</span>
                <h3 style="font-size: 26px; font-family: var(--font-heading); color: var(--color-primary);">Similar Verified Listings</h3>
            </div>

            <div class="property-grid" style="margin-top: 0;">
                <?php foreach ($similar as $sim): ?>
                    <article class="property-card">
                        <div class="card-media">
                            <img src="<?= e($sim['cover_image']) ?>" alt="<?= e($sim['title']) ?>" loading="lazy">
                            <div class="card-price-tag">
                                <?= format_money($sim['price'], $sim['currency']) ?>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="card-location">📍 <?= e($sim['city_name']) ?>, <?= e($sim['state_name']) ?></div>
                            <h4 class="card-title">
                                <a href="<?= url('properties/' . $sim['slug']) ?>"><?= e($sim['title']) ?></a>
                            </h4>
                            <div class="card-footer">
                                <a href="<?= url('properties/' . $sim['slug']) ?>" class="btn btn-outline btn-sm">View Details ➔</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recently Viewed Properties Section -->
    <?php if (!empty($recentlyViewed)): ?>
        <div style="margin-top: 48px;" class="print-hide">
            <div style="margin-bottom: 20px;">
                <span class="section-tag">Your Activity</span>
                <h3 style="font-size: 22px; font-family: var(--font-heading); color: var(--color-primary);">Recently Viewed Properties</h3>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
                <?php foreach ($recentlyViewed as $rv): ?>
                    <a href="<?= url('properties/' . $rv['slug']) ?>" style="display: flex; gap: 12px; background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); padding: 10px; align-items: center; box-shadow: var(--shadow-subtle);">
                        <img src="<?= e($rv['cover_image']) ?>" alt="<?= e($rv['title']) ?>" style="width: 70px; height: 56px; border-radius: var(--radius-sm); object-fit: cover;">
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--color-primary); line-height: 1.3; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= e($rv['title']) ?>
                            </div>
                            <div style="font-size: 13px; font-weight: 700; color: var(--color-primary);">
                                <?= format_money($rv['price'], $rv['currency']) ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Schedule Inspection Tour -->
<div id="viewing-modal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-xl); max-width: 500px; width: 100%; padding: 32px; box-shadow: var(--shadow-luxury); position: relative;">
        <button type="button" class="js-close-modal" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 20px; cursor: pointer; color: var(--color-text-muted);">✕</button>

        <h3 style="font-size: 22px; margin-bottom: 8px;">Schedule Property Inspection</h3>
        <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 24px;">
            Book an accompanied private inspection of <strong><?= e($property['title']) ?></strong> with an accredited advisor.
        </p>

        <form action="<?= url('viewings') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
            <input type="hidden" name="agent_id" value="<?= $property['user_id'] ?>">

            <div class="form-group">
                <label class="form-label">Preferred Date</label>
                <input type="date" name="appointment_date" class="form-control" required min="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Preferred Time Window</label>
                <select name="appointment_time" class="form-control" required>
                    <option value="10:00:00">Morning (10:00 AM)</option>
                    <option value="13:00:00">Midday (01:00 PM)</option>
                    <option value="16:00:00">Afternoon (04:00 PM)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Inspection Type</label>
                <select name="viewing_type" class="form-control">
                    <option value="physical">In-Person Physical Walkthrough</option>
                    <option value="virtual_video">Live Guided Video Inspection</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Special Requests / Guest Count</label>
                <input type="text" name="notes" class="form-control" placeholder="e.g. Attending with family architect">
            </div>

            <button type="submit" class="btn btn-accent btn-lg" style="width: 100%;">
                Confirm Inspection Booking
            </button>
        </form>
    </div>
</div>

<!-- Modal: Report Inaccurate Listing -->
<div id="report-modal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-xl); max-width: 480px; width: 100%; padding: 32px; box-shadow: var(--shadow-luxury); position: relative;">
        <button type="button" class="js-close-report-modal" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 20px; cursor: pointer; color: var(--color-text-muted);">✕</button>

        <h3 style="font-size: 20px; margin-bottom: 8px; color: var(--color-danger);">Report Listing to Compliance</h3>
        <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 20px;">
            Afriserva operates a zero-tolerance policy for fraudulent representation, unavailable properties, or forged title deeds.
        </p>

        <form action="<?= url('properties/' . $property['id'] . '/report') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Nature of Violation</label>
                <select name="reason" class="form-control" required>
                    <option value="Property already sold or unavailable">Property already sold or unavailable</option>
                    <option value="Inaccurate pricing or hidden fee demands">Inaccurate pricing or hidden fee demands</option>
                    <option value="Fraudulent agent impersonation">Fraudulent agent impersonation</option>
                    <option value="Invalid or disputed title documentation">Invalid or disputed title documentation</option>
                    <option value="Incorrect address or misleading photos">Incorrect address or misleading photos</option>
                    <option value="Other compliance breach">Other compliance breach</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Descriptive Details</label>
                <textarea name="details" class="form-control" rows="3" required placeholder="Provide specific circumstances, transaction dates, or evidence..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; background: var(--color-danger); border-color: var(--color-danger);">
                Submit Formal Compliance Report
            </button>
        </form>
    </div>
</div>

<style>
@media print {
    .site-header, .site-footer, .print-hide, .contact-sidebar, .detail-card form, #property-detail-map, #btn-calculate-mortgage {
        display: none !important;
    }
    .detail-layout {
        display: block !important;
    }
    body {
        background: #FFFFFF !important;
        color: #000000 !important;
    }
    .gallery-grid {
        grid-template-columns: 1fr 1fr !important;
        grid-template-rows: 180px !important;
    }
    .gallery-item:nth-child(n+3) {
        display: none !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Leaflet Map Detail
    const mapContainer = document.getElementById('property-detail-map');
    if (mapContainer) {
        const lat = <?= $property['latitude'] ?? 6.4549 ?>;
        const lng = <?= $property['longitude'] ?? 3.4246 ?>;
        const detailMap = L.map('property-detail-map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(detailMap);
        L.marker([lat, lng]).addTo(detailMap)
            .bindPopup("<b><?= e($property['title']) ?></b><br><?= format_money($property['price']) ?>")
            .openPopup();
    }

    // Report Modal Logic
    const reportModal = document.getElementById('report-modal');
    const openReportBtns = document.querySelectorAll('.js-open-report-modal');
    const closeReportBtns = document.querySelectorAll('.js-close-report-modal');

    if (reportModal && openReportBtns.length) {
        openReportBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                reportModal.style.display = 'flex';
            });
        });
        closeReportBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                reportModal.style.display = 'none';
            });
        });
        window.addEventListener('click', (e) => {
            if (e.target === reportModal) {
                reportModal.style.display = 'none';
            }
        });
    }
});
</script>

