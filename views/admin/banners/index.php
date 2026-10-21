<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end;">
    <div>
        <h2 style="font-size: 24px;">Banner Advertisements & Placement System</h2>
        <p style="color: var(--color-text-muted);">Configure monetization ad slots, manage campaigns, and track impression/click-through performance.</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="document.getElementById('bannerModal').style.display='flex'">
        + Create Advertisement
    </button>
</div>

<div class="table-card">
    <?php if (empty($banners)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">📢</div>
            <h4>No Active Banner Campaigns</h4>
            <p>Monetized advertising placements will appear here.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Advertiser & Title</th>
                    <th>Placement Slot</th>
                    <th>Target Destination</th>
                    <th>Flight Dates</th>
                    <th>Performance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($banners as $b): ?>
                    <tr>
                        <td>
                            <strong><?= e($b['title']) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= e($b['advertiser_name']) ?></small>
                        </td>
                        <td>
                            <span class="badge badge-primary" style="text-transform: capitalize;">
                                <?= str_replace('_', ' ', $b['placement']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= e($b['target_url']) ?>" target="_blank" style="color: var(--color-primary); font-size: 13px;">
                                <?= e(substr($b['target_url'], 0, 30)) ?>...
                            </a>
                        </td>
                        <td>
                            <small><?= date('M d, Y', strtotime($b['starts_at'])) ?> to <?= date('M d, Y', strtotime($b['ends_at'])) ?></small>
                        </td>
                        <td>
                            <strong><?= number_format($b['impressions_count']) ?></strong> views<br>
                            <small style="color: var(--color-success); font-weight: 600;"><?= number_format($b['clicks_count']) ?> clicks</small>
                        </td>
                        <td>
                            <span class="badge <?= !empty($b['is_active']) ? 'badge-verified' : 'badge-gold' ?>">
                                <?= !empty($b['is_active']) ? 'ACTIVE' : 'PAUSED' ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Create Banner Modal -->
<div id="bannerModal" role="dialog" aria-modal="true" aria-labelledby="bannerModalTitle" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: var(--radius-md); width: 100%; max-width: 550px; padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="bannerModalTitle" style="font-size: 20px; font-weight: 700;">Create Banner Advertisement</h3>
            <button type="button" aria-label="Close modal" onclick="document.getElementById('bannerModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer;">✕</button>
        </div>
        <form action="<?= url('admin/banners/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Advertiser / Brand Name *</label>
                <input type="text" name="advertiser_name" class="form-control" placeholder="e.g. Stanbic IBTC Home Loans" required>
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Campaign Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Low-Rate Mortgage Financing 2026" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Placement Slot *</label>
                    <select name="placement" class="form-control" required>
                        <option value="homepage_top">Homepage Hero Top Strip</option>
                        <option value="search_sidebar">Search Results Sidebar</option>
                        <option value="property_detail">Property Spec Sheet Inline</option>
                        <option value="footer_strip">Global Footer Strip</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Target Destination URL *</label>
                    <input type="url" name="target_url" class="form-control" placeholder="https://brand.com/campaign" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Banner Image URL *</label>
                <input type="url" name="image_url" class="form-control" placeholder="https://cdn.example.com/banner-728x90.jpg" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div class="form-group">
                    <label class="form-label">Start Date *</label>
                    <input type="datetime-local" name="starts_at" class="form-control" required value="<?= date('Y-m-d\TH:i') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">End Date *</label>
                    <input type="datetime-local" name="ends_at" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime('+30 days')) ?>">
                </div>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('bannerModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Publish Advertisement</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('bannerModal');
        if (modal) modal.style.display = 'none';
    }
});
</script>
