<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px;">Tenancy & Rental Applications</h2>
    <p style="color: var(--color-text-muted);">Manage prospective tenant applications, conduct lawful background & guarantor screening, and approve tenancy leases.</p>
</div>

<div class="table-card">
    <?php if (empty($applications)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">📑</div>
            <h4>No Rental Applications Found</h4>
            <p>Submitted rental applications will appear here for verification and approval.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date Applied</th>
                    <th>Property Listing</th>
                    <th><?= $viewType === 'landlord' ? 'Applicant & Employment' : 'Status' ?></th>
                    <th>Guarantor Reference</th>
                    <th>Screening Status</th>
                    <th style="text-align: right;">Application Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td>
                            <strong><?= date('M d, Y', strtotime($app['created_at'])) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= date('h:i A', strtotime($app['created_at'])) ?></small>
                        </td>
                        <td>
                            <a href="<?= url('properties/' . $app['property_slug']) ?>" target="_blank" style="color: var(--color-primary); font-weight: 600;">
                                <?= e($app['property_title']) ?>
                            </a><br>
                            <small style="color: var(--color-text-muted);"><?= format_money($app['property_price']) ?> / year</small>
                        </td>
                        <td>
                            <?php if ($viewType === 'landlord'): ?>
                                <strong><?= e($app['applicant_name']) ?></strong><br>
                                <small>💼 <?= e($app['employer_name']) ?> (<?= e($app['employment_status']) ?>)</small><br>
                                <small style="color: var(--color-success); font-weight: 600;">Income: <?= format_money($app['annual_income']) ?>/yr</small>
                            <?php else: ?>
                                <span class="badge <?= $app['status'] === 'approved' ? 'badge-verified' : ($app['status'] === 'rejected' ? 'badge-gold' : 'badge-primary') ?>">
                                    <?= strtoupper($app['status']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($app['guarantor_name']) ?></strong> (<?= e($app['guarantor_relationship']) ?>)<br>
                            <small>📞 <?= e($app['guarantor_phone']) ?></small><br>
                            <small>✉️ <?= e($app['guarantor_email']) ?></small>
                        </td>
                        <td>
                            <span class="badge <?= $app['screening_status'] === 'passed' ? 'badge-verified' : ($app['screening_status'] === 'flagged' ? 'badge-gold' : 'badge-outline') ?>">
                                <?= strtoupper($app['screening_status']) ?>
                            </span>
                            <?php if (!empty($app['screening_notes'])): ?>
                                <div style="font-size: 11px; color: var(--color-secondary); margin-top: 4px;">
                                    <?= e($app['screening_notes']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if ($viewType === 'landlord'): ?>
                                <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                    <?php if ($app['screening_status'] === 'pending'): ?>
                                        <form action="<?= url('dashboard/rental-applications/' . $app['id'] . '/screening') ?>" method="POST" style="margin: 0;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="screening_status" value="passed">
                                            <input type="hidden" name="screening_notes" value="Verified income & guarantor identity successfully.">
                                            <button type="submit" class="btn btn-outline btn-sm" style="color: var(--color-info);">
                                                ✓ Verify
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($app['status'] === 'submitted' || $app['status'] === 'screening'): ?>
                                        <form action="<?= url('dashboard/rental-applications/' . $app['id'] . '/status') ?>" method="POST" style="margin: 0;">
                                            <?= csrf_field() ?>
                                            <button type="submit" name="status" value="approved" class="btn btn-primary btn-sm" style="background: var(--color-success); border-color: var(--color-success);">
                                                Approve
                                            </button>
                                            <button type="submit" name="status" value="rejected" class="btn btn-outline btn-sm" style="color: var(--color-danger);">
                                                Decline
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge <?= $app['status'] === 'approved' ? 'badge-verified' : 'badge-gold' ?>">
                                            <?= strtoupper($app['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="badge badge-primary"><?= strtoupper($app['status']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
