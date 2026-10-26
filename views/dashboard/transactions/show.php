<?php
$bankData = json_decode($transaction['bank_account_snapshot'] ?? '{}', true) ?: [];
$pricingData = json_decode($transaction['pricing_snapshot'] ?? '{}', true) ?: [];
?>

<div class="mb-4" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <a href="<?= url('dashboard/transactions') ?>" style="color: #94A3B8; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            ← Back to Transactions
        </a>
        <h1 style="font-size: 24px; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 10px;">
            Transaction: <span style="color: var(--color-accent); font-family: monospace;"><?= e($transaction['transaction_reference']) ?></span>
        </h1>
        <p style="color: #94A3B8; margin: 4px 0 0; font-size: 14px;">Property: <strong><?= e($transaction['property_title']) ?></strong> &bull; Reference: <strong><?= e($transaction['property_ref']) ?></strong></p>
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
        <span style="display: inline-block; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 800; text-transform: uppercase; background: <?= $c ?>20; color: <?= $c ?>; border: 1px solid <?= $c ?>50;">
            Status: <?= str_replace('_', ' ', $transaction['stage']) ?>
        </span>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <div>
        <?php if ($isBuyer): ?>
            <!-- Buyer Financial Overview & Payment Instructions -->
            <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(200, 154, 53, 0.3); border-radius: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0;">
                        Official Afriserva Payment Instructions
                    </h3>
                    <div style="font-size: 18px; font-weight: 800; color: #10B981;">
                        Amount Payable: ₦<?= number_format((float)$transaction['buyer_price'], 2) ?>
                    </div>
                </div>

                <?php if (!empty($bankData) || $activeAccount): ?>
                    <?php
                    $acc = !empty($bankData) ? $bankData : $activeAccount;
                    ?>
                    <div style="background: #020617; border: 2px solid var(--color-accent); border-radius: 10px; padding: 20px; margin-bottom: 16px;">
                        <div style="color: #F59E0B; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <span>⚠️</span> Transfer Only to Official Afriserva Receiving Account
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 14px;">
                            <div>
                                <span style="font-size: 11px; color: #94A3B8; text-transform: uppercase; display: block;">Beneficiary Account Name</span>
                                <strong style="color: #FFF; font-size: 15px;"><?= e($acc['account_name']) ?></strong>
                            </div>
                            <div>
                                <span style="font-size: 11px; color: #94A3B8; text-transform: uppercase; display: block;">Bank Name</span>
                                <strong style="color: #FFF; font-size: 15px;"><?= e($acc['bank_name']) ?></strong>
                            </div>
                            <div>
                                <span style="font-size: 11px; color: #94A3B8; text-transform: uppercase; display: block;">NUBAN Account Number</span>
                                <strong style="color: var(--color-accent); font-family: monospace; font-size: 20px; letter-spacing: 1px;"><?= e($acc['account_number']) ?></strong>
                            </div>
                            <div>
                                <span style="font-size: 11px; color: #94A3B8; text-transform: uppercase; display: block;">Mandatory Payment Narration / Ref</span>
                                <strong style="color: #38BDF8; font-family: monospace; font-size: 16px;"><?= e($transaction['transaction_reference']) ?></strong>
                            </div>
                        </div>
                        <div style="background: rgba(255,255,255,0.04); padding: 10px 14px; border-radius: 6px; font-size: 12px; color: #CBD5E1;">
                            <?= e($acc['payment_instructions'] ?? 'Please ensure the exact transaction reference is indicated in your transfer narration. Never make cash or direct payments to third-party bank accounts.') ?>
                        </div>
                    </div>

                    <!-- Payment Evidence Upload Form -->
                    <div class="card p-4 mb-4" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px;">
                        <h4 style="font-size: 15px; font-weight: 700; color: #FFF; margin: 0 0 12px;">
                            Submit Bank Transfer Evidence
                        </h4>
                        <p style="font-size: 12px; color: #94A3B8; margin-bottom: 16px;">
                            After completing your transfer, upload your bank confirmation receipt or screenshot. Afriserva will verify receipt of funds with the company bank records before issuing legal confirmation.
                        </p>

                        <form method="POST" action="<?= url("dashboard/transactions/{$transaction['id']}/evidence") ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 4px;">Amount Transferred (₦) *</label>
                                    <input type="number" step="0.01" name="amount_paid" value="<?= (float)$transaction['buyer_price'] ?>" required class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                                </div>
                                <div>
                                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 4px;">Transfer Date *</label>
                                    <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 4px;">Payment Channel / Method</label>
                                    <select name="payment_channel" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                                        <option value="bank_transfer">Mobile Banking / NIP Transfer</option>
                                        <option value="bank_branch_deposit">Bank Branch Deposit Slip</option>
                                        <option value="corporate_wire">Corporate RTGS / Wire</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 4px;">Bank Session ID / Reference</label>
                                    <input type="text" name="reference_or_session_id" placeholder="Bank transaction session ID" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 4px;">Upload Bank Transfer Receipt (PDF, JPG, PNG) *</label>
                                <input type="file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png" required class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                            </div>

                            <div class="mb-3">
                                <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 4px;">Optional Note</label>
                                <textarea name="buyer_notes" rows="2" placeholder="Sender bank name or additional narration..." class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 12px;"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary" style="background: var(--color-accent); color: #000B1A; font-weight: 700; padding: 10px 24px;">
                                Upload Payment Evidence
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; padding: 16px; color: #93C5FD; font-size: 13px;">
                        ℹ️ Your transaction is currently under preliminary review by Afriserva. Once verified, official payment instructions with the approved receiving account will be generated.
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Seller / Agent View (STRICT PRIVACY: Zero buyer contact info) -->
            <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0 0 16px;">
                    Seller Financial Summary & Proceeds
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 16px;">
                    <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 11px; color: #94A3B8; text-transform: uppercase;">Original Asking Price</span>
                        <div style="font-size: 16px; font-weight: 700; color: #FFF;">₦<?= number_format((float)$transaction['asking_price'], 2) ?></div>
                    </div>
                    <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 11px; color: #94A3B8; text-transform: uppercase;">Afriserva Charges</span>
                        <div style="font-size: 16px; font-weight: 700; color: var(--color-accent);">₦<?= number_format((float)$transaction['commission_amount'] + (float)$transaction['fixed_markup'], 2) ?></div>
                    </div>
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 11px; color: #34D399; text-transform: uppercase; font-weight: 700;">Expected Net Proceeds</span>
                        <div style="font-size: 18px; font-weight: 800; color: #10B981;">₦<?= number_format((float)$transaction['seller_expected_net'], 2) ?></div>
                    </div>
                    <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); padding: 12px; border-radius: 8px;">
                        <span style="font-size: 11px; color: #60A5FA; text-transform: uppercase; font-weight: 700;">Settlement Status</span>
                        <div style="font-size: 16px; font-weight: 800; color: #60A5FA;"><?= strtoupper($transaction['settlement_status']) ?></div>
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 14px; font-size: 13px; color: #CBD5E1; line-height: 1.5;">
                    🛡️ <strong>Managed Transaction Guarantee:</strong> Afriserva professionally conducts buyer due diligence, coordinates viewing inspections, collects verified bank funds into escrow, and coordinates full settlement upon completion.
                </div>
            </div>
        <?php endif; ?>

        <!-- Chronological Transaction Activity -->
        <div class="card p-4" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #FFF; margin: 0 0 16px;">
                Transaction Timeline & Status Updates
            </h3>
            <div style="position: relative; padding-left: 20px; border-left: 2px solid rgba(200, 154, 53, 0.3);">
                <?php foreach ($timeline as $tEvent): ?>
                    <div style="margin-bottom: 16px; position: relative;">
                        <div style="position: absolute; left: -26px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: var(--color-accent);"></div>
                        <div style="font-weight: 700; color: #FFF; font-size: 13px;"><?= e($tEvent['title']) ?></div>
                        <div style="font-size: 11px; color: #94A3B8;"><?= date('M d, Y \a\t h:i A', strtotime($tEvent['created_at'])) ?></div>
                        <?php if (!empty($tEvent['description'])): ?>
                            <div style="font-size: 12px; color: #CBD5E1; margin-top: 4px;">
                                <?= e($tEvent['description']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right: Property & File Overview -->
    <div>
        <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #FFF; margin: 0 0 12px; text-transform: uppercase;">
                Property Asset
            </h4>
            <div style="font-weight: 700; color: #FFF; font-size: 15px; margin-bottom: 4px;">
                <?= e($transaction['property_title']) ?>
            </div>
            <div style="font-size: 12px; color: #94A3B8; margin-bottom: 12px;">
                Ref: <strong><?= e($transaction['property_ref']) ?></strong>
            </div>
            <a href="<?= url("properties/{$transaction['property_slug']}") ?>" target="_blank" class="btn btn-sm btn-outline w-100" style="border-color: rgba(255,255,255,0.2); color: #FFF; font-size: 12px;">
                View Marketplace Listing ↗
            </a>
        </div>

        <div class="card p-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #FFF; margin: 0 0 12px; text-transform: uppercase;">
                Need Assistance?
            </h4>
            <p style="font-size: 12px; color: #94A3B8; line-height: 1.5; margin-bottom: 14px;">
                For inquiries regarding this transaction, please communicate through the Afriserva Transaction Desk or contact customer operations.
            </p>
            <div style="font-size: 12px; color: #CBD5E1;">
                <strong>Desk Email:</strong> support@afriserva.com<br>
                <strong>Desk Phone:</strong> +234 1 800 2374
            </div>
        </div>
    </div>
</div>
