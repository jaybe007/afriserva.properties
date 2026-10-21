<div class="container" style="max-width: 680px; padding: 60px 24px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 40px; box-shadow: var(--shadow-luxury);">
        <div style="text-align: center; margin-bottom: 32px;">
            <div class="brand-icon" style="margin: 0 auto 16px;">A</div>
            <h1 style="font-size: 26px; margin-bottom: 8px;">Create Your Account</h1>
            <p style="font-size: 14px; color: var(--color-text-muted);">
                Select your account classification to join Nigeria's premier institutional property marketplace.
            </p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form action="<?= url('register') ?>" method="POST">
            <?= csrf_field() ?>

            <!-- Enterprise Role Selector -->
            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Account Classification & Clearance Level</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                    <label style="border: 1px solid var(--color-border); padding: 12px; border-radius: var(--radius-md); font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="role" value="client" <?= ($role ?? 'client') === 'client' ? 'checked' : '' ?>>
                        <div>
                            <strong>Property Buyer</strong>
                            <div style="font-size: 11px; color: var(--color-text-muted);">Acquire verified assets</div>
                        </div>
                    </label>
                    <label style="border: 1px solid var(--color-border); padding: 12px; border-radius: var(--radius-md); font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="role" value="tenant" <?= ($role ?? '') === 'tenant' ? 'checked' : '' ?>>
                        <div>
                            <strong>Tenant</strong>
                            <div style="font-size: 11px; color: var(--color-text-muted);">Rent & short-let leases</div>
                        </div>
                    </label>
                    <label style="border: 1px solid var(--color-border); padding: 12px; border-radius: var(--radius-md); font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="role" value="landlord" <?= ($role ?? '') === 'landlord' ? 'checked' : '' ?>>
                        <div>
                            <strong>Landlord</strong>
                            <div style="font-size: 11px; color: var(--color-text-muted);">Direct asset owner</div>
                        </div>
                    </label>
                    <label style="border: 1px solid var(--color-border); padding: 12px; border-radius: var(--radius-md); font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="role" value="seller" <?= ($role ?? '') === 'seller' ? 'checked' : '' ?>>
                        <div>
                            <strong>Property Seller</strong>
                            <div style="font-size: 11px; color: var(--color-text-muted);">Direct sales & plots</div>
                        </div>
                    </label>
                    <label style="border: 1px solid var(--color-border); padding: 12px; border-radius: var(--radius-md); font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="role" value="agent" <?= ($role ?? '') === 'agent' ? 'checked' : '' ?>>
                        <div>
                            <strong>Individual Agent</strong>
                            <div style="font-size: 11px; color: var(--color-text-muted);">Licensed broker</div>
                        </div>
                    </label>
                    <label style="border: 1px solid var(--color-border); padding: 12px; border-radius: var(--radius-md); font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="role" value="agency_admin" <?= ($role ?? '') === 'agency_admin' ? 'checked' : '' ?>>
                        <div>
                            <strong>Real Estate Agency</strong>
                            <div style="font-size: 11px; color: var(--color-text-muted);">Corporate broker firm</div>
                        </div>
                    </label>
                    <label style="border: 1px solid var(--color-border); padding: 12px; border-radius: var(--radius-md); font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="role" value="developer" <?= ($role ?? '') === 'developer' ? 'checked' : '' ?>>
                        <div>
                            <strong>Property Developer</strong>
                            <div style="font-size: 11px; color: var(--color-text-muted);">Off-plan estates</div>
                        </div>
                    </label>
                    <label style="border: 1px solid var(--color-border); padding: 12px; border-radius: var(--radius-md); font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="role" value="property_manager" <?= ($role ?? '') === 'property_manager' ? 'checked' : '' ?>>
                        <div>
                            <strong>Property Manager</strong>
                            <div style="font-size: 11px; color: var(--color-text-muted);">Facility operations</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Full Legal Name</label>
                <input type="text" name="full_name" class="form-control" required value="<?= e($input['full_name'] ?? '') ?>" placeholder="e.g. Chief Babatunde Adeleke">
                <?php if (!empty($errors['full_name'])): ?>
                    <small style="color: var(--color-danger); font-size: 12px;"><?= e($errors['full_name']) ?></small>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required value="<?= e($input['email'] ?? '') ?>" placeholder="name@domain.com">
                    <?php if (!empty($errors['email'])): ?>
                        <small style="color: var(--color-danger); font-size: 12px;"><?= e($errors['email']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone / WhatsApp Number</label>
                    <input type="tel" name="phone" class="form-control" required value="<?= e($input['phone'] ?? '') ?>" placeholder="+234 803 000 0000">
                    <?php if (!empty($errors['phone'])): ?>
                        <small style="color: var(--color-danger); font-size: 12px;"><?= e($errors['phone']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password (min 8 characters, hashed with Argon2id)</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
                <?php if (!empty($errors['password'])): ?>
                    <small style="color: var(--color-danger); font-size: 12px;"><?= e($errors['password']) ?></small>
                <?php endif; ?>
            </div>

            <!-- Terms & Consent -->
            <div style="margin: 20px 0; background: #F8FAFC; border: 1px solid var(--color-border); padding: 14px; border-radius: var(--radius-md);">
                <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 13px; cursor: pointer;">
                    <input type="checkbox" name="terms_consent" value="1" required style="margin-top: 3px;">
                    <span style="color: var(--color-text-muted);">
                        I agree to the <strong>Terms of Service</strong> and <strong>Privacy Policy</strong>. I understand that all ownership, price, and regulatory claims are subject to statutory verification and immutable audit logging.
                    </span>
                </label>
                <?php if (!empty($errors['terms_consent'])): ?>
                    <small style="color: var(--color-danger); font-size: 12px;"><?= e($errors['terms_consent']) ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-accent btn-lg" style="width: 100%; margin-top: 8px;">
                Complete Secure Registration ➔
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 13px; color: var(--color-text-muted);">
            Already have an active account? 
            <a href="<?= url('login') ?>" style="color: var(--color-primary); font-weight: 600;">Sign In</a>
        </div>
    </div>
</div>
