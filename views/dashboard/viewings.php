<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px;">Inspection Tours & Viewing Calendar</h2>
    <p style="color: var(--color-text-muted);">Coordinate accompanied physical walkthroughs, update tour stages, and conduct live virtual inspections.</p>
</div>

<div class="table-card">
    <?php if (empty($appointments)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">📅</div>
            <h4>No Scheduled Inspections</h4>
            <p>Direct tour bookings made by prospective investors will appear here.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Appointment Date</th>
                    <th>Time Window</th>
                    <th>Property Title</th>
                    <th>Client Information</th>
                    <th>Tour Format</th>
                    <th>Status</th>
                    <th style="text-align: right;">Inspection Actions</th>
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
                        <td>
                            <strong><?= e($app['client_name']) ?></strong><br>
                            <small><?= e($app['client_phone']) ?></small>
                        </td>
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
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
