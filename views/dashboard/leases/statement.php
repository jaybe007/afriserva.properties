<div style="max-width: 800px; margin: 40px auto; padding: 40px; background: #FFFFFF; border: 1px solid #E2E8F0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #0F172A;">
    <!-- Statement Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0F172A; padding-bottom: 24px; margin-bottom: 24px;">
        <div>
            <div style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #0F172A;">AFRISERVA REAL ESTATE</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">A Division of Cloud Current International Limited • cloudcurrentng.com</div>
            <div style="font-size: 12px; color: #64748B;">Victoria Island, Lagos, Nigeria • support@afriserva.net • afriserva.net</div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 20px; font-weight: 700; color: #1E3A8A;">STATEMENT OF ACCOUNT</div>
            <div style="font-size: 13px; font-weight: 600; margin-top: 4px;">Lease ID: #<?= str_pad($lease['id'], 6, '0', STR_PAD_LEFT) ?></div>
            <div style="font-size: 12px; color: #64748B;">Generated: <?= date('F d, Y') ?></div>
        </div>
    </div>

    <!-- Lease Details Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px; background: #F8FAFC; padding: 20px; border-radius: 6px;">
        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: #64748B; font-weight: 700;">Property & Location</div>
            <div style="font-size: 15px; font-weight: 700; margin-top: 4px;"><?= e($lease['property_title']) ?></div>
            <div style="font-size: 13px; color: #475569;"><?= e($lease['property_address'] ?? 'Private Jurisdiction') ?></div>
            <div style="font-size: 12px; margin-top: 6px;"><strong>Tenancy Term:</strong> <?= date('M d, Y', strtotime($lease['lease_start_date'])) ?> – <?= date('M d, Y', strtotime($lease['lease_end_date'])) ?></div>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: #64748B; font-weight: 700;">Account Parties</div>
            <div style="font-size: 13px; margin-top: 4px;"><strong>Landlord:</strong> <?= e($lease['landlord_name']) ?> (<?= e($lease['landlord_phone']) ?>)</div>
            <div style="font-size: 13px; margin-top: 2px;"><strong>Tenant:</strong> <?= e($lease['tenant_name']) ?> (<?= e($lease['tenant_phone']) ?>)</div>
            <div style="font-size: 13px; margin-top: 2px;"><strong>Agreed Rent:</strong> <?= format_money($lease['rent_amount']) ?> / <?= e($lease['payment_frequency']) ?></div>
        </div>
    </div>

    <!-- Payments Ledger Table -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 32px; font-size: 13px;">
        <thead>
            <tr style="background: #0F172A; color: #FFFFFF; text-align: left;">
                <th style="padding: 10px 12px;">Transaction Date</th>
                <th style="padding: 10px 12px;">Due Date</th>
                <th style="padding: 10px 12px;">Receipt Reference</th>
                <th style="padding: 10px 12px;">Method</th>
                <th style="padding: 10px 12px;">Status</th>
                <th style="padding: 10px 12px; text-align: right;">Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalReceived = 0.00;
            if (empty($payments)): 
            ?>
                <tr>
                    <td colspan="6" style="padding: 24px; text-align: center; color: #64748B;">No payment records logged for this tenancy agreement.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($payments as $p): 
                    if ($p['status'] === 'paid') {
                        $totalReceived += (float)$p['amount'];
                    }
                ?>
                    <tr style="border-bottom: 1px solid #E2E8F0;">
                        <td style="padding: 10px 12px;"><?= !empty($p['paid_date']) ? date('M d, Y', strtotime($p['paid_date'])) : '—' ?></td>
                        <td style="padding: 10px 12px;"><?= date('M d, Y', strtotime($p['due_date'])) ?></td>
                        <td style="padding: 10px 12px; font-family: monospace; font-size: 12px;"><?= e($p['receipt_reference'] ?? 'MANUAL') ?></td>
                        <td style="padding: 10px 12px; text-transform: capitalize;"><?= str_replace('_', ' ', $p['payment_method']) ?></td>
                        <td style="padding: 10px 12px;">
                            <span style="font-weight: 700; color: <?= $p['status'] === 'paid' ? '#16A34A' : ($p['status'] === 'overdue' ? '#DC2626' : '#D97706') ?>;">
                                <?= strtoupper($p['status']) ?>
                            </span>
                        </td>
                        <td style="padding: 10px 12px; text-align: right; font-weight: 600;"><?= format_money($p['amount']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background: #F8FAFC; font-weight: 700; border-top: 2px solid #0F172A;">
                <td colspan="5" style="padding: 12px; text-align: right;">TOTAL VERIFIED RENT REMITTANCE:</td>
                <td style="padding: 12px; text-align: right; font-size: 15px; color: #16A34A;"><?= format_money($totalReceived) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Property Management Fee Calculation (10% statutory standard) -->
    <?php 
    $mgmtFeeRate = 0.10;
    $mgmtFeeAmount = $totalReceived * $mgmtFeeRate;
    $netRemittance = $totalReceived - $mgmtFeeAmount;
    ?>
    <div style="background: #F1F5F9; border-radius: 6px; padding: 18px; margin-bottom: 32px; font-size: 13px;">
        <div style="font-weight: 700; font-size: 14px; margin-bottom: 8px;">Property Management Remittance Accounting (Standard 10% Retainer)</div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
            <span>Gross Collections:</span>
            <span><?= format_money($totalReceived) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px; color: #64748B;">
            <span>Management & Administrative Fee (10%):</span>
            <span>- <?= format_money($mgmtFeeAmount) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 14px; border-top: 1px solid #CBD5E1; padding-top: 6px; margin-top: 6px; color: #0F172A;">
            <span>Net Landlord Remittance:</span>
            <span><?= format_money($netRemittance) ?></span>
        </div>
    </div>

    <!-- Disclaimer & Print Actions -->
    <div style="font-size: 11px; color: #64748B; border-top: 1px solid #E2E8F0; padding-top: 16px; line-height: 1.5;">
        <strong>Statutory Notice:</strong> This statement of account is generated directly from Afriserva Real Estate financial ledgers (Cloud Current International Limited). Verified financial transactions are distinguished from offline self-reported payment records.
    </div>

    <div style="margin-top: 24px; text-align: right;" class="no-print">
        <button onclick="window.print()" style="background: #0F172A; color: #FFFFFF; border: none; padding: 10px 20px; font-weight: 600; border-radius: 4px; cursor: pointer;">
            🖨️ Print Statement
        </button>
    </div>
</div>
