<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 24px;">Property Verification & Legal Due Diligence Hub</h2>
        <p style="color: var(--color-text-muted);">
            Section 12 Compliance: Enforce statutory review of land titles, LASRERA licenses, and anti-fraud duplicate detection.
        </p>
    </div>
    <div style="display: flex; gap: 8px;">
        <span class="badge badge-verified" style="font-size: 13px; padding: 8px 14px;">
            🛡️ Zero-Tolerance Fraud Policy
        </span>
    </div>
</div>

<div class="table-card">
    <?php if (empty($verifications)): ?>
        <div style="padding: 48px; text-align: center; color: var(--color-text-muted);">
            <div style="font-size: 40px; margin-bottom: 12px;">✅</div>
            <h4>No Pending Verifications in Queue</h4>
            <p>All property listings and agent credential submissions have been thoroughly audited.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ref / Property</th>
                    <th>Submitting Lister</th>
                    <th>Title Document Claim</th>
                    <th>Status</th>
                    <th>Reviewer</th>
                    <th>Compliance Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($verifications as $v): ?>
                    <tr>
                        <td style="max-width: 260px;">
                            <code><?= e($v['reference_code']) ?></code><br>
                            <strong><?= e($v['title']) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= e($v['city_name']) ?>, <?= e($v['state_name']) ?></small>
                            <?php if (!empty($v['duplicate_candidates'])): ?>
                                <div style="margin-top: 4px;">
                                    <span class="badge badge-gold" style="font-size: 10px; background: #FEF3C7; color: #92400E; border: 1px solid #F59E0B;">
                                        ⚠️ Potential Duplicate Detected
                                    </span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($v['agent_name']) ?></strong><br>
                            <small style="color: var(--color-text-muted);"><?= e($v['agent_email']) ?></small><br>
                            <small style="color: var(--color-accent); font-weight: 600;"><?= e($v['agent_phone'] ?? '') ?></small>
                        </td>
                        <td>
                            <strong><?= e($v['title_document'] ?? 'Certificate of Occupancy') ?></strong><br>
                            <?php if (!empty($v['documents'])): ?>
                                <div style="margin-top: 6px;">
                                    <?php foreach ($v['documents'] as $doc): ?>
                                        <a href="<?= url('admin/documents/' . $doc['id'] . '/download') ?>" class="btn btn-outline btn-sm" style="font-size: 11px; padding: 2px 8px; margin-bottom: 2px; display: inline-block;">
                                            📄 <?= e($doc['file_name']) ?> ⬇
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <small style="color: var(--color-text-muted);">No private documents attached</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $v['verification_status'] === 'verified' ? 'badge-verified' : ($v['verification_status'] === 'rejected' ? 'badge-gold' : 'badge-primary') ?>">
                                <?= ucwords(str_replace('_', ' ', $v['verification_status'] ?? 'unverified')) ?>
                            </span>
                            <?php if (!empty($v['rejection_reason'])): ?>
                                <div style="font-size: 11px; color: var(--color-danger); margin-top: 4px;">
                                    Reason: <?= e(ucwords(str_replace('_', ' ', $v['rejection_reason']))) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form action="<?= url('admin/verifications/' . $v['id'] . '/assign') ?>" method="POST">
                                <?= csrf_field() ?>
                                <select name="reviewer_id" class="form-control" style="font-size: 12px; padding: 4px 8px; height: auto;" onchange="this.form.submit()">
                                    <option value="">-- Assign Reviewer --</option>
                                    <?php foreach ($moderators as $mod): ?>
                                        <option value="<?= $mod['id'] ?>" <?= ((int)($v['assigned_reviewer_id'] ?? 0) === (int)$mod['id']) ? 'selected' : '' ?>>
                                            <?= e($mod['full_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <!-- Review & Adjudication Action Form -->
                            <form action="<?= url('admin/verifications/' . $v['id'] . '/review') ?>" method="POST" style="display: flex; flex-direction: column; gap: 6px;">
                                <?= csrf_field() ?>

                                <select name="verification_status" class="form-control" style="font-size: 12px; padding: 4px 8px; height: auto;" required>
                                    <option value="verified" <?= $v['verification_status'] === 'verified' ? 'selected' : '' ?>>✓ Mark Verified</option>
                                    <option value="additional_info_required" <?= $v['verification_status'] === 'additional_info_required' ? 'selected' : '' ?>>⚠️ Require Additional Info</option>
                                    <option value="rejected" <?= $v['verification_status'] === 'rejected' ? 'selected' : '' ?>>✕ Reject Submission</option>
                                    <option value="expired_or_revoked" <?= $v['verification_status'] === 'expired_or_revoked' ? 'selected' : '' ?>>🚫 Revoke Verification</option>
                                </select>

                                <select name="rejection_reason" class="form-control" style="font-size: 11px; padding: 4px 6px; height: auto;">
                                    <option value="">-- Reason (if rejected/info needed) --</option>
                                    <option value="invalid_title_document">Invalid / Unverified Title Document</option>
                                    <option value="unauthorized_agent">Unauthorized Agent / No Owner Mandate</option>
                                    <option value="fraudulent_ownership_claim">Fraudulent Ownership Claim</option>
                                    <option value="duplicate_listing">Duplicate Listing Detected</option>
                                    <option value="unclear_scan">Unclear / Illegible Document Scan</option>
                                    <option value="expired_license">Expired Regulatory Broker License</option>
                                </select>

                                <input type="text" name="notes" class="form-control" placeholder="Reviewer notes..." style="font-size: 11px; padding: 4px 6px; height: auto;">

                                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; font-size: 12px; padding: 4px 8px;">
                                    Submit Decision
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
