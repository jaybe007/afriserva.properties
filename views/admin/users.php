<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px;">User Account Clearances & Security Oversight</h2>
    <p style="color: var(--color-text-muted);">Manage platform accounts, licensed agents, private landlords, and role permissions.</p>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Account ID</th>
                <th>Full Name</th>
                <th>Email Address</th>
                <th>Phone Number</th>
                <th>Role Tier</th>
                <th>Listings</th>
                <th>Status</th>
                <th>Security Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><code>#<?= $u['id'] ?></code></td>
                    <td><strong><?= e($u['full_name']) ?></strong></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['phone'] ?? '—') ?></td>
                    <td><span class="badge badge-primary"><?= strtoupper($u['role_name'] ?? 'User') ?></span></td>
                    <td><?= $u['total_listings'] ?> properties</td>
                    <td>
                        <span class="badge <?= $u['status'] === 'active' ? 'badge-verified' : 'badge-gold' ?>">
                            <?= strtoupper($u['status']) ?>
                        </span>
                    </td>
                    <td>
                        <form action="<?= url('admin/users/' . $u['id'] . '/toggle') ?>" method="POST" style="display: inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm <?= $u['status'] === 'active' ? 'btn-outline' : 'btn-primary' ?>" style="<?= $u['status'] === 'active' ? 'color: var(--color-danger); border-color: var(--color-danger);' : '' ?>">
                                <?= $u['status'] === 'active' ? 'Suspend Account' : 'Reactivate' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
