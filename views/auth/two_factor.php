<div class="container" style="max-width: 480px; padding: 60px 24px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 40px; box-shadow: var(--shadow-luxury);">
        <div style="text-align: center; margin-bottom: 32px;">
            <div class="brand-icon" style="margin: 0 auto 16px;">🔐</div>
            <h1 style="font-size: 24px; margin-bottom: 8px;">Two-Factor Challenge</h1>
            <p style="font-size: 14px; color: var(--color-text-muted);">
                Two-Factor Authentication (2FA) is enforced on this account. Enter the 6-digit verification code sent to your authorized credentials.
            </p>
        </div>

        <?php if (!empty($demoCode)): ?>
            <div class="alert alert-info" style="margin-bottom: 20px; text-align: center;">
                <strong>Demo Security Code:</strong> <code><?= e($demoCode) ?></code>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['2fa'])): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;"><?= e($errors['2fa']) ?></div>
        <?php endif; ?>

        <form action="<?= url('login/2fa') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" style="text-align: center; display: block;">6-Digit Security Token</label>
                <input type="text" name="two_factor_code" class="form-control" required placeholder="123456" maxlength="6" style="text-align: center; font-size: 24px; letter-spacing: 6px; font-weight: 700;" autofocus>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 12px;">
                Verify Code & Authorize Access ➔
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 13px; color: var(--color-text-muted);">
            <a href="<?= url('login') ?>" style="color: var(--color-primary); font-weight: 600;">Cancel & Return to Sign In</a>
        </div>
    </div>
</div>
