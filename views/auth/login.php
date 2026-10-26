<div class="container" style="max-width: 480px; padding: 60px 24px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 40px; box-shadow: var(--shadow-luxury);">
        <div style="text-align: center; margin-bottom: 32px;">
            <div class="brand-icon" style="margin: 0 auto 16px;">A</div>
            <h1 style="font-size: 26px; margin-bottom: 8px;">Access Your Portal</h1>
            <p style="font-size: 14px; color: var(--color-text-muted);">
                Sign in to manage your real estate portfolio, appointments, and client leads.
            </p>
        </div>

        <?php if (!empty($errors['auth'])): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;"><?= e($errors['auth']) ?></div>
        <?php endif; ?>

        <form action="<?= url('login') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="<?= e($redirect ?? '') ?>">

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" id="login-email" name="email" class="form-control" required value="<?= e($email ?? '') ?>" placeholder="name@domain.com">
                <?php if (!empty($errors['email'])): ?>
                    <small style="color: var(--color-danger); font-size: 12px;"><?= e($errors['email']) ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label" style="margin-bottom: 0;">Password</label>
                    <a href="<?= url('forgot-password') ?>" style="font-size: 12px; color: var(--color-primary); font-weight: 600;">Forgot Password?</a>
                </div>
                <input type="password" id="login-password" name="password" class="form-control" required placeholder="••••••••">
                <?php if (!empty($errors['password'])): ?>
                    <small style="color: var(--color-danger); font-size: 12px;"><?= e($errors['password']) ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 8px;">
                Secure Sign In ➔
            </button>
        </form>

        <!-- Quick 1-Click Test Persona Logins -->
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--color-border);">
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-primary); font-weight: 700; margin-bottom: 6px; text-align: center;">
                🔑 Universal Test Credentials
            </div>
            <div style="font-size: 12px; color: var(--color-text-muted); text-align: center; margin-bottom: 12px;">
                Password for all accounts: <strong style="font-family: monospace; color: var(--color-primary); background: #F1F5F9; padding: 2px 6px; border-radius: 4px;">Password123!</strong>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="setDemo('admin@afriserva.com')">
                    👑 Super Admin
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="setDemo('officer@afriserva.com')">
                    🛡️ Transaction Officer
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="setDemo('seller@afriserva.com')">
                    🏷️ Property Seller
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="setDemo('agent@afriserva.com')">
                    🏢 Licensed Agent
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="setDemo('buyer@afriserva.com')">
                    👤 Buyer / Client
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="setDemo('landlord@afriserva.com')">
                    🏠 Landlord
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="setDemo('tenant@afriserva.com')">
                    📑 Tenant
                </button>
                <button type="button" class="btn btn-primary btn-sm" style="background: var(--color-primary);" onclick="setDemo('test@afriserva.com')">
                    ⚡ Master Test (Admin)
                </button>
            </div>
        </div>
        <script>
        function setDemo(email) {
            document.getElementById('login-email').value = email;
            document.getElementById('login-password').value = 'Password123!';
        }
        </script>

        <div style="text-align: center; margin-top: 24px; font-size: 13px; color: var(--color-text-muted);">
            Don't have an account yet? 
            <a href="<?= url('register') ?>" style="color: var(--color-primary); font-weight: 600;">Create Account</a>
        </div>
    </div>
</div>

