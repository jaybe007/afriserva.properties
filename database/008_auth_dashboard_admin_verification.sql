-- Migration 008: Auth, Dashboard, Admin Command, and Property Verification Enterprise Extensions

-- 1. Insert Additional Account Roles
INSERT IGNORE INTO roles (name, label, description) VALUES
('tenant', 'Tenant', 'Leases residential or commercial properties and submits maintenance requests'),
('seller', 'Property Seller', 'Lists properties for outright sale'),
('property_manager', 'Property Manager', 'Oversees facility management, rent collection and tenancy operations'),
('support', 'Customer Support', 'Handles buyer/tenant enquiries, reviews disputes and assists users');

-- 2. Password Resets Table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pwd_email (email),
    INDEX idx_pwd_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Login Attempt Throttling Table
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(191) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attempt_ip (ip_address, attempted_at),
    INDEX idx_attempt_email (email, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Account Deletion Requests Table
CREATE TABLE IF NOT EXISTS account_deletion_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    admin_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_del_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Expand Users Table with 2FA, Verification, Preferences, and Consent Records
ALTER TABLE users 
    ADD COLUMN IF NOT EXISTS two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER two_factor_secret,
    ADD COLUMN IF NOT EXISTS email_verification_token VARCHAR(100) NULL AFTER email_verified_at,
    ADD COLUMN IF NOT EXISTS phone_verification_token VARCHAR(20) NULL AFTER phone_verified_at,
    ADD COLUMN IF NOT EXISTS notify_email TINYINT(1) NOT NULL DEFAULT 1 AFTER bio,
    ADD COLUMN IF NOT EXISTS notify_sms TINYINT(1) NOT NULL DEFAULT 1 AFTER notify_email,
    ADD COLUMN IF NOT EXISTS notify_marketing TINYINT(1) NOT NULL DEFAULT 0 AFTER notify_sms,
    ADD COLUMN IF NOT EXISTS is_phone_public TINYINT(1) NOT NULL DEFAULT 1 AFTER notify_marketing,
    ADD COLUMN IF NOT EXISTS is_email_public TINYINT(1) NOT NULL DEFAULT 0 AFTER is_phone_public,
    ADD COLUMN IF NOT EXISTS terms_accepted_at DATETIME NULL AFTER is_email_public,
    ADD COLUMN IF NOT EXISTS privacy_accepted_at DATETIME NULL AFTER terms_accepted_at,
    ADD COLUMN IF NOT EXISTS consent_ip_address VARCHAR(45) NULL AFTER privacy_accepted_at;

-- 6. Expand Properties Status and Verification Status ENUMs & Review Tracking
ALTER TABLE properties
    MODIFY COLUMN status ENUM('draft', 'pending_review', 'published', 'rejected', 'archived', 'sold', 'rented', 'paused', 'unavailable') NOT NULL DEFAULT 'published',
    MODIFY COLUMN verification_status ENUM('unverified', 'submitted', 'under_review', 'additional_info_required', 'verified', 'rejected', 'expired_or_revoked') NOT NULL DEFAULT 'unverified',
    ADD COLUMN IF NOT EXISTS rejection_reason VARCHAR(100) NULL AFTER verification_status,
    ADD COLUMN IF NOT EXISTS reviewer_feedback TEXT NULL AFTER rejection_reason,
    ADD COLUMN IF NOT EXISTS assigned_reviewer_id INT UNSIGNED NULL AFTER reviewer_feedback;

-- 7. Expand Property Documents with Expiry & Reviewer Tracking
ALTER TABLE property_documents
    ADD COLUMN IF NOT EXISTS expiry_date DATE NULL AFTER reviewer_notes,
    ADD COLUMN IF NOT EXISTS reviewed_by INT UNSIGNED NULL AFTER expiry_date,
    ADD COLUMN IF NOT EXISTS reviewed_at DATETIME NULL AFTER reviewed_by;

-- 8. Expand Property Verifications with Rejection Reason
ALTER TABLE property_verifications
    ADD COLUMN IF NOT EXISTS rejection_reason VARCHAR(100) NULL AFTER notes;

-- 9. Promotional Coupons & Discount Codes Table
CREATE TABLE IF NOT EXISTS coupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    max_uses INT UNSIGNED DEFAULT NULL,
    uses_count INT UNSIGNED DEFAULT 0,
    expires_at DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO coupons (code, discount_type, discount_value, max_uses, expires_at, is_active) VALUES
('LAUNCH2026', 'percentage', 20.00, 500, '2026-12-31 23:59:59', 1),
('PRESTIGE100', 'fixed', 5000.00, 200, '2026-12-31 23:59:59', 1);

-- Mark existing admin and agents as having verified emails and accepted terms for seamless testing
UPDATE users 
SET email_verified_at = NOW(), 
    phone_verified_at = NOW(), 
    terms_accepted_at = NOW(), 
    privacy_accepted_at = NOW() 
WHERE email_verified_at IS NULL;
