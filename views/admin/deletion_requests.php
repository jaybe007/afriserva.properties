<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Account Deletion & Data Privacy Requests</h1>
        <p class="dashboard-subtitle">Section 11 Compliance: Manage statutory Right-to-be-Forgotten requests under Nigeria Data Protection Regulation (NDPR).</p>
    </div>
</div>

<div class="table-card">
    <div class="table-header">
        <h3 style="font-size: 18px; font-weight: 600;">Pending Deletion & Anonymization Requests</h3>
        <span class="badge badge-gold"><?= count($requests) ?> Pending Tickets</span>
    </div>

    <?php if (empty($requests)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">🛡️</div>
            <h4>No Pending Account Deletion Requests</h4>
            <p>All privacy requests have been reviewed and adjudicated by compliance officers.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Requester</th>
                    <th>Reason Given</th>
                    <th>Active Listings</th>
                    <th>Status</th>
                    <th>Date Requested</th>
                    <th style="text-align: right;">Adjudication Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td>
                            <strong><?= e($req['full_name']) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= e($req['email']) ?></small>
                        </td>
                        <td style="max-width: 280px;">
                            <small><?= e($req['reason']) ?></small>
                        </td>
                        <td>
                            <span class="badge badge-primary"><?= (int)$req['active_listings'] ?> Active</span>
                        </td>
                        <td>
                            <span class="badge <?= $req['status'] === 'approved' ? 'badge-verified' : ($req['status'] === 'rejected' ? 'badge-gold' : 'badge-primary') ?>">
                                <?= strtoupper($req['status']) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y, H:i', strtotime($req['created_at'])) ?></td>
                        <td style="text-align: right;">
                            <form action="<?= url('admin/deletion-requests/' . $req['id'] . '/process') ?>" method="POST" style="display: inline-flex; gap: 6px; justify-content: flex-end;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="user_id" value="<?= $req['user_id'] ?>">
                                <button type="submit" name="decision" value="approve" class="btn btn-primary btn-sm" style="background: var(--color-danger); border-color: var(--color-danger);" onclick="return confirm('Approve account deletion and anonymize user personal data?');">
                                    ✓ Approve & Purge
                                </button>
                                <button type="submit" name="decision" value="reject" class="btn btn-outline btn-sm">
                                    ✕ Reject
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
