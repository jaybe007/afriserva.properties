<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 32px;">
        <h2 style="font-size: 28px; margin-bottom: 8px;">Create New Property Listing</h2>
        <p style="color: var(--color-text-muted);">
            Provide comprehensive, accurate property data to qualify for the <strong>Verified Title Badge</strong>.
        </p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Please resolve the following errors:</strong>
            <ul style="margin-top: 8px; margin-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= url('dashboard/listings/store') ?>" method="POST" style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 40px; box-shadow: var(--shadow-subtle);">
        <?= csrf_field() ?>

        <!-- 1. Basic Information -->
        <h3 style="font-size: 18px; margin-bottom: 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            1. Core Classification & Narrative
        </h3>

        <div class="form-group">
            <label class="form-label">Property Title *</label>
            <input type="text" name="title" class="form-control" required value="<?= e($input['title'] ?? '') ?>" placeholder="e.g. Contemporary 5-Bedroom Fully Detached Mansion with Infinity Pool">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Transaction Type *</label>
                <select name="transaction_type" class="form-control" required>
                    <option value="sale" <?= ($input['transaction_type'] ?? '') === 'sale' ? 'selected' : '' ?>>For Sale</option>
                    <option value="rent" <?= ($input['transaction_type'] ?? '') === 'rent' ? 'selected' : '' ?>>For Rent</option>
                    <option value="short_let" <?= ($input['transaction_type'] ?? '') === 'short_let' ? 'selected' : '' ?>>Short Let</option>
                    <option value="commercial" <?= ($input['transaction_type'] ?? '') === 'commercial' ? 'selected' : '' ?>>Commercial</option>
                    <option value="land" <?= ($input['transaction_type'] ?? '') === 'land' ? 'selected' : '' ?>>Land & Plots</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category_id" class="form-control" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($input['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Property Type *</label>
                <select name="property_type_id" class="form-control" required>
                    <?php foreach ($propertyTypes as $pt): ?>
                        <option value="<?= $pt['id'] ?>" <?= ($input['property_type_id'] ?? '') == $pt['id'] ? 'selected' : '' ?>>
                            <?= e($pt['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- 2. Pricing -->
        <h3 style="font-size: 18px; margin: 32px 0 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            2. Commercial Terms & Pricing
        </h3>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Price (₦ - NGN) *</label>
                <input type="number" step="1000" name="price" class="form-control" required value="<?= e($input['price'] ?? '') ?>" placeholder="e.g. 350000000">
            </div>

            <div class="form-group">
                <label class="form-label">Price Suffix</label>
                <input type="text" name="price_suffix" class="form-control" value="<?= e($input['price_suffix'] ?? '') ?>" placeholder="e.g. / annum, / night, negotiable">
            </div>
        </div>

        <!-- 3. Location -->
        <h3 style="font-size: 18px; margin: 32px 0 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            3. Geospatial Location & Demarcation
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">State / Federal Territory *</label>
                <select name="state_id" class="form-control" required>
                    <?php foreach ($states as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($input['state_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                            <?= e($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">City / Local Government *</label>
                <input type="text" name="city_id" class="form-control" value="2" placeholder="2 for Eti-Osa/Island, 3 for Ikeja">
                <small style="color: var(--color-text-muted);">Enter 2 for Eti-Osa/Lagos Island, 5 for Abuja Municipal</small>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Street Address & Landmark *</label>
            <input type="text" name="address" class="form-control" required value="<?= e($input['address'] ?? '') ?>" placeholder="e.g. Zone K, Banana Island, Ikoyi, Lagos">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Latitude (for Interactive Map)</label>
                <input type="text" name="latitude" class="form-control" value="<?= e($input['latitude'] ?? '6.4549') ?>" placeholder="e.g. 6.4549">
            </div>
            <div class="form-group">
                <label class="form-label">Longitude (for Interactive Map)</label>
                <input type="text" name="longitude" class="form-control" value="<?= e($input['longitude'] ?? '3.4246') ?>" placeholder="e.g. 3.4246">
            </div>
        </div>

        <!-- 4. Specifications -->
        <h3 style="font-size: 18px; margin: 32px 0 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            4. Architecture, Dimensions & Title Documents
        </h3>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
            <div class="form-group">
                <label class="form-label">Bedrooms</label>
                <input type="number" name="bedrooms" class="form-control" value="<?= e($input['bedrooms'] ?? '4') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Bathrooms</label>
                <input type="number" name="bathrooms" class="form-control" value="<?= e($input['bathrooms'] ?? '4') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Parking Spaces</label>
                <input type="number" name="parking_spaces" class="form-control" value="<?= e($input['parking_spaces'] ?? '4') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Building Area (SQM)</label>
                <input type="number" name="building_size_sqm" class="form-control" value="<?= e($input['building_size_sqm'] ?? '450') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Furnishing Status</label>
                <select name="furnishing_status" class="form-control">
                    <option value="unfurnished">Unfurnished</option>
                    <option value="semi_furnished">Semi Furnished</option>
                    <option value="fully_furnished">Fully Furnished</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Condition</label>
                <select name="property_condition" class="form-control">
                    <option value="brand_new">Brand New</option>
                    <option value="excellent">Excellent</option>
                    <option value="good">Good</option>
                    <option value="under_construction">Off-Plan / Under Construction</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Title Document Type *</label>
                <select name="title_document" class="form-control" required>
                    <option value="Federal Certificate of Occupancy">Federal C of O</option>
                    <option value="State Certificate of Occupancy">State C of O</option>
                    <option value="Governor's Consent">Governor's Consent</option>
                    <option value="Deed of Assignment">Deed of Assignment</option>
                    <option value="Gazette">Gazette</option>
                    <option value="Approved Building Plan">Approved Building Plan</option>
                </select>
            </div>
        </div>

        <!-- 5. Description -->
        <div class="form-group">
            <label class="form-label">Detailed Property Description & Narrative *</label>
            <textarea name="description" class="form-control" rows="6" required placeholder="Describe interior layout, high-end finishing materials, kitchen appointments, compound size, and security infrastructure..."><?= e($input['description'] ?? '') ?></textarea>
        </div>

        <!-- 6. Amenities Checklist -->
        <h3 style="font-size: 18px; margin: 32px 0 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            5. Property Amenities & Lifestyle Inclusions
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 32px;">
            <?php foreach ($amenities as $am): ?>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                    <input type="checkbox" name="amenities[]" value="<?= $am['id'] ?>">
                    <span><?= e($am['name']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <!-- 7. Media Cover Image -->
        <h3 style="font-size: 18px; margin: 32px 0 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
            6. High-Definition Photography
        </h3>
        <div class="form-group">
            <label class="form-label">Primary Cover Image URL</label>
            <input type="url" name="image_url" class="form-control" value="<?= e($input['image_url'] ?? '') ?>" placeholder="https://images.unsplash.com/photo-...">
            <small style="color: var(--color-text-muted);">Provide a high-resolution image URL (or leave blank to use high-quality default architecture photography).</small>
        </div>

        <button type="submit" class="btn btn-accent btn-lg" style="width: 100%; margin-top: 16px;">
            Submit Property for Verification & Publishing ➔
        </button>
    </form>
</div>
