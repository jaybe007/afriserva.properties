-- ============================================================================
-- AFRISERVA PROPERTIES: MIGRATION 013
-- DEFAULT COMMISSION-BASED REAL ESTATE PLAN & ADMIN TRANSACTION DESK
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Ensure transaction_officer role exists
INSERT INTO `roles` (`name`, `description`, `created_at`)
SELECT 'transaction_officer', 'Manages buyer enquiries, coordinates viewings, verifies manual payments, and oversees transactions', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE `name` = 'transaction_officer');

-- 2. Extend subscription_plans table with commission & default plan columns
ALTER TABLE `subscription_plans` 
    ADD COLUMN IF NOT EXISTS `plan_type` ENUM('subscription', 'commission') NOT NULL DEFAULT 'subscription' AFTER `slug`,
    ADD COLUMN IF NOT EXISTS `is_default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`,
    ADD COLUMN IF NOT EXISTS `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `price`,
    ADD COLUMN IF NOT EXISTS `fixed_markup` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `commission_rate`,
    ADD COLUMN IF NOT EXISTS `commission_mode` ENUM('percentage', 'markup', 'combined', 'custom') NOT NULL DEFAULT 'percentage' AFTER `fixed_markup`,
    ADD COLUMN IF NOT EXISTS `commission_basis` ENUM('added_to_asking', 'deducted_from_proceeds', 'percentage_of_buyer_price') NOT NULL DEFAULT 'added_to_asking' AFTER `commission_mode`;

-- 3. Extend properties table with pricing & commission engine fields
ALTER TABLE `properties`
    ADD COLUMN IF NOT EXISTS `asking_price` DECIMAL(15,2) NULL AFTER `price`,
    ADD COLUMN IF NOT EXISTS `buyer_price` DECIMAL(15,2) NULL AFTER `asking_price`,
    ADD COLUMN IF NOT EXISTS `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `buyer_price`,
    ADD COLUMN IF NOT EXISTS `fixed_markup` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `commission_rate`,
    ADD COLUMN IF NOT EXISTS `commission_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `fixed_markup`,
    ADD COLUMN IF NOT EXISTS `seller_expected_net` DECIMAL(15,2) NULL AFTER `commission_amount`,
    ADD COLUMN IF NOT EXISTS `commission_calculation_basis` VARCHAR(50) NOT NULL DEFAULT 'added_to_asking' AFTER `seller_expected_net`,
    ADD COLUMN IF NOT EXISTS `commission_mode` VARCHAR(50) NOT NULL DEFAULT 'percentage' AFTER `commission_calculation_basis`,
    ADD COLUMN IF NOT EXISTS `pricing_rule_snapshot` LONGTEXT NULL AFTER `commission_mode`;

-- Backfill existing properties so asking_price and buyer_price are not null
UPDATE `properties` SET `asking_price` = `price` WHERE `asking_price` IS NULL;
UPDATE `properties` SET `buyer_price` = `price` WHERE `buyer_price` IS NULL;
UPDATE `properties` SET `seller_expected_net` = `price` WHERE `seller_expected_net` IS NULL;

-- 4. Create Company Receiving Bank Accounts table
CREATE TABLE IF NOT EXISTS `company_bank_accounts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `account_name` VARCHAR(150) NOT NULL,
    `bank_name` VARCHAR(100) NOT NULL,
    `account_number` VARCHAR(50) NOT NULL,
    `account_type` VARCHAR(50) NOT NULL DEFAULT 'current',
    `currency` VARCHAR(5) NOT NULL DEFAULT 'NGN',
    `payment_instructions` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_bank_active` (`is_active`, `currency`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create Property Transactions table (Afriserva Transaction Desk)
CREATE TABLE IF NOT EXISTS `property_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `transaction_reference` VARCHAR(50) NOT NULL UNIQUE,
    `property_id` INT UNSIGNED NOT NULL,
    `buyer_id` INT UNSIGNED NOT NULL,
    `seller_id` INT UNSIGNED NOT NULL,
    `agent_id` INT UNSIGNED NULL,
    `assigned_officer_id` INT UNSIGNED NULL,
    `enquiry_id` INT UNSIGNED NULL,
    `stage` ENUM(
        'enquiry_received',
        'under_review',
        'viewing_requested',
        'viewing_scheduled',
        'offer_submitted',
        'offer_accepted',
        'payment_instructions_issued',
        'payment_evidence_submitted',
        'payment_verified',
        'seller_settlement_pending',
        'seller_settled',
        'completed',
        'cancelled',
        'disputed',
        'refunded'
    ) NOT NULL DEFAULT 'enquiry_received',
    `payment_status` ENUM(
        'not_payable',
        'awaiting_transfer',
        'evidence_submitted',
        'under_verification',
        'partially_received',
        'payment_confirmed',
        'payment_rejected',
        'refund_requested',
        'refund_processing',
        'refunded',
        'payment_disputed'
    ) NOT NULL DEFAULT 'not_payable',
    `settlement_status` ENUM(
        'not_applicable',
        'pending',
        'processing',
        'settled',
        'disputed',
        'on_hold'
    ) NOT NULL DEFAULT 'not_applicable',
    `currency` VARCHAR(5) NOT NULL DEFAULT 'NGN',
    `asking_price` DECIMAL(15,2) NOT NULL,
    `buyer_price` DECIMAL(15,2) NOT NULL,
    `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `commission_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `fixed_markup` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `seller_expected_net` DECIMAL(15,2) NOT NULL,
    `amount_paid` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `pricing_snapshot` LONGTEXT NOT NULL,
    `bank_account_snapshot` LONGTEXT NULL,
    `payment_deadline` DATETIME NULL,
    `admin_notes` TEXT NULL,
    `buyer_notes` TEXT NULL,
    `seller_notes` TEXT NULL,
    `verified_by` INT UNSIGNED NULL,
    `verified_at` DATETIME NULL,
    `reconciliation_reference` VARCHAR(100) NULL,
    `settled_by` INT UNSIGNED NULL,
    `settled_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_txn_ref` (`transaction_reference`),
    INDEX `idx_txn_stage` (`stage`),
    INDEX `idx_txn_payment_status` (`payment_status`),
    INDEX `idx_txn_buyer` (`buyer_id`),
    INDEX `idx_txn_seller` (`seller_id`),
    INDEX `idx_txn_property` (`property_id`),
    INDEX `idx_txn_officer` (`assigned_officer_id`),
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`buyer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`agent_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_officer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`settled_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create Transaction Payment Evidence table
CREATE TABLE IF NOT EXISTS `transaction_payment_evidence` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `transaction_id` INT UNSIGNED NOT NULL,
    `buyer_id` INT UNSIGNED NOT NULL,
    `amount_paid` DECIMAL(15,2) NOT NULL,
    `payment_date` DATE NOT NULL,
    `payment_channel` VARCHAR(100) NOT NULL DEFAULT 'bank_transfer',
    `reference_or_session_id` VARCHAR(150) NULL,
    `receipt_file_url` VARCHAR(255) NOT NULL,
    `receipt_original_name` VARCHAR(255) NULL,
    `buyer_notes` TEXT NULL,
    `review_status` ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
    `reviewed_by` INT UNSIGNED NULL,
    `reviewed_at` DATETIME NULL,
    `admin_feedback` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ev_txn` (`transaction_id`),
    INDEX `idx_ev_status` (`review_status`),
    FOREIGN KEY (`transaction_id`) REFERENCES `property_transactions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`buyer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Create Transaction Timeline & Audit table
