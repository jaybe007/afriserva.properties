<div class="dashboard-header mb-4" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--color-accent);">🏛️</span> Afriserva Transaction Desk
        </h1>
        <p style="color: #94A3B8; margin: 4px 0 0; font-size: 14px;">Centralized financial management, buyer relationship governance, payment verification, and seller settlements.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= url('admin/bank-accounts') ?>" class="btn btn-outline" style="border-color: rgba(200, 154, 53, 0.4); color: #E2E8F0; font-size: 13px;">
            🏦 Receiving Accounts
        </a>
        <a href="<?= url('admin/commission-settings') ?>" class="btn btn-primary" style="background: var(--color-accent); color: #000B1A; font-weight: 700; font-size: 13px;">
            ⚙️ Commission Settings
        </a>
    </div>
</div>

<!-- Key Performance Metrics -->
<div class="stats-grid mb-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
    <div class="card p-3" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
        <span style="color: #94A3B8; font-size: 12px; font-weight: 600; text-transform: uppercase;">Total Pipeline</span>
        <div style="font-size: 26px; font-weight: 800; color: #FFFFFF; margin-top: 4px;"><?= number_format($stats['total_transactions']) ?></div>
        <span style="font-size: 12px; color: #64748B;">Managed real estate files</span>
    </div>
    <div class="card p-3" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px;">
        <span style="color: #F59E0B; font-size: 12px; font-weight: 600; text-transform: uppercase;">Pending Review</span>
        <div style="font-size: 26px; font-weight: 800; color: #F59E0B; margin-top: 4px;"><?= number_format($stats['pending_review']) ?></div>
        <span style="font-size: 12px; color: #64748B;">Awaiting staff evaluation</span>
    </div>
    <div class="card p-3" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px;">
        <span style="color: #60A5FA; font-size: 12px; font-weight: 600; text-transform: uppercase;">Awaiting Transfers</span>
        <div style="font-size: 26px; font-weight: 800; color: #60A5FA; margin-top: 4px;"><?= number_format($stats['awaiting_transfer']) ?></div>
        <span style="font-size: 12px; color: #64748B;">Instructions issued to buyers</span>
    </div>
    <div class="card p-3" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px;">
        <span style="color: #F87171; font-size: 12px; font-weight: 600; text-transform: uppercase;">Under Verification</span>
        <div style="font-size: 26px; font-weight: 800; color: #F87171; margin-top: 4px;"><?= number_format($stats['under_verification']) ?></div>
        <span style="font-size: 12px; color: #64748B;">Bank transfer slips submitted</span>
    </div>
    <div class="card p-3" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px;">
        <span style="color: #34D399; font-size: 12px; font-weight: 600; text-transform: uppercase;">Verified Escrow Funds</span>
        <div style="font-size: 22px; font-weight: 800; color: #34D399; margin-top: 4px;">₦<?= number_format($stats['total_funds_received'], 2) ?></div>
        <span style="font-size: 12px; color: #64748B;">Confirmed in receiving account</span>
    </div>
</div>

