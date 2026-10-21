-- Migration 009: Resolve Sections 9-12 Oversights
-- 2FA OTP, Phone Verification, User Compliance Documents, and Location CSV Import

-- 1. Add 2FA OTP Code and Phone Verification Columns to Users Table
ALTER TABLE users 
    ADD COLUMN IF NOT EXISTS two_factor_code VARCHAR(10) NULL AFTER two_factor_secret,
    ADD COLUMN IF NOT EXISTS two_factor_code_expires_at DATETIME NULL AFTER two_factor_code;

-- 2. Professional & Identity Verification Documents Table (Agent, Agency, Developer, Landlord)
CREATE TABLE IF NOT EXISTS identity_verification_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    agency_id INT UNSIGNED NULL,
    document_type ENUM('lasrera_license', 'cac_certificate', 'scuml_certificate', 'national_id', 'passport', 'law_firm_mandate', 'developer_permit') NOT NULL,
    document_number VARCHAR(100) NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(150) NOT NULL,
    verification_status ENUM('pending', 'verified', 'rejected', 'expired') NOT NULL DEFAULT 'pending',
    reviewer_id INT UNSIGNED NULL,
    reviewer_notes TEXT NULL,
    expiry_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_ident_status (verification_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Seed Initial Compliance Verification Documents for Demo Agent & Agency
INSERT IGNORE INTO identity_verification_documents 
(user_id, agency_id, document_type, document_number, file_path, file_name, verification_status, reviewer_id, reviewer_notes) 
VALUES
(2, 1, 'lasrera_license', 'LASRERA/IND/2023/1109', 'storage/documents/lasrera_cert_chioma.pdf', 'LASRERA_Broker_Certificate_2023.pdf', 'verified', 1, 'Verified against Lagos State Real Estate Regulatory Authority registry.'),
(3, 1, 'cac_certificate', 'RC-1849204', 'storage/documents/cac_prestige_prime.pdf', 'Prestige_Prime_CAC_Certificate.pdf', 'verified', 1, 'Corporate Affairs Commission registration certified active.');
