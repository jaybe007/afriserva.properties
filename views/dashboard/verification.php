<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Professional Identity & Agency Verification</h1>
        <p class="dashboard-subtitle">
            Section 12 Compliance: Submit regulatory credentials to obtain the <strong>Verified Broker Badge</strong>.
            All documents are audited by compliance personnel against official LASRERA, CAC, and SCUML registries.
        </p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; align-items: start;">
    <div class="table-card">
        <div class="table-header">
            <h3 style="font-size: 18px; font-weight: 600;">Submitted Regulatory Credentials</h3>
            <span class="badge badge-primary"><?= count($documents ?? []) ?> Submitted</span>
        </div>

        <?php if (empty($documents)): ?>
            <div style="padding: 40px; text-align: center; color: var(--color-text-muted);">
                <div style="font-size: 36px; margin-bottom: 8px;">🛡️</div>
                <h4>No Credentials Submitted Yet</h4>
                <p>Upload your official regulatory certifications using the portal on the right to initiate compliance audit.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Credential Type</th>
                        <th>Document / Registration No.</th>
                        <th>Status</th>
                        <th>Audited By</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td>
                                <strong><?= ucwords(str_replace('_', ' ', $doc['document_type'])) ?></strong><br>
                                <small style="color: var(--color-text-muted);"><?= e($doc['file_name']) ?></small>
                            </td>
                            <td><code><?= e($doc['document_number'] ?? 'N/A') ?></code></td>
                            <td>
                                <span class="badge <?= $doc['verification_status'] === 'verified' ? 'badge-verified' : ($doc['verification_status'] === 'rejected' ? 'badge-gold' : 'badge-primary') ?>">
                                    <?= strtoupper($doc['verification_status']) ?>
                                </span>
                            </td>
                            <td>
                                <small><?= e($doc['reviewer_notes'] ?? 'Pending manual review') ?></small>
                            </td>
                            <td><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Upload Credential Card -->
    <div class="dashboard-card">
        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 16px; color: var(--color-primary);">Submit Compliance Document</h3>
        <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 20px;">
            Supported formats: PDF, JPG, PNG (Max 10MB). Stored in encrypted private storage.
        </p>

        <form action="<?= url('dashboard/verification/upload') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Credential Classification *</label>
                <select name="document_type" class="form-control" required>
                    <option value="lasrera_license">LASRERA Practitioner License</option>
                    <option value="cac_certificate">CAC Certificate of Incorporation</option>
                    <option value="scuml_certificate">SCUML / EFCC Anti-Money Laundering Cert</option>
                    <option value="national_id">National Identity Slip / NIN</option>
                    <option value="passport">International Passport Data Page</option>
                    <option value="developer_permit">Government Development Approval</option>
                    <option value="law_firm_mandate">Legal Power of Attorney / Mandate</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Official License / Registration Number</label>
                <input type="text" name="document_number" class="form-control" placeholder="e.g. LASRERA/IND/2026/0412" required>
            </div>

            <div class="form-group">
                <label class="form-label">Document Scan URL / Storage Path *</label>
                <input type="text" name="file_path" class="form-control" placeholder="storage/documents/lasrera_cert.pdf" required>
            </div>

            <div class="form-group">
                <label class="form-label">Friendly File Label</label>
                <input type="text" name="file_name" class="form-control" placeholder="e.g. LASRERA_Certificate_2026.pdf" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px;">
                🛡️ Submit for Verification
            </button>
        </form>
    </div>
</div>
