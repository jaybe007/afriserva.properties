-- ============================================================================
-- AFRISERVA PROPERTIES - MIGRATION 010: SECTIONS 13 - 16
-- ENQUIRIES & MESSAGING, MONETIZATION & PLANS, PAYMENT GATEWAY & REFUNDS, RENTAL MANAGEMENT
-- ============================================================================

-- 1. In-App Notifications
CREATE TABLE IF NOT EXISTS `in_app_notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(191) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('enquiry', 'message', 'viewing', 'payment', 'subscription', 'lease', 'maintenance', 'system') NOT NULL DEFAULT 'system',
    `link_url` VARCHAR(255) NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `read_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_user` (`user_id`),
    INDEX `idx_notif_read` (`is_read`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Agent Availability Slots for Viewings / Appointments
CREATE TABLE IF NOT EXISTS `agent_availabilities` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `day_of_week` TINYINT UNSIGNED NOT NULL COMMENT '0=Sunday, 1=Monday... 6=Saturday',
    `start_time` TIME NOT NULL DEFAULT '09:00:00',
    `end_time` TIME NOT NULL DEFAULT '17:00:00',
    `slot_duration_minutes` INT UNSIGNED NOT NULL DEFAULT 45,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_avail_user_day` (`user_id`, `day_of_week`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Enhance Enquiries with Privacy, Spam Scoring, and First Response Time Tracking
ALTER TABLE `enquiries` 
    ADD COLUMN IF NOT EXISTS `first_responded_at` DATETIME NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `response_time_seconds` INT UNSIGNED NULL AFTER `first_responded_at`,
    ADD COLUMN IF NOT EXISTS `is_spam` TINYINT(1) NOT NULL DEFAULT 0 AFTER `response_time_seconds`,
    ADD COLUMN IF NOT EXISTS `spam_reason` VARCHAR(255) NULL AFTER `is_spam`,
    ADD COLUMN IF NOT EXISTS `contact_unlocked` TINYINT(1) NOT NULL DEFAULT 0 AFTER `spam_reason`,
    ADD COLUMN IF NOT EXISTS `unlocked_at` DATETIME NULL AFTER `contact_unlocked`;

-- 4. Enhance Subscription Plans with Commercial Policies
ALTER TABLE `subscription_plans`
    ADD COLUMN IF NOT EXISTS `listing_duration_days` INT NOT NULL DEFAULT 30 AFTER `featured_allowance`,
    ADD COLUMN IF NOT EXISTS `grace_period_days` INT NOT NULL DEFAULT 7 AFTER `listing_duration_days`,
    ADD COLUMN IF NOT EXISTS `analytics_level` ENUM('basic', 'standard', 'advanced', 'realtime') NOT NULL DEFAULT 'basic' AFTER `grace_period_days`,
    ADD COLUMN IF NOT EXISTS `renewal_policy` ENUM('manual', 'auto_charge', 'flexible') NOT NULL DEFAULT 'flexible' AFTER `analytics_level`,
    ADD COLUMN IF NOT EXISTS `upgrade_rules` TEXT NULL AFTER `renewal_policy`,
    ADD COLUMN IF NOT EXISTS `lead_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `upgrade_rules`,
    ADD COLUMN IF NOT EXISTS `verification_included` TINYINT(1) NOT NULL DEFAULT 0 AFTER `lead_price`;

-- 5. Banner Advertisements
CREATE TABLE IF NOT EXISTS `banner_advertisements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `advertiser_name` VARCHAR(150) NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `placement` ENUM('homepage_top', 'search_sidebar', 'property_detail', 'footer_strip') NOT NULL DEFAULT 'homepage_top',
    `image_url` VARCHAR(255) NOT NULL,
    `target_url` VARCHAR(255) NOT NULL,
    `impressions_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `clicks_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `starts_at` DATETIME NOT NULL,
    `ends_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_banner_placement` (`placement`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Refund Workflows
CREATE TABLE IF NOT EXISTS `refund_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `payment_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `currency` VARCHAR(5) NOT NULL DEFAULT 'NGN',
    `reason` TEXT NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected', 'processed') NOT NULL DEFAULT 'pending',
    `admin_notes` TEXT NULL,
    `reviewed_by` INT UNSIGNED NULL,
    `reviewed_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_refund_status` (`status`),
    INDEX `idx_refund_user` (`user_id`),
    FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Rental Applications & Tenant Screening
CREATE TABLE IF NOT EXISTS `rental_applications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `property_id` INT UNSIGNED NOT NULL,
    `applicant_id` INT UNSIGNED NOT NULL,
    `landlord_id` INT UNSIGNED NOT NULL,
    `annual_income` DECIMAL(14,2) NOT NULL,
    `employer_name` VARCHAR(191) NOT NULL,
    `employment_status` ENUM('employed', 'self_employed', 'business_owner', 'contractor', 'other') NOT NULL DEFAULT 'employed',
    `guarantor_name` VARCHAR(150) NOT NULL,
    `guarantor_phone` VARCHAR(40) NOT NULL,
    `guarantor_email` VARCHAR(191) NOT NULL,
    `guarantor_relationship` VARCHAR(100) NOT NULL,
    `status` ENUM('submitted', 'screening', 'approved', 'rejected', 'withdrawn') NOT NULL DEFAULT 'submitted',
    `screening_status` ENUM('pending', 'passed', 'flagged') NOT NULL DEFAULT 'pending',
    `screening_notes` TEXT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_app_property` (`property_id`),
    INDEX `idx_app_applicant` (`applicant_id`),
    INDEX `idx_app_landlord` (`landlord_id`),
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`applicant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Property Inspections
CREATE TABLE IF NOT EXISTS `property_inspections` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `property_id` INT UNSIGNED NOT NULL,
    `lease_id` INT UNSIGNED NULL,
    `inspector_id` INT UNSIGNED NOT NULL,
    `inspection_date` DATE NOT NULL,
    `type` ENUM('move_in', 'routine', 'move_out') NOT NULL DEFAULT 'routine',
    `findings` TEXT NOT NULL,
    `condition_rating` ENUM('excellent', 'good', 'fair', 'poor') NOT NULL DEFAULT 'good',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`lease_id`) REFERENCES `leases` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tenancy Move-in / Move-out Checklists
CREATE TABLE IF NOT EXISTS `tenancy_checklists` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `lease_id` INT UNSIGNED NOT NULL,
    `checklist_type` ENUM('move_in', 'move_out') NOT NULL DEFAULT 'move_in',
    `keys_issued_count` INT NOT NULL DEFAULT 2,
    `meter_reading_electricity` VARCHAR(100) NULL,
    `meter_reading_water` VARCHAR(100) NULL,
    `inventory_condition_notes` TEXT NULL,
    `tenant_acknowledged` TINYINT(1) NOT NULL DEFAULT 0,
    `acknowledged_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`lease_id`) REFERENCES `leases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Enhance Maintenance Requests with Assignment & Management Fee
ALTER TABLE `maintenance_requests`
    ADD COLUMN IF NOT EXISTS `assigned_contractor_name` VARCHAR(150) NULL AFTER `cost`,
    ADD COLUMN IF NOT EXISTS `assigned_contractor_phone` VARCHAR(50) NULL AFTER `assigned_contractor_name`,
    ADD COLUMN IF NOT EXISTS `management_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `assigned_contractor_phone`,
    ADD COLUMN IF NOT EXISTS `scheduled_date` DATE NULL AFTER `management_fee`,
    ADD COLUMN IF NOT EXISTS `resolved_at` DATETIME NULL AFTER `scheduled_date`;
