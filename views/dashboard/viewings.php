<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px; font-weight: 700; color: var(--color-primary);">
        <?= !empty($isClient) ? 'My Scheduled Property Tours & Inspections' : 'Inspection Tours & Viewing Calendar' ?>
    </h2>
    <p style="color: var(--color-text-muted);">
        <?= !empty($isClient) 
            ? 'Track your upcoming walkthroughs. You can communicate directly with the Afriserva Admin Desk before or after viewing any property.' 
            : 'Coordinate accompanied physical walkthroughs, update tour stages, and conduct live virtual inspections.' ?>
    </p>
</div>

<!-- Direct Admin Advisory Banner for Prospective Buyers -->
<?php if (!empty($isClient)): ?>
    <div style="background: linear-gradient(135deg, #F0FDF4 0%, #ECFDF5 100%); border: 1px solid #A7F3D0; border-radius: var(--radius-md); padding: 18px 24px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
        <div style="display: flex; gap: 14px; align-items: flex-start;">
            <div style="font-size: 28px;">🛡️</div>
            <div>
                <h4 style="font-size: 15px; font-weight: 700; color: #065F46; margin-bottom: 4px;">
                    Direct Administrator Assistance Before & After Viewing
                </h4>
                <p style="font-size: 13px; color: #047857; margin: 0; line-height: 1.5;">
                    Need official accompaniment, title document verification before your visit, or ready to submit a formal offer after inspecting? The Afriserva Admin Desk is available to guide you at every step.
                </p>
            </div>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?= url('dashboard/contact-admin') ?>" class="btn btn-primary btn-sm" style="background: #065F46; border-color: #065F46; color: #FFF; font-weight: 600;">
                💬 Message Admin Desk
            </a>
            <a href="https://wa.me/2348002374737?text=<?= urlencode('Hello Afriserva Admin Desk, I need assistance regarding my property inspection tour.') ?>" target="_blank" class="btn btn-outline btn-sm" style="border-color: #059669; color: #065F46; font-weight: 600;">
                📞 Concierge WhatsApp
            </a>
        </div>
    </div>
<?php endif; ?>

<div class="table-card">
    <?php if (empty($appointments)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">📅</div>
            <h4><?= !empty($isClient) ? 'You have no scheduled inspection tours yet' : 'No Scheduled Inspections' ?></h4>
            <p>
                <?= !empty($isClient) 
                    ? 'Explore properties and click "Schedule Inspection Tour" on any listing to book accompanied walkthroughs.' 
                    : 'Direct tour bookings made by prospective buyers will appear here.' ?>
            </p>
            <?php if (!empty($isClient)): ?>
                <a href="<?= url('properties') ?>" class="btn btn-primary btn-sm" style="margin-top: 12px;">
                    🔍 Browse Verified Properties
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Appointment Date</th>
                    <th>Time Window</th>
                    <th>Property Title</th>
                    <?php if (empty($isClient)): ?>
                        <th>Client Details</th>
                    <?php endif; ?>
                    <th>Tour Format</th>
                    <th>Status</th>
                    <th style="text-align: right;">
                        <?= !empty($isClient) ? 'Admin Support & Actions' : 'Inspection Actions' ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $app): ?>
                    <tr>
                        <td><strong><?= date('l, M d, Y', strtotime($app['appointment_date'])) ?></strong></td>
                        <td><?= date('h:i A', strtotime($app['appointment_time'])) ?></td>
                        <td>
                            <a href="<?= url('properties/' . $app['property_slug']) ?>" target="_blank" style="color: var(--color-primary); font-weight: 600;">
                                <?= e($app['property_title']) ?>
                            </a><br>
                            <small style="color: var(--color-text-muted);"><?= e($app['address']) ?></small>
                        </td>
                        <?php if (empty($isClient)): ?>
                            <td>
                                <strong><?= e($app['client_name']) ?></strong><br>
                                <small style="color: #64748B; font-family: monospace;">🔒 <?= e($app['client_phone']) ?></small>
                            </td>
                        <?php endif; ?>
                        <td>
                            <span class="badge <?= $app['viewing_type'] === 'physical' ? 'badge-primary' : 'badge-gold' ?>">
                                <?= $app['viewing_type'] === 'physical' ? '🏢 In-Person' : '📹 Guided Video' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $app['status'] === 'confirmed' ? 'badge-verified' : ($app['status'] === 'completed' ? 'badge-primary' : 'badge-gold') ?>">
                                <?= strtoupper($app['status']) ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <?php if (!empty($isClient)): ?>
                                <!-- Buyer Direct Actions with Admin Desk Before & After Viewing -->
                                <div style="display: inline-flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap;">
                                    <a href="<?= url('properties/' . $app['property_id'] . '/contact-admin') ?>" class="btn btn-outline btn-sm" title="Contact Admin regarding this viewing" style="font-size: 11px;">
                                        💬 Chat with Admin
                                    </a>
                                    <form action="<?= url('properties/' . $app['property_id'] . '/initiate-transaction') ?>" method="POST" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-primary btn-sm" style="font-size: 11px; background: var(--color-accent); color: #000B1A; border: none; font-weight: 700;">
                                            🏛️ Make Offer
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <!-- Agent Inspection Tour Controls -->
                                <form action="<?= url('dashboard/viewings/' . $app['id'] . '/status') ?>" method="POST" style="display: inline-flex; gap: 6px; justify-content: flex-end;">
                                    <?= csrf_field() ?>
                                    <?php if ($app['status'] === 'pending'): ?>
                                        <button type="submit" name="status" value="confirmed" class="btn btn-primary btn-sm" style="background: var(--color-success); border-color: var(--color-success);">
                                            ✓ Confirm
                                        </button>
                                        <button type="submit" name="status" value="cancelled" class="btn btn-outline btn-sm" style="color: var(--color-danger);">
                                            ✕ Decline
                                        </button>
                                    <?php elseif ($app['status'] === 'confirmed'): ?>
                                        <button type="submit" name="status" value="completed" class="btn btn-outline btn-sm" style="color: var(--color-success); border-color: var(--color-success);">
                                            ✓ Mark Completed
                                        </button>
                                        <button type="submit" name="status" value="rescheduled" class="btn btn-outline btn-sm">
                                            🔄 Reschedule
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="status" value="confirmed" class="btn btn-outline btn-sm">
                                            Re-open Tour
                                        </button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