<!-- Search & Filtering Toolbar -->
<div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
    <form method="GET" action="<?= url('admin/transactions') ?>" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 100px; gap: 12px; align-items: end;">
        <div>
            <label style="font-size: 12px; color: #94A3B8; font-weight: 600; display: block; margin-bottom: 6px;">Keyword / Ref / Buyer</label>
            <input type="text" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Search reference, buyer, property..." class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px; height: 38px;">
        </div>
        <div>
            <label style="font-size: 12px; color: #94A3B8; font-weight: 600; display: block; margin-bottom: 6px;">Lifecycle Stage</label>
            <select name="stage" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px; height: 38px;">
                <option value="">All Stages</option>
                <option value="enquiry_received" <?= ($filters['stage'] === 'enquiry_received') ? 'selected' : '' ?>>Enquiry Received</option>
                <option value="under_review" <?= ($filters['stage'] === 'under_review') ? 'selected' : '' ?>>Under Review</option>
                <option value="viewing_scheduled" <?= ($filters['stage'] === 'viewing_scheduled') ? 'selected' : '' ?>>Viewing Scheduled</option>
                <option value="offer_submitted" <?= ($filters['stage'] === 'offer_submitted') ? 'selected' : '' ?>>Offer Submitted</option>
                <option value="payment_instructions_issued" <?= ($filters['stage'] === 'payment_instructions_issued') ? 'selected' : '' ?>>Payment Instructions Issued</option>
                <option value="payment_evidence_submitted" <?= ($filters['stage'] === 'payment_evidence_submitted') ? 'selected' : '' ?>>Payment Evidence Submitted</option>
                <option value="payment_verified" <?= ($filters['stage'] === 'payment_verified') ? 'selected' : '' ?>>Payment Verified</option>
                <option value="seller_settlement_pending" <?= ($filters['stage'] === 'seller_settlement_pending') ? 'selected' : '' ?>>Seller Settlement Pending</option>
                <option value="seller_settled" <?= ($filters['stage'] === 'seller_settled') ? 'selected' : '' ?>>Seller Settled</option>
                <option value="completed" <?= ($filters['stage'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= ($filters['stage'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div>
            <label style="font-size: 12px; color: #94A3B8; font-weight: 600; display: block; margin-bottom: 6px;">Payment Verification</label>
            <select name="payment_status" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px; height: 38px;">
                <option value="">All Statuses</option>
                <option value="not_payable" <?= ($filters['payment_status'] === 'not_payable') ? 'selected' : '' ?>>Not Yet Payable</option>
                <option value="awaiting_transfer" <?= ($filters['payment_status'] === 'awaiting_transfer') ? 'selected' : '' ?>>Awaiting Transfer</option>
                <option value="evidence_submitted" <?= ($filters['payment_status'] === 'evidence_submitted') ? 'selected' : '' ?>>Evidence Submitted</option>
                <option value="payment_confirmed" <?= ($filters['payment_status'] === 'payment_confirmed') ? 'selected' : '' ?>>Payment Confirmed</option>
                <option value="payment_rejected" <?= ($filters['payment_status'] === 'payment_rejected') ? 'selected' : '' ?>>Payment Rejected</option>
            </select>
        </div>
        <div>
            <label style="font-size: 12px; color: #94A3B8; font-weight: 600; display: block; margin-bottom: 6px;">Assigned Officer</label>
            <select name="officer_id" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px; height: 38px;">
                <option value="">All Staff</option>
                <?php foreach ($officers as $off): ?>
                    <option value="<?= $off['id'] ?>" <?= ((string)$filters['officer_id'] === (string)$off['id']) ? 'selected' : '' ?>>
                        <?= e($off['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary w-100" style="background: var(--color-accent); color: #000B1A; font-weight: 700; height: 38px;">
                Filter
            </button>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="card p-0" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: #E2E8F0;">
            <thead>
                <tr style="background: rgba(0,0,0,0.4); border-bottom: 1px solid rgba(255,255,255,0.1); color: #94A3B8; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                    <th style="padding: 14px 16px;">Reference</th>
                    <th style="padding: 14px 16px;">Property & Asset</th>
                    <th style="padding: 14px 16px;">Buyer Contact</th>
                    <th style="padding: 14px 16px;">Seller Net / Buyer Price</th>
                    <th style="padding: 14px 16px;">Stage</th>
                    <th style="padding: 14px 16px;">Payment Verification</th>
                    <th style="padding: 14px 16px;">Officer</th>
                    <th style="padding: 14px 16px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="8" style="padding: 40px; text-align: center; color: #94A3B8;">
                            <div style="font-size: 32px; margin-bottom: 8px;">📋</div>
                            No transactions matching current filter criteria found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $txn): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 14px 16px; font-family: monospace; font-weight: 700; color: var(--color-accent);">
                                <?= e($txn['transaction_reference']) ?>
                                <div style="font-size: 11px; color: #64748B; font-family: sans-serif;"><?= date('M d, Y', strtotime($txn['created_at'])) ?></div>
                            </td>
                            <td style="padding: 14px 16px;">
                                <a href="<?= url("properties/{$txn['property_slug']}") ?>" target="_blank" style="color: #FFF; text-decoration: none; font-weight: 600; display: block; max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($txn['property_title']) ?>
                                </a>
                                <span style="font-size: 11px; color: #94A3B8;"><?= e($txn['property_ref']) ?></span>
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="font-weight: 600; color: #E2E8F0;"><?= e($txn['buyer_name']) ?></div>
                                <div style="font-size: 11px; color: #94A3B8;"><?= e($txn['buyer_phone']) ?></div>
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="font-weight: 700; color: #10B981;">₦<?= number_format((float)$txn['buyer_price'], 2) ?></div>
                                <div style="font-size: 11px; color: #94A3B8;">Net: ₦<?= number_format((float)$txn['seller_expected_net'], 2) ?></div>
                            </td>
                            <td style="padding: 14px 16px;">
                                <?php
                                $stageColors = [
                                    'enquiry_received' => '#94A3B8',
                                    'under_review' => '#F59E0B',
                                    'viewing_scheduled' => '#3B82F6',
                                    'payment_instructions_issued' => '#8B5CF6',
                                    'payment_evidence_submitted' => '#EF4444',
                                    'payment_verified' => '#10B981',
                                    'completed' => '#10B981',
                                    'cancelled' => '#64748B'
                                ];
                                $color = $stageColors[$txn['stage']] ?? '#94A3B8';
                                ?>
                                <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: <?= $color ?>20; color: <?= $color ?>; border: 1px solid <?= $color ?>40;">
                                    <?= str_replace('_', ' ', $txn['stage']) ?>
                                </span>
                            </td>
                            <td style="padding: 14px 16px;">
                                <?php if ($txn['payment_status'] === 'payment_confirmed'): ?>
                                    <span style="color: #10B981; font-weight: 700; font-size: 11px;">✓ VERIFIED</span>
                                <?php elseif ($txn['payment_status'] === 'evidence_submitted'): ?>
                                    <span style="color: #F87171; font-weight: 700; font-size: 11px; animation: pulse 1.5s infinite;">⏳ EVIDENCE REVIEW</span>
                                <?php elseif ($txn['payment_status'] === 'awaiting_transfer'): ?>
                                    <span style="color: #60A5FA; font-weight: 600; font-size: 11px;">Awaiting Transfer</span>
                                <?php else: ?>
                                    <span style="color: #64748B; font-size: 11px;"><?= str_replace('_', ' ', $txn['payment_status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 16px; color: #94A3B8;">
                                <?= !empty($txn['officer_name']) ? e($txn['officer_name']) : '<span style="color: #EF4444; font-size: 11px;">Unassigned</span>' ?>
                            </td>
                            <td style="padding: 14px 16px; text-align: right;">
                                <a href="<?= url("admin/transactions/{$txn['id']}") ?>" class="btn btn-sm btn-outline" style="border-color: rgba(200, 154, 53, 0.4); color: var(--color-accent); font-weight: 600; padding: 4px 12px; font-size: 12px;">
                                    Manage Desk
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
