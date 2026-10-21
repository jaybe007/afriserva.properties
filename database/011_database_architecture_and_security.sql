-- ============================================================================
-- AFRISERVA PROPERTIES - MIGRATION 011: DATABASE ARCHITECTURE & SECURITY
-- FULL NORMALIZATION, AUDIT TRAILS, MODERATION, SEARCH ALERTS, SCHEDULED TASKS
-- ============================================================================

-- 1. Property Owners Entity Ledger
CREATE TABLE IF NOT EXISTS `property_owners` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `legal_entity_name` VARCHAR(191) NOT NULL,
    `is_corporate` TINYINT(1) NOT NULL DEFAULT 0,
    `rc_number` VARCHAR(50) NULL COMMENT 'CAC Registration Number if corporate',
    `tax_identification_number` VARCHAR(50) NULL COMMENT 'FIRS Tax ID',
    `verified_identity_document` VARCHAR(255) NULL,
    `ownership_verification_status` ENUM('pending', 'verified', 'flagged') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_owner_user` (`user_id`),
    INDEX `idx_owner_status` (`ownership_verification_status`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Property Locations Normalized Structure
CREATE TABLE IF NOT EXISTS `property_locations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `property_id` INT UNSIGNED NOT NULL,
    `country_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `state_id` INT UNSIGNED NOT NULL,
    `city_id` INT UNSIGNED NULL,
    `local_government_id` INT UNSIGNED NULL,
    `neighbourhood_id` INT UNSIGNED NULL,
    `address` VARCHAR(255) NOT NULL,
    `street_name` VARCHAR(150) NULL,
    `building_number` VARCHAR(50) NULL,
    `postal_code` VARCHAR(20) NULL,
    `latitude` DECIMAL(10,8) NULL,
    `longitude` DECIMAL(11,8) NULL,
    `is_address_obfuscated` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_loc_property` (`property_id`),
    INDEX `idx_loc_geo` (`state_id`, `local_government_id`, `neighbourhood_id`),
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
    FOREIGN KEY (`state_id`) REFERENCES `states` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Search Alerts
