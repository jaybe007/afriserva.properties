<div class="container" style="max-width: 480px; padding: 60px 24px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 40px; box-shadow: var(--shadow-luxury);">
        <div style="text-align: center; margin-bottom: 32px;">
            <div class="brand-icon" style="margin: 0 auto 16px;">🔑</div>
            <h1 style="font-size: 24px; margin-bottom: 8px;">Reset Password</h1>
            <p style="font-size: 14px; color: var(--color-text-muted);">
                Enter your registered email address and we will issue a cryptographically secured reset authorization link.
            </p>
        </div>

        <?php if (!empty($resetLink)): ?>
            <div class="alert alert-success" style="word-break: break-all; margin-bottom: 24px;">
                <strong>Demo Reset Link Generated:</strong><br>
                <a href="<?= e($resetLink) ?>" style="font-size: 12px; color: #047857; text-decoration: underline;"><?= e($resetLink) ?></a>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['email'])): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;"><?= e($errors['email']) ?></div>
        <?php endif; ?>

        <form action="<?= url('forgot-password') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Registered Account Email</label>
                <input type="email" name="email" class="form-control" required value="<?= e($email ?? '') ?>" placeholder="name@domain.com">
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 12px;">
                Generate Password Reset Link ➔
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 13px; color: var(--color-text-muted);">
            Remember your password? 
            <a href="<?= url('login') ?>" style="color: var(--color-primary); font-weight: 600;">Back to Sign In</a>
        </div>
    </div>
</div>
