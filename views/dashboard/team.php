<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Agency Team & Agent Clearance</h1>
        <p class="dashboard-subtitle">Manage accredited brokers, branch managers, and associate agents affiliated with your corporate practice.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; align-items: start;">
    <div class="table-card">
        <div class="table-header">
            <h3 style="font-size: 18px; font-weight: 600;">Active Agency Team Members</h3>
            <span class="badge badge-primary"><?= count($teamMembers ?? []) ?> Accredited Members</span>
        </div>

        <?php if (empty($teamMembers)): ?>
            <div style="padding: 40px; text-align: center; color: var(--color-text-muted);">
                <h4>No team members enrolled yet</h4>
                <p>Use the form on the right to invite licensed agents and branch managers to your agency roster.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Role in Firm</th>
                        <th>Status</th>
                        <th>Enrolled</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teamMembers as $m): ?>
                        <tr>
                            <td>
                                <strong><?= e($m['full_name']) ?></strong><br>
                                <small style="color: var(--color-text-muted);"><?= e($m['email']) ?> | <?= e($m['phone'] ?? '') ?></small>
                            </td>
                            <td>
                                <span class="badge badge-gold">
                                    <?= ucwords(str_replace('_', ' ', $m['role_in_agency'] ?? 'associate_agent')) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-verified">
                                    <?= strtoupper($m['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($m['created_at'])) ?></td>
                            <td>
                                <form action="<?= url('dashboard/team/' . $m['user_id'] . '/remove') ?>" method="POST" onsubmit="return confirm('Remove agent from agency practice?');" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--color-danger); border-color: var(--color-danger);">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Invite Agent Card -->
    <div class="dashboard-card">
        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 16px; color: var(--color-primary);">Invite Team Member</h3>
        <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 20px;">
            Accredited associates can publish listings and receive direct buyer enquiries under your agency brand umbrella.
        </p>

        <form action="<?= url('dashboard/team/invite') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Associate Full Name</label>
                <input type="text" name="full_name" class="form-control" required placeholder="e.g. Tunde Williams">
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="tunde@agency.ng">
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="+234 800 000 0000">
            </div>

            <div class="form-group">
                <label class="form-label">Role in Agency</label>
                <select name="role_in_agency" class="form-control" required>
                    <option value="associate_agent">Associate Agent</option>
                    <option value="branch_manager">Branch Manager</option>
                    <option value="principal">Co-Principal Broker</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px;">
                ➕ Enroll Team Member
            </button>
        </form>
    </div>
</div>
