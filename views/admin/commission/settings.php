<div class="dashboard-header mb-4" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 10px;">
            <span>💰</span> Afriserva Commission Plan & Pricing Engine
        </h1>
        <p style="color: #94A3B8; margin: 4px 0 0; font-size: 14px;">Define default marketplace commission rates, fixed markup policies, pricing calculation basis, and user migration rules.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <!-- Left: Global Pricing Configuration Form -->
    <div>
        <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
                <span>⚙️</span> Financial Rules & Commission Configuration
            </h3>

            <form method="POST" action="<?= url('admin/commission-settings/update') ?>">
                <?= csrf_field() ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 6px;">Default Commission Percentage (%) *</label>
                        <div style="position: relative;">
                            <input type="number" step="0.01" min="0" max="100" name="commission_rate" value="<?= (float)$settings['commission_rate'] ?>" required class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 14px; font-weight: 700;">
                            <span style="position: absolute; right: 12px; top: 10px; color: #94A3B8;">%</span>
                        </div>
                        <span style="font-size: 11px; color: #64748B;">Applied per property transaction</span>
                    </div>

                    <div>
                        <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 6px;">Fixed Listing Markup (₦) *</label>
                        <div style="position: relative;">
                            <input type="number" step="1000" min="0" name="fixed_markup" value="<?= (float)$settings['fixed_markup'] ?>" required class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 14px; font-weight: 700;">
                            <span style="position: absolute; right: 12px; top: 10px; color: #94A3B8;">NGN</span>
                        </div>
                        <span style="font-size: 11px; color: #64748B;">Fixed amount added to buyer price</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 6px;">Commission Calculation Mode</label>
                    <select name="commission_mode" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                        <option value="percentage" <?= ($settings['commission_mode'] === 'percentage') ? 'selected' : '' ?>>Percentage Commission Only</option>
                        <option value="markup" <?= ($settings['commission_mode'] === 'markup') ? 'selected' : '' ?>>Fixed Markup Only</option>
                        <option value="combined" <?= ($settings['commission_mode'] === 'combined') ? 'selected' : '' ?>>Percentage Commission Plus Fixed Markup</option>
                        <option value="custom" <?= ($settings['commission_mode'] === 'custom') ? 'selected' : '' ?>>Custom Property Category Rules</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label style="font-size: 12px; color: #94A3B8; display: block; margin-bottom: 6px;">Calculation Basis & Deduction Model</label>
                    <select name="commission_basis" class="form-control" style="background: #020617; border-color: rgba(255,255,255,0.15); color: #FFF; font-size: 13px;">
                        <option value="added_to_asking" <?= ($settings['commission_basis'] === 'added_to_asking') ? 'selected' : '' ?>>
                            Added to Asking Price (Buyer pays Asking + Commission + Markup; Seller nets 100% of Asking)
                        </option>
                        <option value="deducted_from_proceeds" <?= ($settings['commission_basis'] === 'deducted_from_proceeds') ? 'selected' : '' ?>>
                            Deducted from Seller Proceeds (Buyer pays Asking; Commission & Markup deducted from Seller Payout)
                        </option>
                        <option value="percentage_of_buyer_price" <?= ($settings['commission_basis'] === 'percentage_of_buyer_price') ? 'selected' : '' ?>>
                            Percentage of Gross Buyer-Facing Price (Calculated via Gross Price formula)
                        </option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #E2E8F0; cursor: pointer;">
                        <input type="checkbox" name="require_manual_review" value="1" <?= (!empty($settings['require_manual_review']) ? 'checked' : '') ?>>
                        <span>Require staff review before payment</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #E2E8F0; cursor: pointer;">
                        <input type="checkbox" name="enable_manual_transfer" value="1" <?= (!empty($settings['enable_manual_transfer']) ? 'checked' : '') ?>>
                        <span>Enable manual bank transfer channel</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="background: var(--color-accent); color: #000B1A; font-weight: 700; padding: 10px 24px;">
                    Save Global Commission Rules
                </button>
            </form>
        </div>

        <!-- Transparent Formula & Model Explanation -->
        <div class="card p-4" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #FFF; margin: 0 0 12px;">
                Transparent Financial Calculation Engine
            </h4>
            <div style="background: #020617; border-radius: 8px; padding: 14px; font-size: 13px; color: #CBD5E1; line-height: 1.6;">
                <p style="margin: 0 0 8px;"><strong>Model Example:</strong> Suppose a property owner's asking price is ₦50,000,000, the configured commission rate is 5.0%, and fixed markup is ₦500,000.</p>
                <ul style="margin: 0; padding-left: 20px; color: #94A3B8;">
                    <li><strong>Asking Price:</strong> ₦50,000,000</li>
                    <li><strong>5% Commission:</strong> ₦2,500,000</li>
                    <li><strong>Fixed Markup:</strong> ₦500,000</li>
                    <li><strong>Buyer-Facing Price:</strong> ₦53,000,000 (when added to asking)</li>
                    <li><strong>Expected Seller Net Proceeds:</strong> ₦50,000,000</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Right: User Migration & Plan Controls -->
    <div>
        <!-- Default Commission Plan Status -->
        <div class="card p-4 mb-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(200, 154, 53, 0.3); border-radius: 12px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #FFF; margin: 0 0 8px;">
                Default Plan: Afriserva Commission Plan
            </h4>
            <div style="font-size: 12px; color: #94A3B8; margin-bottom: 14px;">
                Subscription Fee: <strong style="color: #10B981; font-size: 14px;">₦0 (No Monthly or Annual Fee)</strong>
            </div>
            <div style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 6px; font-size: 12px; color: #CBD5E1; margin-bottom: 14px;">
                ✓ Auto-assigned to new Sellers, Agents, and Landlords upon registration.<br>
                ✓ Generous 500-listing free quota.<br>
                ✓ Afriserva manages buyer verification & payment escrow.
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #94A3B8; margin-bottom: 14px;">
                <span>Subscribed Users on Plan:</span>
                <strong style="color: #FFF;"><?= number_format($commissionPlanUserCount) ?></strong>
            </div>
        </div>

        <!-- Idempotent Bulk Migration Tool -->
        <div class="card p-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #FFF; margin: 0 0 8px;">
                Bulk User Assignment Tool
            </h4>
            <p style="font-size: 12px; color: #94A3B8; line-height: 1.5; margin-bottom: 14px;">
                Assign the Afriserva Commission Plan as the default to eligible existing sellers and agents who do not hold an active paid subscription tier.
            </p>
            <div style="font-size: 12px; color: #64748B; margin-bottom: 16px;">
                ⚠️ <strong>Safe & Idempotent:</strong> Never cancels paid plans, deletes entitlements, or alters historic transaction records.
            </div>

            <form method="POST" action="<?= url('admin/commission-settings/migrate-users') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline w-100" style="border-color: rgba(200, 154, 53, 0.4); color: var(--color-accent); font-weight: 700;">
                    Assign to Eligible Existing Users
                </button>
            </form>
        </div>
    </div>
</div>
