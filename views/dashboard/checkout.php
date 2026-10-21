<div style="max-width: 600px; margin: 20px auto;">
    <div style="background: #FFFFFF; border-radius: var(--radius-xl); border: 1px solid var(--color-border); padding: 40px; box-shadow: var(--shadow-luxury);">
        <h2 style="font-size: 26px; margin-bottom: 8px;">Order Checkout & Plan Activation</h2>
        <p style="color: var(--color-text-muted); margin-bottom: 24px;">Complete payment verification to unlock elevated listing allowances.</p>

        <!-- Order Summary Card -->
        <div style="background: #F8FAFC; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-weight: 600;"><?= e($plan['name']) ?> (<?= ucfirst($plan['billing_interval']) ?>)</span>
                <strong><?= format_money($plan['price']) ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--color-text-muted); margin-bottom: 12px;">
                <span>VAT / Statutory Taxes</span>
                <span>Included</span>
            </div>
            <div style="border-top: 1px solid var(--color-border); padding-top: 12px; display: flex; justify-content: space-between; font-size: 18px; font-weight: 700; color: var(--color-primary);">
                <span>Total Due:</span>
                <span><?= format_money($plan['price']) ?></span>
            </div>
        </div>

        <form action="<?= url('checkout/process') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">

            <div class="form-group">
                <label class="form-label">Payment Gateway Provider</label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="border: 1px solid var(--color-border); padding: 14px; border-radius: var(--radius-md); display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="radio" name="gateway" value="test_sandbox" checked>
                        <div>
                            <strong>Test Sandbox Instant Activation (Evaluation Mode)</strong>
                            <div style="font-size: 12px; color: var(--color-text-muted);">Instantly activates membership with zero card charges for testing.</div>
                        </div>
                    </label>

                    <label style="border: 1px solid var(--color-border); padding: 14px; border-radius: var(--radius-md); display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="radio" name="gateway" value="paystack">
                        <div>
                            <strong>Paystack (Cards, Bank Transfer, USSD)</strong>
                            <div style="font-size: 12px; color: var(--color-text-muted);">Secure payment verification via Paystack Nigeria.</div>
                        </div>
                    </label>

                    <label style="border: 1px solid var(--color-border); padding: 14px; border-radius: var(--radius-md); display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="radio" name="gateway" value="flutterwave">
                        <div>
                            <strong>Flutterwave (Cards, Barter, International Accounts)</strong>
                            <div style="font-size: 12px; color: var(--color-text-muted);">Pan-African and international card checkout.</div>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-accent btn-lg" style="width: 100%; margin-top: 16px;">
                Confirm & Activate Membership ➔
            </button>
        </form>
    </div>
</div>
