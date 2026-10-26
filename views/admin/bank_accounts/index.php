<div class="dashboard-header mb-4" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 10px;">
            <span>🏦</span> Company Receiving Bank Accounts
        </h1>
        <p style="color: #94A3B8; margin: 4px 0 0; font-size: 14px;">Official Afriserva company receiving accounts for client manual transfers. Only the currently designated active account is presented to buyers.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <!-- Left: Existing Accounts List -->
    <div>
        <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0 0 16px;">
                Designated Company Bank Accounts
            </h3>

            <?php if (empty($accounts)): ?>
                <div style="padding: 24px; text-align: center; color: #94A3B8;">
                    No company accounts registered yet.
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 16px;">
                    <?php foreach ($accounts as $acc): ?>
                        <div style="background: #020617; border: 1px solid <?= $acc['is_active'] ? 'rgba(16, 185, 129, 0.5)' : 'rgba(255,255,255,0.08)' ?>; border-radius: 10px; padding: 18px; position: relative;">
                            <?php if ($acc['is_active']): ?>
                                <span style="position: absolute; top: 14px; right: 14px; background: #10B981; color: #000B1A; font-size: 11px; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 6px;">
                                    ✓ Active Primary Destination
                                </span>
                            <?php endif; ?>

                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(200, 154, 53, 0.15); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                    🏦
                                </div>
                                <div>
                                    <h4 style="font-size: 16px; font-weight: 700; color: #FFF; margin: 0;"><?= e($acc['bank_name']) ?></h4>
                                    <span style="font-size: 12px; color: #94A3B8;"><?= e($acc['account_name']) ?> (<?= e(ucfirst(str_replace('_', ' ', $acc['account_type']))) ?>)</span>
                                </div>
                            </div>

                            <div style="margin: 12px 0; background: rgba(0,0,0,0.3); padding: 10px 14px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 12px; color: #94A3B8;">Account Number:</span>
                                <span style="font-family: monospace; font-size: 18px; font-weight: 800; color: var(--color-accent); letter-spacing: 1px;">
                                    <?= e($acc['account_number']) ?>
                                </span>
                            </div>

                            <?php if (!empty($acc['payment_instructions'])): ?>
                                <div style="font-size: 12px; color: #CBD5E1; margin-bottom: 12px;">
                                    <strong>Instructions:</strong> <?= e($acc['payment_instructions']) ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!$acc['is_active']): ?>
                                <form method="POST" action="<?= url("admin/bank-accounts/{$acc['id']}/activate") ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline" style="border-color: rgba(16, 185, 129, 0.5); color: #10B981; font-weight: 600;">
                                        Make Active Receiving Destination
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Register New Account Form -->
    <div>
        <div class="card p-4" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0 0 16px;">
                Add Receiving Account
            </h3>

            <form method="POST" action="<?= url('admin/bank-accounts/store') ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 6px;">Bank Institution *</label>
                    <input type="text" name="bank_name" placeholder="e.g. Zenith Bank Plc, GTBank" required class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                </div>

                <div class="mb-3">
                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 6px;">Account Holder Name *</label>
                    <input type="text" name="account_name" placeholder="e.g. Afriserva Real Estate (Cloud Current Int Ltd)" required class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                </div>

                <div class="mb-3">
                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 6px;">NUBAN Account Number *</label>
                    <input type="text" name="account_number" placeholder="10-digit NUBAN" maxlength="20" required class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px; font-family: monospace;">
                </div>

                <div class="mb-3">
                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 6px;">Account Type</label>
                    <select name="account_type" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                        <option value="corporate_current">Corporate Current</option>
                        <option value="escrow_trust">Escrow / Client Money Account</option>
                        <option value="standard_current">Standard Current</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 6px;">Buyer Transfer Instructions</label>
                    <textarea name="payment_instructions" rows="3" placeholder="Narration instructions, warning against cash deposits..." class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 12px;">Ensure your unique Transaction Reference is specified in the narration. Only transfers to this official corporate account are recognized.</textarea>
                </div>

                <div class="mb-4">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #E2E8F0; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Set as currently active destination</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100" style="background: var(--color-accent); color: #000B1A; font-weight: 700;">
                    Save Receiving Account
                </button>
            </form>
        </div>
    </div>
</div>
