<div style="max-width: 960px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 28px; margin-bottom: 8px;">Edit Property Listing</h2>
            <p style="color: var(--color-text-muted);">
                Reference: <code><?= e($property['reference_code']) ?></code> | Status: <strong><?= strtoupper($property['status']) ?></strong>
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?= url('properties/' . $property['slug']) ?>" target="_blank" class="btn btn-outline">
                👁️ Preview Listing ↗
            </a>
            <a href="<?= url('dashboard/listings') ?>" class="btn btn-primary">
                ← Back to Inventory
            </a>
        </div>
    </div>

    <!-- Compliance Correction Feedback Alert -->
    <?php if ($property['verification_status'] === 'additional_info_required' && !empty($property['reviewer_feedback'])): ?>
        <div class="alert alert-warning" style="margin-bottom: 24px; border-left: 4px solid var(--color-warning); background: #FFFBEB;">
            <h4 style="font-size: 15px; font-weight: 700; color: #92400E; margin-bottom: 6px;">
                ⚠️ Compliance Officer Requested Corrections
            </h4>
            <p style="margin: 0; font-size: 13px; color: #78350F;">
                <strong>Reason:</strong> <?= e($property['rejection_reason'] ?? 'Additional Information Required') ?><br>
                <strong>Auditor Feedback:</strong> <?= e($property['reviewer_feedback']) ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (!empty($duplicateWarnings)): ?>
        <div class="alert alert-warning" style="margin-bottom: 24px;">
            <strong>⚠️ Potential Duplicate Listing Detected:</strong>
            <p style="margin-top: 4px; font-size: 13px;">Our automated anti-fraud scanner identified properties with similar titles/addresses in this jurisdiction. Ensure your listing holds authentic exclusive mandate.</p>
        </div>
    <?php endif; ?>

    <!-- Main Listing Form -->
    <form action="<?= url('dashboard/listings/' . $property['id'] . '/update') ?>" method="POST" style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 40px; box-shadow: var(--shadow-subtle); margin-bottom: 32px;">
        <?= csrf_field() ?>

        <!-- 1. Core Classification -->
        <h3 style="font-size: 18px; margin-bottom: 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            1. Property Details & Title
        </h3>

        <div class="form-group">
            <label class="form-label">Property Title *</label>
            <input type="text" name="title" class="form-control" required value="<?= e($property['title']) ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Transaction Type *</label>
                <select name="transaction_type" class="form-control" required>
                    <option value="sale" <?= $property['transaction_type'] === 'sale' ? 'selected' : '' ?>>For Sale</option>
                    <option value="rent" <?= $property['transaction_type'] === 'rent' ? 'selected' : '' ?>>For Rent</option>
                    <option value="short_let" <?= $property['transaction_type'] === 'short_let' ? 'selected' : '' ?>>Short Let</option>
                    <option value="commercial" <?= $property['transaction_type'] === 'commercial' ? 'selected' : '' ?>>Commercial</option>
                    <option value="land" <?= $property['transaction_type'] === 'land' ? 'selected' : '' ?>>Land & Plots</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category_id" class="form-control" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $property['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Property Type *</label>
                <select name="property_type_id" class="form-control" required>
                    <?php foreach ($propertyTypes as $pt): ?>
                        <option value="<?= $pt['id'] ?>" <?= $property['property_type_id'] == $pt['id'] ? 'selected' : '' ?>>
                            <?= e($pt['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- 2. Pricing -->
        <h3 style="font-size: 18px; margin: 32px 0 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            2. Valuation & Financial Terms
        </h3>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Asking Price (NGN) *</label>
                <input type="number" step="0.01" name="price" class="form-control" required value="<?= e($property['price']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Price Suffix</label>
                <input type="text" name="price_suffix" class="form-control" value="<?= e($property['price_suffix'] ?? '') ?>" placeholder="e.g. /year, /night">
            </div>
        </div>

        <!-- 3. Location -->
        <h3 style="font-size: 18px; margin: 32px 0 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            3. Geographic Jurisdiction & Physical Address
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">State / Federal Territory *</label>
                <select name="state_id" class="form-control" required>
                    <?php foreach ($states as $st): ?>
                        <option value="<?= $st['id'] ?>" <?= $property['state_id'] == $st['id'] ? 'selected' : '' ?>>
                            <?= e($st['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Full Street Address *</label>
                <input type="text" name="address" class="form-control" required value="<?= e($property['address']) ?>">
            </div>
        </div>

        <div style="margin: 12px 0 24px;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="checkbox" name="is_address_hidden" value="1" <?= !empty($property['is_address_hidden']) ? 'checked' : '' ?>>
                <span><strong>Obfuscate Exact Street Address</strong> (Protects resident privacy on high-value residential assets)</span>
            </label>
        </div>

        <!-- 4. Specifications -->
        <h3 style="font-size: 18px; margin: 32px 0 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            4. Architectural Specifications
        </h3>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
            <div class="form-group">
                <label class="form-label">Bedrooms</label>
                <input type="number" name="bedrooms" class="form-control" value="<?= e($property['bedrooms'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Bathrooms</label>
                <input type="number" name="bathrooms" class="form-control" value="<?= e($property['bathrooms'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Toilets</label>
                <input type="number" name="toilets" class="form-control" value="<?= e($property['toilets'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Parking Spaces</label>
                <input type="number" name="parking_spaces" class="form-control" value="<?= e($property['parking_spaces'] ?? 0) ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Furnishing Status</label>
                <select name="furnishing_status" class="form-control">
                    <option value="unfurnished" <?= $property['furnishing_status'] === 'unfurnished' ? 'selected' : '' ?>>Unfurnished</option>
                    <option value="semi_furnished" <?= $property['furnishing_status'] === 'semi_furnished' ? 'selected' : '' ?>>Semi Furnished</option>
                    <option value="fully_furnished" <?= $property['furnishing_status'] === 'fully_furnished' ? 'selected' : '' ?>>Fully Furnished</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Condition</label>
                <select name="property_condition" class="form-control">
                    <option value="brand_new" <?= $property['property_condition'] === 'brand_new' ? 'selected' : '' ?>>Brand New</option>
                    <option value="excellent" <?= $property['property_condition'] === 'excellent' ? 'selected' : '' ?>>Excellent</option>
                    <option value="good" <?= $property['property_condition'] === 'good' ? 'selected' : '' ?>>Good</option>
                    <option value="under_construction" <?= $property['property_condition'] === 'under_construction' ? 'selected' : '' ?>>Off-Plan</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Title Document Type</label>
                <input type="text" name="title_document" class="form-control" value="<?= e($property['title_document'] ?? 'Certificate of Occupancy') ?>">
            </div>
        </div>

        <!-- 5. Description -->
        <div class="form-group">
            <label class="form-label">Detailed Property Description *</label>
            <textarea name="description" class="form-control" rows="6" required><?= e($property['description']) ?></textarea>
        </div>

        <!-- 6. Status & Publication Mode -->
        <h3 style="font-size: 18px; margin: 32px 0 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            5. Lifecycle Status & Publication
        </h3>

        <div class="form-group">
            <label class="form-label">Listing Lifecycle State</label>
            <select name="status" class="form-control" style="font-weight: 600;">
                <option value="published" <?= $property['status'] === 'published' ? 'selected' : '' ?>>Published (Live)</option>
                <option value="draft" <?= $property['status'] === 'draft' ? 'selected' : '' ?>>Draft (Unpublished)</option>
                <option value="pending_review" <?= $property['status'] === 'pending_review' ? 'selected' : '' ?>>Submit for Review</option>
                <option value="paused" <?= $property['status'] === 'paused' ? 'selected' : '' ?>>Paused (Temporarily Hidden)</option>
                <option value="sold" <?= $property['status'] === 'sold' ? 'selected' : '' ?>>Sold (Archived Transaction)</option>
                <option value="rented" <?= $property['status'] === 'rented' ? 'selected' : '' ?>>Rented (Leased Out)</option>
                <option value="archived" <?= $property['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 24px;">
            Save Property Details ➔
        </button>
    </form>

    <!-- 2. Multi-Image Gallery Manager -->
    <div style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 40px; box-shadow: var(--shadow-subtle); margin-bottom: 32px;">
        <h3 style="font-size: 18px; margin-bottom: 8px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            📸 Multi-Image Gallery & Cover Photo Manager
        </h3>
        <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 20px;">
            Add, reorder, or delete high-resolution property photography. Designate your primary hero cover.
        </p>

        <!-- Current Gallery Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <?php foreach (($images ?? []) as $img): ?>
                <div style="border: 1px solid var(--color-border); border-radius: var(--radius-md); overflow: hidden; position: relative;">
                    <img src="<?= e($img['image_url']) ?>" alt="Gallery Image" style="width: 100%; height: 130px; object-fit: cover;">
                    <div style="padding: 10px; background: #FAFAFA;">
                        <?php if (!empty($img['is_cover'])): ?>
                            <span class="badge badge-verified" style="display: block; text-align: center; margin-bottom: 6px;">★ Primary Cover</span>
                        <?php else: ?>
                            <form action="<?= url('dashboard/listings/' . $property['id'] . '/images/' . $img['id'] . '/cover') ?>" method="POST" style="margin-bottom: 6px;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; font-size: 11px;">Set as Cover</button>
                            </form>
                        <?php endif; ?>

                        <form action="<?= url('dashboard/listings/' . $property['id'] . '/images/' . $img['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this photograph?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; font-size: 11px; color: var(--color-danger); border-color: var(--color-danger);">
                                🗑 Delete Image
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Add New Image -->
        <form action="<?= url('dashboard/listings/' . $property['id'] . '/images/store') ?>" method="POST" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; background: #F8FAFC; padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <?= csrf_field() ?>
            <input type="url" name="image_url" class="form-control" placeholder="https://images.unsplash.com/photo-..." required>
            <input type="text" name="caption" class="form-control" placeholder="Caption (e.g. Master Bedroom View)">
            <button type="submit" class="btn btn-primary btn-sm">➕ Add Photo</button>
        </form>
    </div>

    <!-- 3. Property Video Tour Manager -->
    <div style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 40px; box-shadow: var(--shadow-subtle);">
        <h3 style="font-size: 18px; margin-bottom: 8px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            📹 Guided Video Tour & Virtual Walkthrough
        </h3>
        <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 20px;">
            Embed high-definition YouTube, Vimeo, or MP4 walkthroughs for remote diaspora investors.
        </p>

        <?php if (!empty($videos)): ?>
            <div style="margin-bottom: 16px;">
                <?php foreach ($videos as $vid): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 8px; border: 1px solid var(--color-border);">
                        <div>
                            <span class="badge badge-gold"><?= strtoupper($vid['video_type']) ?></span>
                            <code><?= e($vid['video_url']) ?></code>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('dashboard/listings/' . $property['id'] . '/videos/store') ?>" method="POST" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px;">
            <?= csrf_field() ?>
            <input type="url" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required>
            <select name="video_type" class="form-control">
                <option value="youtube">YouTube</option>
                <option value="vimeo">Vimeo</option>
                <option value="mp4">MP4 Video Link</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Attach Video</button>
        </form>
    </div>
</div>
