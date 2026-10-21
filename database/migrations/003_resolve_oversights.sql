-- Migration 003: Address Regulatory, Privacy, Short-let, and Multi-Currency Oversights

-- 1. Add LASRERA & SCUML Regulatory Compliance Columns
ALTER TABLE agent_profiles 
    ADD COLUMN IF NOT EXISTS lasrera_reg_number VARCHAR(100) NULL AFTER license_no;

ALTER TABLE agencies 
    ADD COLUMN IF NOT EXISTS lasrera_reg_number VARCHAR(100) NULL AFTER cac_number,
    ADD COLUMN IF NOT EXISTS scuml_reg_number VARCHAR(100) NULL AFTER lasrera_reg_number;

-- 2. Create Exchange Rates Table for USD/NGN/GBP Dual Currency Conversion
CREATE TABLE IF NOT EXISTS exchange_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    base_currency VARCHAR(5) NOT NULL,
    target_currency VARCHAR(5) NOT NULL,
    exchange_rate DECIMAL(15, 4) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_currency_pair (base_currency, target_currency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO exchange_rates (base_currency, target_currency, exchange_rate) VALUES
('USD', 'NGN', 1520.0000),
('GBP', 'NGN', 1980.0000),
('EUR', 'NGN', 1650.0000)
ON DUPLICATE KEY UPDATE exchange_rate = VALUES(exchange_rate);

-- 3. Short-Let Dedicated Booking & Availability Conflict Engine
CREATE TABLE IF NOT EXISTS short_let_reservations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_code VARCHAR(30) NOT NULL UNIQUE,
    property_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    guests_count SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    total_nights INT UNSIGNED NOT NULL,
    nightly_rate DECIMAL(15, 2) NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL,
    security_deposit DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(15, 2) NOT NULL,
    currency VARCHAR(5) NOT NULL DEFAULT 'NGN',
    status ENUM('pending', 'confirmed', 'checked_in', 'completed', 'cancelled') NOT NULL DEFAULT 'confirmed',
    guest_name VARCHAR(150) NOT NULL,
    guest_email VARCHAR(191) NOT NULL,
    guest_phone VARCHAR(40) NOT NULL,
    special_requests TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reservation_dates (property_id, check_in_date, check_out_date, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Enable Address Obfuscation on Ultra-Luxury Residential Listings (Physical Security)
UPDATE properties 
SET is_address_hidden = 1 
WHERE slug = 'ultra-luxury-6-bedroom-waterfront-mansion-banana-island';

-- 5. Seed Real Regulatory Compliance Data for Existing Demo Agents & Agencies
UPDATE agencies 
SET lasrera_reg_number = 'LASRERA/BR/2022/0488',
    scuml_reg_number = 'SCUML/RN/DN-FBP/2021/00842'
WHERE slug = 'prestige-prime-luxury-realty';

UPDATE agent_profiles 
SET lasrera_reg_number = 'LASRERA/IND/2023/1109'
WHERE user_id IN (SELECT id FROM users WHERE email = 'chioma@prestigeprime.ng');
