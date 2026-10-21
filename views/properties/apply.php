<div class="container" style="max-width: 800px; padding: 48px 20px;">
    <div style="margin-bottom: 32px; text-align: center;">
        <span class="badge badge-gold" style="font-size: 12px; margin-bottom: 8px;">Statutory Tenancy Screening</span>
        <h1 style="font-size: 28px; font-weight: 700;">Rental Application Portal</h1>
        <p style="color: var(--color-text-muted); max-width: 600px; margin: 8px auto 0;">
            Apply for leasehold residency at <strong><?= e($property['title']) ?></strong> (<?= format_money($property['price']) ?> / year).
        </p>
    </div>

    <div class="table-card" style="padding: 32px;">
        <form action="<?= url('properties/' . $property['slug'] . '/apply') ?>" method="POST">
            <?= csrf_field() ?>

            <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
                1. Financial & Employment Verification
            </h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Annual Verified Income (₦) *</label>
                    <input type="number" step="0.01" name="annual_income" class="form-control" placeholder="e.g. 15000000" required>
                    <small style="color: var(--color-text-muted); font-size: 11px;">Standard guideline requires annual income to be at least 2.5x annual rent.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Employment Status *</label>
                    <select name="employment_status" class="form-control" required>
                        <option value="employed">Full-Time Employed</option>
                        <option value="self_employed">Self-Employed / Freelance</option>
                        <option value="business_owner">Business Owner / Director</option>
                        <option value="contractor">Expatriate / Contractor</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label">Employer / Business Name *</label>
                <input type="text" name="employer_name" class="form-control" placeholder="e.g. Zenith Bank Plc, Shell Petroleum, etc." required>
            </div>

            <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--color-border); padding-bottom: 8px;">
                2. Guarantor & Reference Information
            </h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Guarantor Full Legal Name *</label>
                    <input type="text" name="guarantor_name" class="form-control" placeholder="e.g. Barrister Tunde Okonkwo" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Relationship to Applicant *</label>
                    <input type="text" name="guarantor_relationship" class="form-control" placeholder="e.g. Employer, Senior Colleague, Family" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div class="form-group">
                    <label class="form-label">Guarantor Phone Number *</label>
                    <input type="text" name="guarantor_phone" class="form-control" placeholder="+234 803 000 0000" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Guarantor Work Email *</label>
                    <input type="email" name="guarantor_email" class="form-control" placeholder="guarantor@firm.ng" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label">Additional Application Notes or Special Requests</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Provide any additional details or preferred move-in dates..."></textarea>
            </div>

            <!-- Statutory Notice -->
            <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: var(--radius-md); padding: 14px; margin-bottom: 24px; font-size: 12px; color: #92400E; line-height: 1.5;">
                <strong>Statutory Notice & Privacy Consent:</strong> By submitting this rental application, you consent to lawful verification of income and reference checks by the landlord or authorized managing agent. This application is an expression of interest and does not constitute a legally binding tenancy agreement until executed.
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 16px; font-weight: 700;">
                Submit Rental Application for Landlord Review →
            </button>
        </form>
    </div>
</div>
