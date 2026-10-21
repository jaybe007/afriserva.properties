<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end;">
    <div>
        <h2 style="font-size: 24px;">In-App Notifications</h2>
        <p style="color: var(--color-text-muted);">Real-time alerts for lead inquiries, tour requests, verified payments, and tenancy updates.</p>
    </div>
    <?php if ($unreadCount > 0): ?>
        <form action="<?= url('dashboard/notifications/mark-all-read') ?>" method="POST" style="margin: 0;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline btn-sm">
                ✓ Mark All As Read
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="table-card">
    <?php if (empty($notifications)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">🔔</div>
            <h4>You're All Caught Up</h4>
            <p>No new notifications at this time.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column;">
            <?php foreach ($notifications as $notif): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--color-border); <?= empty($notif['is_read']) ? 'background: #F8FAFC;' : '' ?>">
                    <div style="display: flex; gap: 14px; align-items: center;">
                        <div style="font-size: 24px;">
                            <?php 
                            switch ($notif['type']) {
                                case 'enquiry': echo '✉️'; break;
                                case 'message': echo '💬'; break;
                                case 'viewing': echo '📅'; break;
                                case 'payment': echo '💳'; break;
                                case 'lease': echo '📜'; break;
                                case 'maintenance': echo '🔧'; break;
                                default: echo '🔔'; break;
                            }
                            ?>
                        </div>
                        <div>
                            <div style="font-weight: <?= empty($notif['is_read']) ? '700' : '600' ?>; font-size: 15px; color: #0F172A;">
                                <?= e($notif['title']) ?>
                                <?php if (empty($notif['is_read'])): ?>
                                    <span class="badge badge-gold" style="font-size: 10px; margin-left: 6px;">NEW</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 13px; color: var(--color-secondary); margin-top: 3px;">
                                <?= e($notif['message']) ?>
                            </div>
                            <small style="color: var(--color-text-muted); font-size: 11px;">
                                <?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <?php if (!empty($notif['link_url'])): ?>
                            <a href="<?= e($notif['link_url']) ?>" class="btn btn-outline btn-sm">
                                View Details →
                            </a>
                        <?php endif; ?>
                        <?php if (empty($notif['is_read'])): ?>
                            <form action="<?= url('dashboard/notifications/' . $notif['id'] . '/read') ?>" method="POST" style="margin: 0;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline btn-sm" title="Mark as read">
                                    ✓
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
