<div style="max-width: 800px; margin: 40px auto; padding: 40px; background: #FFFFFF; border: 1px solid #E2E8F0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #0F172A; position: relative;">
    
    <!-- Paid Watermark Stamp -->
    <?php if ($invoice['status'] === 'paid'): ?>
        <div style="position: absolute; top: 120px; right: 60px; border: 4px solid #16A34A; color: #16A34A; font-size: 32px; font-weight: 900; padding: 8px 24px; text-transform: uppercase; transform: rotate(-12deg); opacity: 0.85; border-radius: 8px; letter-spacing: 2px;">
            PAID IN FULL
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0F172A; padding-bottom: 24px; margin-bottom: 24px;">
        <div>
            <div style="font-size: 26px; font-weight: 800; color: #0F172A;">AFRISERVA REAL ESTATE</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">Operated by Cloud Current International Limited • cloudcurrentng.com</div>
            <div style="font-size: 12px; color: #64748B;">Lagos, Nigeria • billing@afriserva.net • afriserva.net</div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 22px; font-weight: 800; color: #1E3A8A;">TAX INVOICE / RECEIPT</div>
            <div style="font-size: 14px; font-weight: 700; margin-top: 4px;"><?= e($invoice['invoice_number']) ?></div>
            <div style="font-size: 12px; color: #64748B;">Date Issued: <?= date('F d, Y', strtotime($invoice['created_at'])) ?></div>
        </div>
    </div>

    <!-- Bill To / Payment Details -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px; background: #F8FAFC; padding: 20px; border-radius: 6px;">
        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: #64748B; font-weight: 700;">Billed To Customer</div>
            <div style="font-size: 16px; font-weight: 700; margin-top: 4px;"><?= e($invoice['customer_name']) ?></div>
            <div style="font-size: 13px; color: #475569;"><?= e($invoice['customer_email']) ?></div>
            <div style="font-size: 13px; color: #475569;"><?= e($invoice['customer_phone'] ?? '') ?></div>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: #64748B; font-weight: 700;">Payment & Reconciliation</div>
            <div style="font-size: 13px; margin-top: 4px;"><strong>Payment Gateway:</strong> <?= strtoupper($invoice['gateway'] ?? 'CARD/TRANSFER') ?></div>
            <div style="font-size: 13px; margin-top: 2px;"><strong>Transaction Ref:</strong> <span style="font-family: monospace; font-size: 12px;"><?= e($invoice['payment_reference'] ?? 'PENDING') ?></span></div>
            <div style="font-size: 13px; margin-top: 2px;"><strong>Settlement Date:</strong> <?= !empty($invoice['paid_at']) ? date('M d, Y h:i A', strtotime($invoice['paid_at'])) : 'Unsettled' ?></div>
        </div>
    </div>

    <!-- Items Table -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 32px; font-size: 13px;">
        <thead>
            <tr style="background: #0F172A; color: #FFFFFF; text-align: left;">
                <th style="padding: 10px 12px;">Description / Service Provision</th>
                <th style="padding: 10px 12px; text-align: center;">Qty</th>
                <th style="padding: 10px 12px; text-align: right;">Unit Price (₦)</th>
                <th style="padding: 10px 12px; text-align: right;">Total (₦)</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-bottom: 1px solid #E2E8F0;">
                <td style="padding: 12px;">
                    <strong><?= e($invoice['notes'] ?? 'Professional Membership Subscription') ?></strong><br>
                    <small style="color: #64748B;">Includes multi-listing allocations, verified badge, and lead management CRM access.</small>
                </td>
                <td style="padding: 12px; text-align: center;">1</td>
                <td style="padding: 12px; text-align: right;"><?= format_money($invoice['subtotal']) ?></td>
                <td style="padding: 12px; text-align: right; font-weight: 600;"><?= format_money($invoice['subtotal']) ?></td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="border-top: 1px solid #E2E8F0;">
                <td colspan="3" style="padding: 8px 12px; text-align: right; font-weight: 600;">Subtotal:</td>
                <td style="padding: 8px 12px; text-align: right; font-weight: 600;"><?= format_money($invoice['subtotal']) ?></td>
            </tr>
            <tr>
                <td colspan="3" style="padding: 8px 12px; text-align: right; font-weight: 600;">VAT (7.5% Standard Zero-Rated):</td>
                <td style="padding: 8px 12px; text-align: right; font-weight: 600;">₦0.00</td>
            </tr>
            <tr style="background: #F8FAFC; border-top: 2px solid #0F172A; font-size: 16px; font-weight: 800;">
                <td colspan="3" style="padding: 12px; text-align: right;">TOTAL AMOUNT PAID:</td>
                <td style="padding: 12px; text-align: right; color: #16A34A;"><?= format_money($invoice['total_amount']) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Actions & Refund Request Form -->
    <div style="border-top: 1px solid #E2E8F0; padding-top: 20px; display: flex; justify-content: space-between; align-items: center;" class="no-print">
        <?php if ($invoice['status'] === 'paid'): ?>
            <div>
                <button type="button" onclick="document.getElementById('refundModal').style.display='flex'" style="background: none; border: none; color: #DC2626; font-size: 12px; text-decoration: underline; cursor: pointer;">
                    Request Refund on this Transaction
                </button>
            </div>
        <?php else: ?>
            <div></div>
        <?php endif; ?>
        <button onclick="window.print()" style="background: #0F172A; color: #FFFFFF; border: none; padding: 10px 24px; font-weight: 700; border-radius: 4px; cursor: pointer;">
            🖨️ Print Receipt
        </button>
    </div>
</div>

<!-- Refund Request Modal -->
<div id="refundModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;" class="no-print">
    <div style="background: #FFFFFF; border-radius: var(--radius-md); width: 100%; max-width: 480px; padding: 28px;">
        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 12px;">Request Payment Refund</h3>
        <p style="font-size: 13px; color: #64748B; margin-bottom: 16px;">Refunds are processed in compliance with Afriserva commercial policy within 5 business days.</p>
        <form action="<?= url('dashboard/payments/' . ($invoice['id'] ?? 0) . '/refund-request') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Reason for Refund *</label>
                <textarea name="reason" rows="3" class="form-control" placeholder="Provide details regarding the reason for your refund request..." required></textarea>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('refundModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #DC2626; border-color: #DC2626;">Submit Refund Request</button>
            </div>
        </form>
    </div>
</div>
