<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 24px; font-weight: 700; color: var(--color-primary); margin: 0 0 4px;">User Accounts, Roles & Security Clearance</h2>
        <p style="color: var(--color-text-muted); margin: 0;">Comprehensive oversight of licensed agents, agency principals, landlords, buyers, and administrative staff.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?= url('admin/export/users') ?>" class="btn btn-outline btn-sm">
            📥 Export Users CSV
        </a>
    </div>
</div>

<!-- Quick Metric Pills -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card" style="padding: 16px 20px;">
        <div style="font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase;">Total Users</div>
        <div style="font-size: 26px; font-weight: 800; color: var(--color-primary); margin-top: 4px;"><?= number_format($counts['total'] ?? count($users)) ?></div>
    </div>
    <div class="stat-card" style="padding: 16px 20px;">
        <div style="font-size: 12px; font-weight: 600; color: #10B981; text-transform: uppercase;">Active Accounts</div>
        <div style="font-size: 26px; font-weight: 800; color: #10B981; margin-top: 4px;"><?= number_format($counts['active'] ?? 0) ?></div>
    </div>
    <div class="stat-card" style="padding: 16px 20px;">
        <div style="font-size: 12px; font-weight: 600; color: var(--color-accent); text-transform: uppercase;">Licensed Agents</div>
        <div style="font-size: 26px; font-weight: 800; color: var(--color-accent); margin-top: 4px;"><?= number_format($counts['agents'] ?? 0) ?></div>
    </div>
    <div class="stat-card" style="padding: 16px 20px;">
        <div style="font-size: 12px; font-weight: 600; color: #3B82F6; text-transform: uppercase;">Verified Landlords</div>
        <div style="font-size: 26px; font-weight: 800; color: #3B82F6; margin-top: 4px;"><?= number_format($counts['landlords'] ?? 0) ?></div>
    </div>
    <div class="stat-card" style="padding: 16px 20px;">
        <div style="font-size: 12px; font-weight: 600; color: #EF4444; text-transform: uppercase;">Suspended</div>
        <div style="font-size: 26px; font-weight: 800; color: #EF4444; margin-top: 4px;"><?= number_format($counts['suspended'] ?? 0) ?></div>
    </div>
</div>

