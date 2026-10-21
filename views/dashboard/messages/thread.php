<?php
$currentUserId = \App\Core\Auth::id();
$otherName = ($activeConversation['user_one_id'] == $currentUserId) ? $activeConversation['user_two_name'] : $activeConversation['user_one_name'];
$otherEmail = ($activeConversation['user_one_id'] == $currentUserId) ? $activeConversation['user_two_email'] : $activeConversation['user_one_email'];
?>

<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <a href="<?= url('dashboard/messages') ?>" style="color: var(--color-primary); font-size: 13px; font-weight: 600; text-decoration: none;">
            ← Back to All Conversations
        </a>
        <h2 style="font-size: 22px; margin-top: 6px;">Conversation with <?= e($otherName) ?></h2>
    </div>
    <?php if (!empty($activeConversation['property_title'])): ?>
        <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 8px 16px; text-align: right;">
            <div style="font-size: 11px; color: var(--color-text-muted); text-transform: uppercase;">Regarding Property</div>
            <a href="<?= url('properties/' . $activeConversation['property_slug']) ?>" target="_blank" style="color: var(--color-primary); font-weight: 600; font-size: 13px;">
                <?= e($activeConversation['property_title']) ?>
            </a>
            <div style="font-size: 12px; color: var(--color-secondary);"><?= format_money($activeConversation['property_price']) ?></div>
        </div>
    <?php endif; ?>
</div>

<div style="background: #FFFFFF; border-radius: var(--radius-md); border: 1px solid var(--color-border); display: flex; flex-direction: column; height: 580px;">
    <!-- Message History Stream -->
    <div style="flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 16px; background: #F8FAFC;" id="messageStream">
        <?php if (empty($messages)): ?>
            <div style="margin: auto; text-align: center; color: var(--color-text-muted);">
                <div style="font-size: 32px; margin-bottom: 8px;">👋</div>
                <p>This is the start of your message thread with <strong><?= e($otherName) ?></strong>.</p>
            </div>
        <?php else: ?>
            <?php foreach ($messages as $msg): 
                $isMe = ((int)$msg['sender_id'] === (int)$currentUserId);
            ?>
                <div style="display: flex; flex-direction: column; align-items: <?= $isMe ? 'flex-end' : 'flex-start' ?>;">
                    <div style="font-size: 11px; color: var(--color-text-muted); margin-bottom: 4px;">
                        <?= $isMe ? 'You' : e($msg['sender_name']) ?> • <?= date('M d, h:i A', strtotime($msg['created_at'])) ?>
                    </div>
                    <div style="max-width: 70%; padding: 12px 16px; border-radius: 12px; font-size: 14px; line-height: 1.5; <?= $isMe ? 'background: var(--color-primary); color: #FFFFFF; border-bottom-right-radius: 2px;' : 'background: #FFFFFF; color: #0F172A; border: 1px solid var(--color-border); border-bottom-left-radius: 2px;' ?>">
                        <?= nl2br(e($msg['message'])) ?>
                    </div>
                    <?php if ($isMe): ?>
                        <div style="font-size: 10px; color: var(--color-text-muted); margin-top: 2px;">
                            <?= $msg['is_read'] ? '✓✓ Read' : '✓ Delivered' ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Message Input Box -->
    <div style="padding: 16px 20px; border-top: 1px solid var(--color-border); background: #FFFFFF;">
        <form action="<?= url('dashboard/messages/' . $activeConversation['id'] . '/send') ?>" method="POST" style="display: flex; gap: 12px; margin: 0;">
            <?= csrf_field() ?>
            <textarea name="message" rows="2" class="form-control" placeholder="Type your reply to <?= e($otherName) ?>..." required style="resize: none; font-size: 14px;"></textarea>
            <button type="submit" class="btn btn-primary" style="padding: 0 24px; align-self: flex-end; height: 54px; font-weight: 600;">
                Send Reply
            </button>
        </form>
    </div>
</div>

<script>
    // Scroll chat stream to bottom on load
    const stream = document.getElementById('messageStream');
    if (stream) {
        stream.scrollTop = stream.scrollHeight;
    }
</script>