CREATE TABLE IF NOT EXISTS `transaction_timeline` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `transaction_id` INT UNSIGNED NOT NULL,
    `actor_id` INT UNSIGNED NULL,
    `actor_role` VARCHAR(50) NOT NULL DEFAULT 'system',
    `action` VARCHAR(100) NOT NULL,
    `from_stage` VARCHAR(50) NULL,
    `to_stage` VARCHAR(50) NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `metadata` LONGTEXT NULL,
    `is_internal_only` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tt_txn` (`transaction_id`),
    INDEX `idx_tt_action` (`action`),
    FOREIGN KEY (`transaction_id`) REFERENCES `property_transactions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`actor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Seed Default Commission Plan (Idempotent)
INSERT INTO `subscription_plans` (
    `name`, `slug`, `plan_type`, `description`, `price`, `currency`, `billing_interval`,
    `listing_allowance`, `featured_allowance`, `listing_duration_days`, `grace_period_days`,
    `analytics_level`, `renewal_policy`, `lead_price`, `verification_included`, `team_members_limit`,
    `is_active`, `is_default`, `commission_rate`, `fixed_markup`, `commission_mode`, `commission_basis`,
    `display_order`, `created_at`
)
SELECT 
    'Afriserva Commission Plan',
    'afriserva-commission-plan',
    'commission',
    'No subscription fee. List and market properties freely while Afriserva manages transactions, verification, and escrow coordination with a transparent commission.',
    0.00,
    'NGN',
    'monthly',
    500,
    0,
    365,
    30,
    'standard',
    'flexible',
    0.00,
    0,
    5,
    1,
    1,
    5.00,
    0.00,
    'percentage',
    'added_to_asking',
    0,
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `subscription_plans` WHERE `slug` = 'afriserva-commission-plan'
);

-- Ensure other plans have is_default = 0
UPDATE `subscription_plans` SET `is_default` = 0 WHERE `slug` != 'afriserva-commission-plan';
UPDATE `subscription_plans` SET `is_default` = 1, `price` = 0.00, `plan_type` = 'commission' WHERE `slug` = 'afriserva-commission-plan';

-- 9. Seed Official Receiving Bank Account (Idempotent)
INSERT INTO `company_bank_accounts` (
    `account_name`, `bank_name`, `account_number`, `account_type`, `currency`,
    `payment_instructions`, `is_active`, `created_at`
)
SELECT
    'Afriserva Real Estate (Cloud Current Int Ltd)',
    'Zenith Bank Plc',
    '1018894220',
    'corporate_current',
    'NGN',
    'Ensure you specify your exact Transaction Reference in the transfer narration. Only transfers to this official corporate account are recognized and verified by Afriserva.',
    1,
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `company_bank_accounts` WHERE `account_number` = '1018894220'
);

-- 10. Seed System Settings for Commission & Transaction Desk (Idempotent)
INSERT IGNORE INTO `system_settings` (`key`, `value`, `group_name`, `created_at`) VALUES
('commission_default_rate', '5.00', 'commission', NOW()),
('commission_default_fixed_markup', '0.00', 'commission', NOW()),
('commission_default_mode', 'percentage', 'commission', NOW()),
('commission_default_basis', 'added_to_asking', 'commission', NOW()),
('commission_require_manual_review', '1', 'commission', NOW()),
('commission_enable_manual_transfer', '1', 'commission', NOW()),
('commission_rounding_mode', 'round', 'commission', NOW()),
('commission_min_amount', '0.00', 'commission', NOW()),
('commission_max_amount', '0.00', 'commission', NOW());

SET FOREIGN_KEY_CHECKS = 1;
