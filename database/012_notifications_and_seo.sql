-- ============================================================================
-- AFRISERVA PROPERTIES - MIGRATION 012: NOTIFICATION PREFERENCES & SEO REDIRECTS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `user_notification_preferences` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `event_type` VARCHAR(60) NOT NULL,
    `email_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `sms_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `in_app_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `whatsapp_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_user_event` (`user_id`, `event_type`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `url_redirects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `source_path` VARCHAR(255) NOT NULL UNIQUE,
    `target_path` VARCHAR(255) NOT NULL,
    `status_code` SMALLINT UNSIGNED NOT NULL DEFAULT 301,
    `hits_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_redirect_source` (`source_path`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