<!-- Search & Filter Toolbar -->
<div class="table-card" style="margin-bottom: 24px; padding: 20px;">
    <form action="<?= url('admin/users') ?>" method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto auto; gap: 12px; align-items: end;">
        <div>
            <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Search Entities</label>
            <input type="text" name="q" class="form-control" placeholder="Search by name, email or phone..." value="<?= e($filters['q'] ?? '') ?>">
        </div>
        <div>
            <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Role Tier</label>
            <select name="role" class="form-control">
                <option value="">-- All Roles --</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= e($r['name']) ?>" <?= ($filters['role'] ?? '') === $r['name'] ? 'selected' : '' ?>>
                        <?= e($r['label']) ?> (<?= e($r['name']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Account Status</label>
            <select name="status" class="form-control">
                <option value="">-- All Statuses --</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active Only</option>
                <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended Only</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 20px;">
                🔍 Apply Filter
            </button>
        </div>
        <?php if (!empty($filters['q']) || !empty($filters['role']) || !empty($filters['status'])): ?>
            <div>
                <a href="<?= url('admin/users') ?>" class="btn btn-outline" style="height: 42px; display: flex; align-items: center;">
                    ✕ Reset
                </a>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- User Accounts Table -->
<div class="table-card">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">Registered Accounts & Roles (<?= count($users) ?> results)</h3>
    </div>

    <?php if (empty($users)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 36px; margin-bottom: 12px;">👥</div>
            <h4>No user accounts match current filter criteria</h4>
            <p><a href="<?= url('admin/users') ?>">Clear search filters</a> to view all platform entities.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Contact & Verification</th>
                    <th>Current Role</th>
                    <th>Listings</th>
                    <th>Status</th>
                    <th>Change Role</th>
                    <th style="text-align: right;">Security Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $currentAdminId = \App\Core\Auth::id();
                foreach ($users as $u): 
                    $isSelf = ((int)$u['id'] === (int)$currentAdminId);
                ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #0F172A; color: var(--color-accent); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                    <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <strong style="color: var(--color-primary); font-size: 14px;"><?= e($u['full_name']) ?></strong>
                                    <?php if ($isSelf): ?>
                                        <span class="badge" style="background: rgba(212, 175, 55, 0.2); color: #B45309; font-size: 10px; margin-left: 4px;">YOU</span>
                                    <?php endif; ?>
                                    <div style="font-size: 11px; color: var(--color-text-muted); font-family: monospace;">ID: #<?= $u['id'] ?> | <?= date('M d, Y', strtotime($u['created_at'])) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 13px;">✉️ <?= e($u['email']) ?></div>
                            <div style="font-size: 12px; color: var(--color-text-muted);">
                                📞 <?= e($u['phone'] ?: '—') ?>
                                <?php if (!empty($u['email_verified_at'])): ?>
                                    <span title="Email Verified" style="color: #10B981; margin-left: 4px;">✓ Verified</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php 
                            $roleClass = match($u['role_name']) {
                                'super_admin' => 'background: #000B1A; color: var(--color-accent); border: 1px solid var(--color-accent);',
                                'moderator' => 'background: #3B82F6; color: #FFF;',
                                'agency_admin' => 'background: #8B5CF6; color: #FFF;',
                                'agent' => 'background: #10B981; color: #FFF;',
                                'developer' => 'background: #F59E0B; color: #FFF;',
                                'landlord' => 'background: #06B6D4; color: #FFF;',
                                default => 'background: #E2E8F0; color: #334155;'
                            };
                            ?>
                            <span class="badge" style="<?= $roleClass ?> font-size: 11px; padding: 4px 8px; font-weight: 700;">
                                <?= strtoupper($u['role_label'] ?? $u['role_name'] ?? 'Client') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-outline" style="font-weight: 600;">
                                <?= $u['total_listings'] ?> listings
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $u['status'] === 'active' ? 'badge-verified' : 'badge-gold' ?>" style="font-size: 11px;">
                                <?= strtoupper($u['status']) ?>
                            </span>
                        </td>
                        <td>
                            <form action="<?= url('admin/users/' . $u['id'] . '/role') ?>" method="POST" style="margin: 0; display: flex; gap: 4px; align-items: center;">
                                <?= csrf_field() ?>
                                <select name="role_name" class="form-control" style="font-size: 11px; padding: 4px 6px; height: 32px; width: 140px;">
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= e($r['name']) ?>" <?= ($u['role_name'] === $r['name']) ? 'selected' : '' ?>>
                                            <?= e($r['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-outline btn-sm" style="font-size: 11px; padding: 4px 8px; height: 32px;" title="Apply role modification">
                                    Set
                                </button>
                            </form>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px; align-items: center;">
                                <!-- Reset Password Modal Trigger -->
                                <button type="button" class="btn btn-outline btn-sm" style="font-size: 11px;" onclick="openPassResetModal(<?= $u['id'] ?>, '<?= e(addslashes($u['full_name'])) ?>', '<?= e(addslashes($u['email'])) ?>')">
                                    🔑 Reset Pass
                                </button>

                                <!-- Toggle Suspend Status -->
                                <form action="<?= url('admin/users/' . $u['id'] . '/toggle') ?>" method="POST" style="display: inline; margin: 0;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm <?= $u['status'] === 'active' ? 'btn-outline' : 'btn-primary' ?>" 
                                            style="font-size: 11px; <?= $u['status'] === 'active' ? 'color: var(--color-danger); border-color: var(--color-danger);' : '' ?>"
                                            <?= $isSelf ? 'disabled title="Cannot suspend yourself"' : '' ?>
                                            onclick="return confirm('<?= $u['status'] === 'active' ? 'Suspend access for this user?' : 'Reactivate account access for this user?' ?>');">
                                        <?= $u['status'] === 'active' ? 'Suspend' : 'Reactivate' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal: Reset User Password -->
<div id="passResetModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #FFF; width: 100%; max-width: 450px; border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-luxury); position: relative;">
        <h3 style="margin-top: 0; font-size: 18px; color: var(--color-primary);">Administrative Password Reset</h3>
        <p style="font-size: 13px; color: var(--color-text-muted);">
            Resetting password for: <strong id="resetTargetUser">User</strong>
        </p>

        <form id="resetPassForm" method="POST" action="">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="font-size: 12px;">Specify New Password (or leave empty to auto-generate)</label>
                <input type="text" name="new_password" class="form-control" placeholder="Leave empty for auto-generated high-entropy password">
                <small style="color: var(--color-text-muted); font-size: 11px; display: block; margin-top: 4px;">
                    Password will be hashed immediately with Argon2id.
                </small>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="closePassResetModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Confirm Reset ➔</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPassResetModal(userId, name, email) {
    document.getElementById('resetTargetUser').innerText = name + ' (' + email + ')';
    document.getElementById('resetPassForm').action = '<?= url('admin/users/') ?>' + userId + '/reset-password';
    document.getElementById('passResetModal').style.display = 'flex';
}
function closePassResetModal() {
    document.getElementById('passResetModal').style.display = 'none';
}
</script>
