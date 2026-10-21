<div class="container" style="max-width: 480px; padding: 60px 24px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 40px; box-shadow: var(--shadow-luxury);">
        <div style="text-align: center; margin-bottom: 32px;">
            <div class="brand-icon" style="margin: 0 auto 16px;">🔒</div>
            <h1 style="font-size: 24px; margin-bottom: 8px;">Create New Password</h1>
            <p style="font-size: 14px; color: var(--color-text-muted);">
                Your new credentials will be secured with high-entropy Argon2id hashing algorithms.
            </p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form action="<?= url('reset-password') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

            <div class="form-group">
                <label class="form-label">New Password (min 8 characters)</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
                <?php if (!empty($errors['password'])): ?>
                    <small style="color: var(--color-danger); font-size: 12px;"><?= e($errors['password']) ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" required placeholder="••••••••">
                <?php if (!empty($errors['password_confirmation'])): ?>
                    <small style="color: var(--color-danger); font-size: 12px;"><?= e($errors['password_confirmation']) ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 12px;">
                Update Password & Secure Account ➔
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 13px; color: var(--color-text-muted);">
            <a href="<?= url('login') ?>" style="color: var(--color-primary); font-weight: 600;">Back to Sign In</a>
        </div>
    </div>
</div>
