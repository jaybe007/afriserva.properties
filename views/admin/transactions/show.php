<?php
$pricingData = json_decode($transaction['pricing_snapshot'] ?? '{}', true) ?: [];
$bankData = json_decode($transaction['bank_account_snapshot'] ?? '{}', true) ?: [];
?>

<div class="mb-4" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <a href="<?= url('admin/transactions') ?>" style="color: #94A3B8; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            ← Back to Transaction Desk
        </a>
        <h1 style="font-size: 24px; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 10px;">
            Transaction File: <span style="color: var(--color-accent); font-family: monospace;"><?= e($transaction['transaction_reference']) ?></span>
        </h1>
        <p style="color: #94A3B8; margin: 4px 0 0; font-size: 13px;">Created on <?= date('F d, Y \a\t h:i A', strtotime($transaction['created_at'])) ?> &bull; Property: <strong><?= e($transaction['property_title']) ?></strong></p>
    </div>
    <div>
        <?php
        $stageColors = [
            'enquiry_received' => '#94A3B8',
            'under_review' => '#F59E0B',
            'viewing_scheduled' => '#3B82F6',
            'payment_instructions_issued' => '#8B5CF6',
            'payment_evidence_submitted' => '#EF4444',
            'payment_verified' => '#10B981',
            'seller_settlement_pending' => '#F59E0B',
            'seller_settled' => '#10B981',
            'completed' => '#10B981'
        ];
        $c = $stageColors[$transaction['stage']] ?? '#94A3B8';
        ?>
        <span style="display: inline-block; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: <?= $c ?>20; color: <?= $c ?>; border: 1px solid <?= $c ?>50;">
            Stage: <?= str_replace('_', ' ', $transaction['stage']) ?>
        </span>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <!-- Left Column: Core File Details -->
    <div>
        <!-- Financial Pricing Snapshot Card -->
        <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(200, 154, 53, 0.3); border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>⚖️</span> Immutable Pricing Snapshot & Charges
                </h3>
                <span style="font-size: 11px; color: #94A3B8; background: rgba(255,255,255,0.06); padding: 2px 8px; border-radius: 4px;">
                    Basis: <?= e($pricingData['commission_basis'] ?? 'added_to_asking') ?>
                </span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 16px;">
                <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 8px;">
                    <span style="color: #94A3B8; font-size: 11px; text-transform: uppercase; display: block;">Original Asking Price</span>
                    <strong style="color: #FFF; font-size: 16px;">₦<?= number_format((float)$transaction['asking_price'], 2) ?></strong>
                </div>
                <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 8px;">
                    <span style="color: #94A3B8; font-size: 11px; text-transform: uppercase; display: block;">Afriserva Commission</span>
                    <strong style="color: var(--color-accent); font-size: 16px;">
                        ₦<?= number_format((float)$transaction['commission_amount'], 2) ?>
                        <span style="font-size: 11px; color: #94A3B8;">(<?= $transaction['commission_rate'] ?>%)</span>
                    </strong>
                </div>
                <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 8px;">
                    <span style="color: #94A3B8; font-size: 11px; text-transform: uppercase; display: block;">Fixed Markup</span>
                    <strong style="color: #CBD5E1; font-size: 16px;">₦<?= number_format((float)$transaction['fixed_markup'], 2) ?></strong>
                </div>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 12px; border-radius: 8px;">
                    <span style="color: #34D399; font-size: 11px; text-transform: uppercase; display: block; font-weight: 700;">Final Buyer Price</span>
                    <strong style="color: #10B981; font-size: 18px; font-weight: 800;">₦<?= number_format((float)$transaction['buyer_price'], 2) ?></strong>
                </div>
                <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); padding: 12px; border-radius: 8px;">
                    <span style="color: #60A5FA; font-size: 11px; text-transform: uppercase; display: block; font-weight: 700;">Seller Net Proceeds</span>
                    <strong style="color: #60A5FA; font-size: 18px; font-weight: 800;">₦<?= number_format((float)$transaction['seller_expected_net'], 2) ?></strong>
                </div>
            </div>
            <div style="font-size: 12px; color: #64748B;">
                🔒 Stored snapshot captured at transaction creation. Global rate adjustments will never retrospectively alter these contract values.
            </div>
        </div>

        <!-- Payment Instructions Issued -->
        <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
                <span>🏦</span> Official Payment Destination & Instructions
            </h3>

            <?php if (!empty($bankData)): ?>
                <div style="background: #020617; border: 1px solid rgba(200, 154, 53, 0.3); border-radius: 8px; padding: 16px; margin-bottom: 14px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                        <div>
                            <span style="color: #94A3B8; font-size: 11px; text-transform: uppercase;">Designated Account</span>
                            <div style="color: #FFF; font-weight: 700; font-size: 14px;"><?= e($bankData['account_name'] ?? '') ?></div>
                        </div>
                        <div>
                            <span style="color: #94A3B8; font-size: 11px; text-transform: uppercase;">Bank Institution</span>
                            <div style="color: #FFF; font-weight: 700; font-size: 14px;"><?= e($bankData['bank_name'] ?? '') ?></div>
                        </div>
                        <div>
                            <span style="color: #94A3B8; font-size: 11px; text-transform: uppercase;">Account Number</span>
                            <div style="color: var(--color-accent); font-family: monospace; font-weight: 800; font-size: 16px;"><?= e($bankData['account_number'] ?? '') ?></div>
                        </div>
                        <div>
                            <span style="color: #94A3B8; font-size: 11px; text-transform: uppercase;">Required Narration</span>
                            <div style="color: #38BDF8; font-family: monospace; font-weight: 700; font-size: 14px;"><?= e($transaction['transaction_reference']) ?></div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); padding: 12px 16px; border-radius: 8px; color: #F59E0B; font-size: 13px; margin-bottom: 14px;">
                    ⚠️ Official payment instructions have not yet been dispatched to the buyer for this transaction file.
                </div>
            <?php endif; ?>

            <!-- Action to issue or re-issue payment instructions -->
            <form method="POST" action="<?= url("admin/transactions/{$transaction['id']}/issue-payment-instructions") ?>" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; align-items: end;">
                <?= csrf_field() ?>
                <div>
                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 4px;">Receiving Bank Destination</label>
                    <select name="bank_account_id" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                        <?php foreach ($allAccounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>" <?= ($acc['is_active'] ? 'selected' : '') ?>>
                                <?= e($acc['bank_name']) ?> - <?= e($acc['account_number']) ?> (<?= e($acc['account_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 4px;">Payment Deadline</label>
                    <input type="date" name="payment_deadline" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="background: var(--color-accent); color: #000B1A; font-weight: 700; height: 38px;">
                        Issue Instructions
                    </button>
                </div>
            </form>
        </div>

        <!-- Payment Evidence & Verification Desk -->
        <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>🧾</span> Buyer Transfer Slips & Verification Ledger
                </h3>
                <span style="font-size: 12px; color: #94A3B8;">
                    Status: <strong style="color: #FFF;"><?= strtoupper(str_replace('_', ' ', $transaction['payment_status'])) ?></strong>
                </span>
            </div>

            <?php if (empty($evidence)): ?>
                <div style="padding: 24px; text-align: center; color: #94A3B8; background: rgba(0,0,0,0.2); border-radius: 8px;">
                    No payment evidence uploaded by buyer yet.
                </div>
            <?php else: ?>
                <?php foreach ($evidence as $ev): ?>
                    <div style="background: #020617; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
                            <div>
                                <span style="font-size: 11px; color: #94A3B8; text-transform: uppercase;">Submitted By <?= e($ev['buyer_name']) ?> on <?= date('M d, Y', strtotime($ev['created_at'])) ?></span>
                                <div style="font-size: 18px; font-weight: 800; color: #10B981; margin: 4px 0;">
                                    ₦<?= number_format((float)$ev['amount_paid'], 2) ?>
                                    <span style="font-size: 12px; color: #94A3B8; font-weight: 400;">via <?= e($ev['payment_channel']) ?></span>
                                </div>
                                <div style="font-size: 12px; color: #CBD5E1;">
                                    Session Ref / Narration: <strong style="color: var(--color-accent); font-family: monospace;"><?= e($ev['reference_or_session_id'] ?: 'Not Provided') ?></strong>
                                </div>
                                <?php if (!empty($ev['buyer_notes'])): ?>
                                    <div style="font-size: 12px; color: #94A3B8; margin-top: 4px; font-style: italic;">"<?= e($ev['buyer_notes']) ?>"</div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <a href="<?= url($ev['receipt_file_url']) ?>" target="_blank" class="btn btn-sm btn-outline" style="border-color: rgba(255,255,255,0.2); color: #FFF; font-size: 12px;">
                                    📄 View Uploaded Receipt Slip
                                </a>
                            </div>
                        </div>

                        <!-- Verification form -->
                        <?php if ($ev['review_status'] === 'pending'): ?>
                            <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.08);">
                                <form method="POST" action="<?= url("admin/transactions/{$transaction['id']}/verify-payment") ?>" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                                    <?= csrf_field() ?>
                                    <div>
                                        <label style="font-size: 11px; color: #94A3B8; display: block; margin-bottom: 4px;">Bank Reconciliation Ref</label>
                                        <input type="text" name="reconciliation_reference" placeholder="e.g. ZENITH-STMT-99482" required class="form-control" style="background: #0F172A; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 12px;">
                                    </div>
                                    <div>
                                        <label style="font-size: 11px; color: #94A3B8; display: block; margin-bottom: 4px;">Verified Amount (₦)</label>
                                        <input type="number" step="0.01" name="verified_amount" value="<?= (float)$transaction['buyer_price'] ?>" required class="form-control" style="background: #0F172A; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 12px;">
                                    </div>
                                    <div>
                                        <label style="font-size: 11px; color: #94A3B8; display: block; margin-bottom: 4px;">Audit Notes</label>
                                        <input type="text" name="verification_notes" placeholder="Notes on statement confirmation" class="form-control" style="background: #0F172A; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 12px;">
                                    </div>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="submit" name="decision" value="confirm" class="btn btn-sm btn-primary" style="background: #10B981; border: none; font-weight: 700; height: 35px; color: #FFF;">
                                            Confirm Receipt
                                        </button>
                                        <button type="submit" name="decision" value="reject" class="btn btn-sm btn-outline" style="border-color: #EF4444; color: #EF4444; height: 35px;">
                                            Reject
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div style="margin-top: 10px; font-size: 12px; color: #34D399;">
                                ✓ Verified by <?= e($ev['reviewer_name'] ?? 'Authorized Officer') ?> on <?= date('M d, Y H:i', strtotime($ev['reviewed_at'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Seller Settlement Management -->
        <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>💸</span> Seller Proceeds Settlement Ledger
                </h3>
                <span style="font-size: 12px; color: #94A3B8;">
                    Current Status: <strong style="color: #60A5FA;"><?= strtoupper($transaction['settlement_status']) ?></strong>
                </span>
            </div>

            <div style="background: #020617; border-radius: 8px; padding: 14px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <span style="font-size: 11px; color: #94A3B8; text-transform: uppercase;">Beneficiary Seller</span>
                    <div style="color: #FFF; font-weight: 700; font-size: 14px;"><?= e($transaction['seller_name']) ?> (<?= e($transaction['seller_phone']) ?>)</div>
                </div>
                <div>
                    <span style="font-size: 11px; color: #94A3B8; text-transform: uppercase;">Agreed Net Payout</span>
                    <div style="color: #60A5FA; font-weight: 800; font-size: 18px;">₦<?= number_format((float)$transaction['seller_expected_net'], 2) ?></div>
                </div>
            </div>

            <form method="POST" action="<?= url("admin/transactions/{$transaction['id']}/settlement") ?>" style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 10px; align-items: end;">
                <?= csrf_field() ?>
                <div>
                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 4px;">Settlement Decision</label>
                    <select name="settlement_status" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                        <option value="pending" <?= ($transaction['settlement_status'] === 'pending') ? 'selected' : '' ?>>Pending Escrow Review</option>
                        <option value="processing" <?= ($transaction['settlement_status'] === 'processing') ? 'selected' : '' ?>>Disbursement Processing</option>
                        <option value="settled" <?= ($transaction['settlement_status'] === 'settled') ? 'selected' : '' ?>>Disbursed & Settled</option>
                        <option value="on_hold" <?= ($transaction['settlement_status'] === 'on_hold') ? 'selected' : '' ?>>Held in Compliance Escrow</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 4px;">Settlement Narration / NIP Ref</label>
                    <input type="text" name="settlement_notes" placeholder="Disbursement transfer ref / date" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="background: #2563EB; color: #FFF; font-weight: 700; height: 38px;">
                        Record Settlement
                    </button>
                </div>
            </form>
        </div>

        <!-- Full Audit Timeline -->
        <div class="card p-4" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
                <span>⏱️</span> Immutable Transaction Chronology & Timeline
            </h3>

            <div style="position: relative; padding-left: 20px; border-left: 2px solid rgba(200, 154, 53, 0.3);">
                <?php foreach ($timeline as $tEvent): ?>
                    <div style="margin-bottom: 20px; position: relative;">
                        <div style="position: absolute; left: -27px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: var(--color-accent); border: 2px solid #000B1A;"></div>
                        <div style="font-weight: 700; color: #FFF; font-size: 13px;"><?= e($tEvent['title']) ?></div>
                        <div style="font-size: 11px; color: #94A3B8; margin: 2px 0;">
                            <?= date('M d, Y \a\t h:i A', strtotime($tEvent['created_at'])) ?> &bull; Actor: <strong><?= e($tEvent['actor_name'] ?? 'System') ?></strong> (<?= e($tEvent['actor_role']) ?>)
                        </div>
                        <?php if (!empty($tEvent['description'])): ?>
                            <div style="font-size: 12px; color: #CBD5E1; margin-top: 4px; background: rgba(0,0,0,0.25); padding: 8px; border-radius: 6px;">
                                <?= e($tEvent['description']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Control Desk & Governance Actions -->
    <div>
        <!-- Lifecycle Stage Progression Control -->
        <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #FFF; margin: 0 0 12px; text-transform: uppercase;">
                Stage Transition
            </h4>
            <form method="POST" action="<?= url("admin/transactions/{$transaction['id']}/stage") ?>">
                <?= csrf_field() ?>
                <div style="margin-bottom: 12px;">
                    <label style="font-size: 11px; color: #94A3B8; display: block; margin-bottom: 4px;">Update Status Stage</label>
                    <select name="stage" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                        <option value="enquiry_received" <?= ($transaction['stage'] === 'enquiry_received') ? 'selected' : '' ?>>1. Enquiry Received</option>
                        <option value="under_review" <?= ($transaction['stage'] === 'under_review') ? 'selected' : '' ?>>2. Under Review</option>
                        <option value="viewing_requested" <?= ($transaction['stage'] === 'viewing_requested') ? 'selected' : '' ?>>3. Viewing Requested</option>
                        <option value="viewing_scheduled" <?= ($transaction['stage'] === 'viewing_scheduled') ? 'selected' : '' ?>>4. Viewing Scheduled</option>
                        <option value="offer_submitted" <?= ($transaction['stage'] === 'offer_submitted') ? 'selected' : '' ?>>5. Offer Submitted</option>
                        <option value="offer_accepted" <?= ($transaction['stage'] === 'offer_accepted') ? 'selected' : '' ?>>6. Offer Accepted</option>
                        <option value="payment_instructions_issued" <?= ($transaction['stage'] === 'payment_instructions_issued') ? 'selected' : '' ?>>7. Payment Instructions Issued</option>
                        <option value="payment_evidence_submitted" <?= ($transaction['stage'] === 'payment_evidence_submitted') ? 'selected' : '' ?>>8. Evidence Submitted</option>
                        <option value="payment_verified" <?= ($transaction['stage'] === 'payment_verified') ? 'selected' : '' ?>>9. Payment Verified</option>
                        <option value="seller_settlement_pending" <?= ($transaction['stage'] === 'seller_settlement_pending') ? 'selected' : '' ?>>10. Settlement Pending</option>
                        <option value="seller_settled" <?= ($transaction['stage'] === 'seller_settled') ? 'selected' : '' ?>>11. Seller Settled</option>
                        <option value="completed" <?= ($transaction['stage'] === 'completed') ? 'selected' : '' ?>>12. Completed</option>
                        <option value="cancelled" <?= ($transaction['stage'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        <option value="disputed" <?= ($transaction['stage'] === 'disputed') ? 'selected' : '' ?>>Disputed</option>
                    </select>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="font-size: 11px; color: #94A3B8; display: block; margin-bottom: 4px;">Transition Audit Note</label>
                    <textarea name="notes" rows="2" placeholder="Reason or protocol action..." class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 12px;"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100" style="background: var(--color-accent); color: #000B1A; font-weight: 700;">
                    Update Stage
                </button>
            </form>
        </div>

        <!-- Assigned Staff Officer -->
        <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #FFF; margin: 0 0 12px; text-transform: uppercase;">
                Assigned Officer
            </h4>
            <div style="margin-bottom: 12px; color: #CBD5E1; font-size: 13px;">
                Current Officer: <strong style="color: #FFF;"><?= !empty($transaction['officer_name']) ? e($transaction['officer_name']) : 'None' ?></strong>
            </div>
            <form method="POST" action="<?= url("admin/transactions/{$transaction['id']}/assign-officer") ?>">
                <?= csrf_field() ?>
                <div style="margin-bottom: 12px;">
                    <select name="officer_id" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                        <option value="">Select Staff Member</option>
                        <?php foreach ($officers as $off): ?>
                            <option value="<?= $off['id'] ?>" <?= ((int)($transaction['assigned_officer_id'] ?? 0) === (int)$off['id']) ? 'selected' : '' ?>>
                                <?= e($off['full_name']) ?> (<?= e($off['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-outline w-100" style="border-color: rgba(200, 154, 53, 0.4); color: var(--color-accent); font-weight: 700;">
                    Assign Officer
                </button>
            </form>
        </div>

        <!-- Stakeholders & Contact Records (Staff Only) -->
        <div class="card p-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #FFF; margin: 0 0 12px; text-transform: uppercase;">
                Stakeholder Records
            </h4>
            
            <div style="margin-bottom: 14px; padding-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.06);">
                <span style="font-size: 11px; color: #94A3B8; text-transform: uppercase;">Buyer (Protected)</span>
                <div style="color: #FFF; font-weight: 700; font-size: 14px;"><?= e($transaction['buyer_name']) ?></div>
                <div style="font-size: 12px; color: #CBD5E1;"><?= e($transaction['buyer_email']) ?></div>
                <div style="font-size: 12px; color: #38BDF8;"><?= e($transaction['buyer_phone']) ?></div>
            </div>

            <div>
                <span style="font-size: 11px; color: #94A3B8; text-transform: uppercase;">Property Owner / Seller</span>
                <div style="color: #FFF; font-weight: 700; font-size: 14px;"><?= e($transaction['seller_name']) ?></div>
                <div style="font-size: 12px; color: #CBD5E1;"><?= e($transaction['seller_email']) ?></div>
                <div style="font-size: 12px; color: #38BDF8;"><?= e($transaction['seller_phone']) ?></div>
            </div>
        </div>
    </div>
</div>