CREATE TABLE IF NOT EXISTS `search_alerts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `criteria_json` LONGTEXT NOT NULL,
    `frequency` ENUM('instant', 'daily', 'weekly') NOT NULL DEFAULT 'daily',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_notified_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_alert_user` (`user_id`),
    INDEX `idx_alert_active` (`is_active`, `frequency`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Moderation Actions Log
CREATE TABLE IF NOT EXISTS `moderation_actions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `moderator_id` INT UNSIGNED NOT NULL,
    `target_type` ENUM('property', 'user', 'review', 'agency', 'enquiry') NOT NULL,
    `target_id` INT UNSIGNED NOT NULL,
    `action` ENUM('approved', 'rejected', 'suspended', 'flagged', 'edited', 'restored') NOT NULL,
    `reason` VARCHAR(255) NOT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_mod_target` (`target_type`, `target_id`),
    INDEX `idx_mod_moderator` (`moderator_id`),
    FOREIGN KEY (`moderator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Verification Reviews
CREATE TABLE IF NOT EXISTS `verification_reviews` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `verification_id` INT UNSIGNED NOT NULL,
    `reviewer_id` INT UNSIGNED NOT NULL,
    `decision` ENUM('approved', 'rejected', 'request_more_info') NOT NULL,
    `comments` TEXT NULL,
    `reviewed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_v_review` (`verification_id`, `reviewer_id`),
    FOREIGN KEY (`verification_id`) REFERENCES `property_verifications` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Email Communication Logs
CREATE TABLE IF NOT EXISTS `email_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `recipient_email` VARCHAR(191) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `template_name` VARCHAR(100) NOT NULL,
    `status` ENUM('queued', 'sent', 'failed') NOT NULL DEFAULT 'sent',
    `error_message` TEXT NULL,
    `sent_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_recipient` (`recipient_email`),
    INDEX `idx_email_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Blog Categories & Blog Posts (Market Intelligence & Guides)
CREATE TABLE IF NOT EXISTS `blog_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NOT NULL,
    `author_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `excerpt` TEXT NOT NULL,
    `content` LONGTEXT NOT NULL,
    `cover_image_url` VARCHAR(255) NOT NULL,
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'published',
    `published_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_blog_slug` (`slug`),
    INDEX `idx_blog_status` (`status`, `published_at`),
    FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Scheduled Automated Tasks Queue (Cron & Background Jobs)
CREATE TABLE IF NOT EXISTS `scheduled_tasks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `task_name` VARCHAR(100) NOT NULL UNIQUE,
    `command` VARCHAR(255) NOT NULL,
    `cron_expression` VARCHAR(50) NOT NULL DEFAULT '0 0 * * *',
    `last_run_at` DATETIME NULL,
    `next_run_at` DATETIME NULL,
    `status` ENUM('idle', 'running', 'success', 'failed') NOT NULL DEFAULT 'idle',
    `last_output` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. User Identity Verifications Structured History
CREATE TABLE IF NOT EXISTS `user_verifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `verification_type` ENUM('email', 'phone', 'nin', 'bvn', 'lasrera', 'cac') NOT NULL,
    `identifier_hash` VARCHAR(191) NULL,
    `status` ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
    `reviewer_notes` TEXT NULL,
    `verified_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_uverif_user` (`user_id`, `verification_type`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Compatibility Views for Exact Prompt Schema Names
CREATE OR REPLACE VIEW `local_government_areas` AS 
SELECT id, state_id, name FROM `local_governments`;

CREATE OR REPLACE VIEW `refunds` AS 
SELECT id, payment_id, user_id, amount, currency, reason, status, admin_notes, reviewed_by, reviewed_at, created_at 
FROM `refund_requests`;

CREATE OR REPLACE VIEW `notifications` AS 
SELECT id, user_id, title, message, type, link_url, is_read, read_at, created_at 
FROM `in_app_notifications`;

-- Seed baseline scheduled tasks
INSERT IGNORE INTO `scheduled_tasks` (`task_name`, `command`, `cron_expression`, `status`, `is_active`) VALUES
('check_expiring_leases', 'php console.php leases:check-expiring', '0 8 * * *', 'idle', 1),
('reconcile_pending_payments', 'php console.php payments:reconcile', '*/15 * * * *', 'idle', 1),
('process_search_alerts', 'php console.php search:dispatch-alerts', '0 9 * * *', 'idle', 1),
('purge_expired_sessions', 'php console.php sessions:clean', '0 2 * * *', 'idle', 1);

-- Seed initial blog categories and posts
INSERT IGNORE INTO `blog_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Legal & Regulatory', 'legal-regulatory', 'Nigerian Land Use Act, LASRERA guidelines, and title documentation.'),
(2, 'Market Trends & Analysis', 'market-trends', 'Macroeconomic indices, yield forecasts, and currency hedging strategies.');

INSERT IGNORE INTO `blog_posts` (`id`, `category_id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `cover_image_url`, `status`, `published_at`) VALUES
(1, 1, 1, 'Navigating the Lagos State Real Estate Regulatory Authority (LASRERA) Mandate', 'navigating-lasrera-mandate-2026', 'Understanding statutory compliance obligations for agents, developers, and brokerages operating in Lagos State.', 'Comprehensive legal advisory regarding LASRERA practitioner licenses, escrow account requirements, and anti-fraud provisions under the Lagos Tenancy Law 2011.', 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=800&q=80', 'published', NOW()),
(2, 2, 1, 'Prime Commercial Real Estate Yields in Victoria Island and Ikoyi: 2026 Outlook', 'prime-commercial-yields-ikoyi-vi-2026', 'In-depth capital appreciation and rental yield analysis across prime Grade-A commercial developments.', 'Institutional investors continue to favor high-grade commercial developments along Ozumba Mbadiwe and Alfred Rewane road with long-term US Dollar indexed leases.', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80', 'published', NOW());
