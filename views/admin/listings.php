<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px;">Marketplace Property Moderation & Verification</h2>
    <p style="color: var(--color-text-muted);">Inspect submitted legal documents, approve listings for public discovery, or remove non-compliant records.</p>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Ref</th>
                <th>Title</th>
                <th>Agent</th>
                <th>Location</th>
                <th>Price</th>
                <th>Status</th>
                <th>Featured</th>
                <th>Moderation Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($properties as $p): ?>
                <tr>
                    <td style="width: 70px;">
                        <img src="<?= e($p['cover_image']) ?>" alt="Property" style="width: 55px; height: 40px; object-fit: cover; border-radius: 4px;">
                    </td>
                    <td><code><?= e($p['reference_code']) ?></code></td>
                    <td>
                        <a href="<?= url('properties/' . $p['slug']) ?>" target="_blank" style="color: var(--color-primary); font-weight: 600;">
                            <?= e($p['title']) ?>
                        </a>
                    </td>
                    <td><?= e($p['agent_name']) ?></td>
                    <td><?= e($p['city_name']) ?>, <?= e($p['state_name']) ?></td>
                    <td><strong><?= format_money($p['price']) ?></strong></td>
                    <td>
                        <span class="badge <?= $p['status'] === 'published' ? 'badge-verified' : ($p['status'] === 'rejected' ? 'badge-primary' : 'badge-gold') ?>">
                            <?= strtoupper($p['status']) ?>
                        </span>
                    </td>
                    <td>
                        <form action="<?= url('admin/listings/' . $p['id'] . '/status') ?>" method="POST" style="display: inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle_featured">
                            <button type="submit" class="btn btn-sm <?= $p['is_featured'] ? 'btn-accent' : 'btn-outline' ?>">
                                <?= $p['is_featured'] ? '★ Featured' : '☆ Standard' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <?php if ($p['status'] !== 'published'): ?>
                                <form action="<?= url('admin/listings/' . $p['id'] . '/status') ?>" method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($p['status'] !== 'rejected'): ?>
                                <form action="<?= url('admin/listings/' . $p['id'] . '/status') ?>" method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--color-danger); border-color: var(--color-danger);">Reject</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
