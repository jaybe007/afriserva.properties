<div class="dashboard-header mb-4" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 10px;">
            <span>🏛️</span> <?= $isSeller ? 'Transactions & Settlement Proceeds' : 'My Property Transactions' ?>
        </h1>
        <p style="color: #94A3B8; margin: 4px 0 0; font-size: 14px;">
            <?= $isSeller ? 'Monitor transaction stages, verified funds, commission breakdowns, and net proceeds settlements managed by Afriserva.' : 'Track your active property purchases, official Afriserva payment instructions, transfer verification, and purchase records.' ?>
        </p>
    </div>
</div>

<?php if ($isSeller && !empty($sellerTransactions)): ?>
    <!-- Seller / Agent Sales Records -->
    <div class="card p-0 mb-4" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.2);">
            <h3 style="font-size: 16px; font-weight: 700; color: #FFF; margin: 0;">My Listing Transactions & Payout Ledger</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: #E2E8F0;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.3); border-bottom: 1px solid rgba(255,255,255,0.08); color: #94A3B8; text-transform: uppercase; font-size: 11px;">
                        <th style="padding: 12px 16px;">Reference</th>
                        <th style="padding: 12px 16px;">Property</th>
                        <th style="padding: 12px 16px;">Asking Price</th>
                        <th style="padding: 12px 16px;">Expected Net Proceeds</th>
                        <th style="padding: 12px 16px;">Transaction Stage</th>
                        <th style="padding: 12px 16px;">Settlement Status</th>
                        <th style="padding: 12px 16px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sellerTransactions as $st): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 12px 16px; font-family: monospace; font-weight: 700; color: var(--color-accent);">
                                <?= e($st['transaction_reference']) ?>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 600; color: #FFF;"><?= e($st['property_title']) ?></div>
                                <span style="font-size: 11px; color: #94A3B8;"><?= e($st['property_ref']) ?></span>
                            </td>
                            <td style="padding: 12px 16px; color: #CBD5E1;">
                                ₦<?= number_format((float)$st['asking_price'], 2) ?>
                            </td>
                            <td style="padding: 12px 16px;">
                                <strong style="color: #10B981; font-size: 15px;">₦<?= number_format((float)$st['seller_expected_net'], 2) ?></strong>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: rgba(59, 130, 246, 0.15); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.3);">
                                    <?= str_replace('_', ' ', $st['stage']) ?>
                                </span>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span style="color: <?= ($st['settlement_status'] === 'settled') ? '#10B981' : '#F59E0B' ?>; font-weight: 700; font-size: 11px;">
                                    <?= strtoupper($st['settlement_status']) ?>
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <a href="<?= url("dashboard/transactions/{$st['id']}") ?>" class="btn btn-sm btn-outline" style="border-color: rgba(200, 154, 53, 0.4); color: var(--color-accent); font-size: 12px;">
                                    View File
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Buyer Transactions Section -->
<div class="card p-0" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.2);">
        <h3 style="font-size: 16px; font-weight: 700; color: #FFF; margin: 0;">My Active Property Files</h3>
    </div>
    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: #E2E8F0;">
            <thead>
                <tr style="background: rgba(0,0,0,0.3); border-bottom: 1px solid rgba(255,255,255,0.08); color: #94A3B8; text-transform: uppercase; font-size: 11px;">
                    <th style="padding: 12px 16px;">Reference</th>
                    <th style="padding: 12px 16px;">Property</th>
                    <th style="padding: 12px 16px;">Agreed Amount</th>
                    <th style="padding: 12px 16px;">Status Stage</th>
                    <th style="padding: 12px 16px;">Payment Verification</th>
                    <th style="padding: 12px 16px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($buyerTransactions)): ?>
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #94A3B8;">
                            <div style="font-size: 32px; margin-bottom: 8px;">📑</div>
                            You do not currently have any active property transaction records.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($buyerTransactions as $bt): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 12px 16px; font-family: monospace; font-weight: 700; color: var(--color-accent);">
                                <?= e($bt['transaction_reference']) ?>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 600; color: #FFF;"><?= e($bt['property_title']) ?></div>
                                <span style="font-size: 11px; color: #94A3B8;"><?= e($bt['property_ref']) ?></span>
                            </td>
                            <td style="padding: 12px 16px;">
                                <strong style="color: #10B981; font-size: 15px;">₦<?= number_format((float)$bt['buyer_price'], 2) ?></strong>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: rgba(59, 130, 246, 0.15); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.3);">
                                    <?= str_replace('_', ' ', $bt['stage']) ?>
                                </span>
                            </td>
                            <td style="padding: 12px 16px;">
                                <?php if ($bt['payment_status'] === 'payment_confirmed'): ?>
                                    <span style="color: #10B981; font-weight: 700; font-size: 11px;">✓ VERIFIED & CONFIRMED</span>
                                <?php elseif ($bt['payment_status'] === 'evidence_submitted'): ?>
                                    <span style="color: #F59E0B; font-weight: 700; font-size: 11px;">⏳ UNDER VERIFICATION</span>
                                <?php elseif ($bt['payment_status'] === 'awaiting_transfer'): ?>
                                    <span style="color: #38BDF8; font-weight: 700; font-size: 11px;">AWAITING TRANSFER</span>
                                <?php else: ?>
                                    <span style="color: #94A3B8; font-size: 11px;"><?= strtoupper(str_replace('_', ' ', $bt['payment_status'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <a href="<?= url("dashboard/transactions/{$bt['id']}") ?>" class="btn btn-sm btn-primary" style="background: var(--color-accent); color: #000B1A; font-weight: 700; font-size: 12px;">
                                    View Instructions & Evidence
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
