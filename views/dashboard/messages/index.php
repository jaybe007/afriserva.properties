<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end;">
    <div>
        <h2 style="font-size: 24px;">Direct Messages & Inquiries</h2>
        <p style="color: var(--color-text-muted);">Real-time conversations between prospective clients, agents, landlords, and property managers.</p>
    </div>
    <?php if ($unreadCount > 0): ?>
        <span class="badge badge-gold" style="font-size: 13px; padding: 6px 12px;">
            <?= $unreadCount ?> Unread Message<?= $unreadCount > 1 ? 's' : '' ?>
        </span>
    <?php endif; ?>
</div>

<div class="table-card">
    <?php if (empty($conversations)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">💬</div>
            <h4>No Active Conversations</h4>
            <p>Direct inquiries submitted on listings will create communication threads here.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Participant</th>
                    <th>Associated Property</th>
                    <th>Latest Message</th>
                    <th>Last Active</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($conversations as $conv): 
                    $currentUserId = \App\Core\Auth::id();
                    $otherName = ($conv['user_one_id'] == $currentUserId) ? $conv['user_two_name'] : $conv['user_one_name'];
                    $otherRole = ($conv['user_one_id'] == $currentUserId) ? $conv['user_two_role'] : $conv['user_one_role'];
                ?>
                    <tr style="<?= !empty($conv['unread_count']) ? 'background: #F8FAFC; font-weight: 600;' : '' ?>">
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--color-primary); color: #FFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;">
                                    <?= strtoupper(substr($otherName, 0, 1)) ?>
                                </div>
                                <div>
                                    <div><?= e($otherName) ?></div>
                                    <small style="color: var(--color-text-muted); text-transform: uppercase; font-size: 10px;"><?= e($otherRole) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($conv['property_title'])): ?>
                                <a href="<?= url('properties/' . $conv['property_slug']) ?>" target="_blank" style="color: var(--color-primary); font-size: 13px;">
                                    <?= e($conv['property_title']) ?>
                                </a><br>
                                <small style="color: var(--color-text-muted);"><?= format_money($conv['property_price']) ?></small>
                            <?php else: ?>
                                <span style="color: var(--color-text-muted); font-size: 12px;">Direct Consultation</span>
                            <?php endif; ?>
                        </td>
                        <td style="max-width: 300px; color: var(--color-secondary); font-size: 13px;">
                            <?= e(substr($conv['last_message_snippet'] ?? 'Conversation initiated', 0, 60)) ?>...
                        </td>
                        <td>
                            <small style="color: var(--color-text-muted);">
                                <?= date('M d, H:i', strtotime($conv['last_message_at'])) ?>
                            </small>
                            <?php if (!empty($conv['unread_count'])): ?>
                                <span class="badge badge-gold" style="font-size: 10px; margin-left: 6px;"><?= $conv['unread_count'] ?> NEW</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <a href="<?= url('dashboard/messages/' . $conv['id']) ?>" class="btn btn-primary btn-sm">
                                Open Chat →
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
