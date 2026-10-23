<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Account Settings & Security Profile</h1>
        <p class="dashboard-subtitle">Manage personal credentials, update contact information, and configure enterprise security parameters.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; align-items: start;">
    <div>
        <!-- 1. Profile Information & Preferences -->
        <div class="dashboard-card" style="margin-bottom: 24px;">
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 20px; color: var(--color-primary);">Personal & Contact Details</h3>

            <form action="<?= url('dashboard/profile') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label">Full Legal Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address (Primary Identity)</label>
                    <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled style="background: #F1F5F9; cursor: not-allowed;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                        <small style="color: var(--color-text-muted);">Email changes require compliance desk authorization.</small>
                        <?php if (!empty($user['email_verified_at'])): ?>
                            <span class="badge badge-verified" style="font-size: 11px;">✓ Email Verified</span>
                        <?php else: ?>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <span class="badge badge-gold" style="font-size: 11px;">Pending Verification</span>
                                <button type="submit" formaction="<?= url('dashboard/verify-email-now') ?>" formmethod="POST" class="btn btn-sm btn-outline" style="padding: 2px 10px; font-size: 11px; border-color: #f59e0b; color: #b45309; background: #fffbeb;">Verify Now ✉</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone / WhatsApp Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="+234 800 000 0000">
                </div>

                <div class="form-group">
                    <label class="form-label">Profile Photo URL</label>
                    <input type="url" name="avatar_url" class="form-control" value="<?= e($user['avatar_url'] ?? '') ?>" placeholder="https://images.unsplash.com/...">
                </div>

                <div class="form-group">
                    <label class="form-label">Professional Bio / Investor Profile</label>
                    <textarea name="bio" class="form-control" rows="3" placeholder="Brief statement regarding your advisory portfolio or property requirements..."><?= e($user['bio'] ?? '') ?></textarea>
                </div>

                <hr style="border: none; border-top: 1px solid var(--color-border); margin: 28px 0;">

                <!-- Notification & Privacy Preferences -->
                <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 16px; color: var(--color-primary);">Communication & Privacy Controls</h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                    <div>
                        <strong style="font-size: 13px; display: block; margin-bottom: 8px;">Notifications</strong>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 6px; cursor: pointer;">
                            <input type="checkbox" name="notify_email" value="1" <?= !empty($user['notify_email']) ? 'checked' : '' ?>>
                            <span>Email Lead Alerts</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 6px; cursor: pointer;">
                            <input type="checkbox" name="notify_sms" value="1" <?= !empty($user['notify_sms']) ? 'checked' : '' ?>>
                            <span>SMS Tour Reminders</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="notify_marketing" value="1" <?= !empty($user['notify_marketing']) ? 'checked' : '' ?>>
                            <span>Market Intelligence Digest</span>
                        </label>
                    </div>

                    <div>
                        <strong style="font-size: 13px; display: block; margin-bottom: 8px;">Public Directory Privacy</strong>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 6px; cursor: pointer;">
                            <input type="checkbox" name="is_phone_public" value="1" <?= !empty($user['is_phone_public']) ? 'checked' : '' ?>>
                            <span>Display Phone on Listings</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="is_email_public" value="1" <?= !empty($user['is_email_public']) ? 'checked' : '' ?>>
                            <span>Display Email on Directory</span>
                        </label>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid var(--color-border); margin: 28px 0;">

                <!-- 2FA & Password Change -->
                <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 16px; color: var(--color-primary);">Security & Argon2id Password Management</h4>

                <div style="margin-bottom: 20px; background: #F8FAFC; border: 1px solid var(--color-border); padding: 16px; border-radius: var(--radius-md);">
                    <label style="display: flex; align-items: center; gap: 10px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" name="two_factor_enabled" value="1" <?= !empty($user['two_factor_enabled']) ? 'checked' : '' ?>>
                        <span>
                            <strong>Enforce Two-Factor Authentication (2FA)</strong><br>
                            <span style="color: var(--color-text-muted); font-size: 12px;">Require dual verification for sensitive transactions, withdrawals, and account logins.</span>
                        </span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">Current Password (Required for password changes)</label>
                    <input type="password" name="current_password" class="form-control" placeholder="••••••••">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">New Password (Min 8 characters)</label>
                        <input type="password" name="new_password" class="form-control" placeholder="New Password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control" placeholder="Confirm Password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="margin-top: 16px;">
                    Save Profile & Security Settings ➔
                </button>
            </form>
        </div>

        <!-- 2. Active Sessions & Device Revocation -->
        <div class="dashboard-card" style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 700; color: var(--color-primary);">Active Device Sessions</h3>
                <form action="<?= url('dashboard/sessions/revoke-others') ?>" method="POST" onsubmit="return confirm('Revoke all other active sessions across mobile and desktop devices?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--color-danger); border-color: var(--color-danger);">
                        🔒 Revoke All Other Sessions
                    </button>
                </form>
            </div>
            <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 16px;">
                Inspect hardware, IP addresses, and browsers currently authorized to access this account.
            </p>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Device / Browser</th>
                        <th>IP Address</th>
                        <th>Last Activity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $currentSessionId = session_id();
                    foreach (($activeSessions ?? []) as $sess): 
                        $isCurrent = ($sess['id'] === $currentSessionId);
                    ?>
                        <tr>
                            <td>
                                <strong><?= e(substr($sess['user_agent'] ?? 'Web Browser', 0, 45)) ?>...</strong>
                                <?php if ($isCurrent): ?>
                                    <span class="badge badge-verified" style="font-size: 10px;">Current Device</span>
                                <?php endif; ?>
                            </td>
                            <td><code><?= e($sess['ip_address'] ?? '127.0.0.1') ?></code></td>
                            <td><?= date('d M Y, H:i', (int)$sess['last_activity']) ?></td>
                            <td>
                                <?php if (!$isCurrent): ?>
                                    <form action="<?= url('dashboard/sessions/' . $sess['id'] . '/revoke') ?>" method="POST" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline btn-sm" style="color: var(--color-danger);">
                                            Revoke
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: var(--color-success);">Active Now</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 3. Account Deletion Request (GDPR / NDPR Compliance) -->
        <div class="dashboard-card" style="border: 1px solid #FCA5A5; background: #FFF5F5;">
            <h4 style="font-size: 16px; font-weight: 700; color: #DC2626; margin-bottom: 8px;">Request Account Deletion & Right to be Forgotten</h4>
            <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 16px;">
                In accordance with Nigeria Data Protection Regulation (NDPR) and international privacy statutes, you may submit a verified request to permanently purge personal data.
            </p>
            <form action="<?= url('dashboard/request-deletion') ?>" method="POST" onsubmit="return confirm('Are you certain you wish to submit an account deletion request? Compliance will audit your pending transactions.');">
                <?= csrf_field() ?>
                <div class="form-group">
                    <input type="text" name="reason" class="form-control" placeholder="Reason for account closure (optional)" style="background: #FFFFFF;">
                </div>
                <button type="submit" class="btn btn-outline btn-sm" style="color: #DC2626; border-color: #DC2626;">
                    Submit Account Deletion Request
                </button>
            </form>
        </div>
    </div>

    <!-- Security & Clearance Sidebar -->
    <div>
        <div class="dashboard-card" style="text-align: center; margin-bottom: 24px;">
            <img src="<?= e($user['avatar_url'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80') ?>" alt="<?= e($user['full_name']) ?>" style="width: 100px; height: 100px; border-radius: var(--radius-full); object-fit: cover; margin: 0 auto 16px; border: 3px solid var(--color-surface); box-shadow: var(--shadow-card);">
            <h4 style="font-size: 18px; margin-bottom: 4px;"><?= e($user['full_name']) ?></h4>
            <div style="font-size: 12px; color: var(--color-accent); font-weight: 700; text-transform: uppercase; margin-bottom: 12px;">
                <?= e(auth_user()['roles'][0] ?? 'Client') ?>
            </div>
            <div class="badge badge-verified" style="margin-bottom: 16px;">
                ✓ Account Active & Verified
            </div>
            <div style="font-size: 12px; color: var(--color-text-muted); margin-bottom: 4px;">
                Member since <?= date('M Y', strtotime($user['created_at'])) ?>
            </div>
            <div style="font-size: 11px; color: var(--color-text-muted);">
                Consented: <?= date('d M Y', strtotime($user['terms_accepted_at'] ?? $user['created_at'])) ?>
            </div>
        </div>

        <!-- Recent Audit Trail -->
        <div class="dashboard-card">
            <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 12px; color: var(--color-primary);">My Security Audit Trail</h4>
            <p style="font-size: 12px; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 16px;">
                Immutable cryptographic ledger of sensitive actions performed on your identity:
            </p>

            <?php if (empty($recentAuditLogs)): ?>
                <div style="font-size: 12px; color: var(--color-text-muted);">No security events recorded.</div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($recentAuditLogs as $log): ?>
                        <div style="font-size: 12px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
                            <div style="font-weight: 600; color: var(--color-text);"><?= e(ucwords(str_replace('_', ' ', $log['action']))) ?></div>
                            <div style="color: var(--color-text-muted); font-size: 11px;"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <a href="<?= url('logout') ?>" class="btn btn-outline btn-sm" style="width: 100%; margin-top: 20px;">
                Safely Terminate Session
            </a>
        </div>
    </div>
</div>
