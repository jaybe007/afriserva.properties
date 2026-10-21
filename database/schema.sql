-- Afriserva Properties: Enterprise Relational Database Schema
-- Compatible with MySQL 8.0+ / MariaDB 10.4+ (InnoDB Engine)

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS maintenance_requests;
DROP TABLE IF EXISTS rent_payments;
DROP TABLE IF EXISTS leases;
DROP TABLE IF EXISTS listing_reports;
DROP TABLE IF EXISTS payment_webhook_events;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS listing_promotions;
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS subscription_plans;
DROP TABLE IF EXISTS viewing_appointments;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS conversations;
DROP TABLE IF EXISTS enquiries;
DROP TABLE IF EXISTS recently_viewed_properties;
DROP TABLE IF EXISTS saved_searches;
DROP TABLE IF EXISTS favourites;
DROP TABLE IF EXISTS property_verifications;
DROP TABLE IF EXISTS property_documents;
DROP TABLE IF EXISTS property_amenities;
DROP TABLE IF EXISTS amenities;
DROP TABLE IF EXISTS property_videos;
DROP TABLE IF EXISTS property_images;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS property_types;
DROP TABLE IF EXISTS property_categories;
DROP TABLE IF EXISTS neighbourhoods;
DROP TABLE IF EXISTS local_governments;
DROP TABLE IF EXISTS cities;
DROP TABLE IF EXISTS states;
DROP TABLE IF EXISTS countries;
DROP TABLE IF EXISTS agent_profiles;
DROP TABLE IF EXISTS agency_members;
DROP TABLE IF EXISTS agencies;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS & ACCESS CONTROL
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password_hash VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(255) NULL,
    bio TEXT NULL,
    status ENUM('active', 'pending', 'suspended') NOT NULL DEFAULT 'active',
    email_verified_at DATETIME NULL,
    phone_verified_at DATETIME NULL,
    two_factor_secret VARCHAR(255) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_status (status),
    INDEX idx_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    label VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    label VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. AGENCIES & AGENT PROFILES
CREATE TABLE agencies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    email VARCHAR(191) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    logo_url VARCHAR(255) NULL,
    banner_url VARCHAR(255) NULL,
    cac_number VARCHAR(80) NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    description TEXT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_agency_slug (slug),
    INDEX idx_agency_verified (is_verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agency_members (
    agency_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    role_in_agency ENUM('principal', 'branch_manager', 'associate_agent') NOT NULL DEFAULT 'associate_agent',
    status ENUM('pending', 'active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (agency_id, user_id),
    FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE agent_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    agency_id INT UNSIGNED NULL,
    license_no VARCHAR(100) NULL,
    specialization VARCHAR(150) NULL,
    experience_years INT UNSIGNED NOT NULL DEFAULT 1,
    whatsapp_number VARCHAR(30) NULL,
    rating DECIMAL(3,2) NOT NULL DEFAULT 5.00,
    total_reviews INT UNSIGNED NOT NULL DEFAULT 0,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    bio TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE SET NULL,
    INDEX idx_agent_verified (is_verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. GEOGRAPHICAL STRUCTURE (Nigeria-focused & Extensible)
CREATE TABLE countries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    iso_code VARCHAR(3) NOT NULL UNIQUE,
    phone_code VARCHAR(10) NOT NULL,
    currency_code VARCHAR(5) NOT NULL DEFAULT 'NGN',
    currency_symbol VARCHAR(10) NOT NULL DEFAULT '₦',
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE states (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    country_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    code VARCHAR(10) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    INDEX idx_state_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    state_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE,
    INDEX idx_city_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE local_governments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    state_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE neighbourhoods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    city_id INT UNSIGNED NOT NULL,
    lga_id INT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE,
    FOREIGN KEY (lga_id) REFERENCES local_governments(id) ON DELETE SET NULL,
    INDEX idx_neighbourhood_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. CATEGORIES, TYPES & AMENITIES
CREATE TABLE property_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(60) NULL,
    description TEXT NULL,
    display_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE property_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT NULL,
    FOREIGN KEY (category_id) REFERENCES property_categories(id) ON DELETE CASCADE,
    INDEX idx_prop_type_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE amenities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(60) NULL,
    group_name ENUM('security', 'luxury', 'convenience', 'utilities', 'outdoor') NOT NULL DEFAULT 'convenience'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PROPERTY MASTER & ATTRIBUTES
CREATE TABLE properties (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    reference_code VARCHAR(30) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    agency_id INT UNSIGNED NULL,
    category_id INT UNSIGNED NOT NULL,
    property_type_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description LONGTEXT NOT NULL,
    transaction_type ENUM('sale', 'rent', 'short_let', 'commercial', 'land') NOT NULL DEFAULT 'sale',
    price DECIMAL(15, 2) NOT NULL,
    price_prefix VARCHAR(50) NULL,
    price_suffix VARCHAR(50) NULL,
    currency VARCHAR(5) NOT NULL DEFAULT 'NGN',
    country_id INT UNSIGNED NOT NULL,
    state_id INT UNSIGNED NOT NULL,
    city_id INT UNSIGNED NOT NULL,
    neighbourhood_id INT UNSIGNED NULL,
    address VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    is_address_hidden TINYINT(1) NOT NULL DEFAULT 0,
    bedrooms SMALLINT UNSIGNED NULL DEFAULT 0,
    bathrooms SMALLINT UNSIGNED NULL DEFAULT 0,
    toilets SMALLINT UNSIGNED NULL DEFAULT 0,
    parking_spaces SMALLINT UNSIGNED NULL DEFAULT 0,
    building_size_sqm DECIMAL(10, 2) NULL,
    land_size_sqm DECIMAL(10, 2) NULL,
    year_built SMALLINT UNSIGNED NULL,
    furnishing_status ENUM('unfurnished', 'semi_furnished', 'fully_furnished') NOT NULL DEFAULT 'unfurnished',
    property_condition ENUM('brand_new', 'excellent', 'good', 'needs_renovation', 'under_construction') NOT NULL DEFAULT 'brand_new',
    title_document VARCHAR(100) NULL,
    status ENUM('draft', 'pending_review', 'published', 'rejected', 'archived', 'sold', 'rented') NOT NULL DEFAULT 'published',
    verification_status ENUM('unverified', 'submitted', 'under_review', 'verified', 'rejected') NOT NULL DEFAULT 'unverified',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_luxury TINYINT(1) NOT NULL DEFAULT 0,
    featured_until DATETIME NULL,
    view_count INT UNSIGNED NOT NULL DEFAULT 0,
    favourite_count INT UNSIGNED NOT NULL DEFAULT 0,
    enquiry_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES property_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (property_type_id) REFERENCES property_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE RESTRICT,
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE RESTRICT,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT,
    FOREIGN KEY (neighbourhood_id) REFERENCES neighbourhoods(id) ON DELETE SET NULL,
    INDEX idx_prop_search (status, transaction_type, state_id, price),
    INDEX idx_prop_featured (is_featured, status),
    INDEX idx_prop_luxury (is_luxury, status),
    INDEX idx_prop_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE property_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    thumbnail_url VARCHAR(255) NULL,
    caption VARCHAR(200) NULL,
    is_cover TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_prop_img (property_id, is_cover)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE property_videos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    video_type ENUM('youtube', 'vimeo', 'mp4') NOT NULL DEFAULT 'youtube',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE property_amenities (
    property_id INT UNSIGNED NOT NULL,
    amenity_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (property_id, amenity_id),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE property_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    document_type ENUM('certificate_of_occupancy', 'governors_consent', 'deed_of_assignment', 'survey_plan', 'building_approval', 'id_card', 'other') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(150) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    verification_status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
    reviewer_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE property_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    reviewer_id INT UNSIGNED NOT NULL,
    previous_status VARCHAR(50) NOT NULL,
    new_status VARCHAR(50) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. USER INTERACTIONS (Favourites, Searches, History)
CREATE TABLE favourites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    property_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_fav (user_id, property_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE saved_searches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    criteria_json JSON NOT NULL,
    notify_email TINYINT(1) NOT NULL DEFAULT 1,
    frequency ENUM('instant', 'daily', 'weekly') NOT NULL DEFAULT 'daily',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE recently_viewed_properties (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    session_id VARCHAR(128) NOT NULL,
    property_id INT UNSIGNED NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_recent_user (user_id, viewed_at),
    INDEX idx_recent_session (session_id, viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. ENQUIRIES, MESSAGING & VIEWING APPOINTMENTS
CREATE TABLE enquiries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    agent_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    message TEXT NOT NULL,
    enquiry_type ENUM('purchase', 'rent', 'inspection', 'pricing_inquiry') NOT NULL DEFAULT 'inspection',
    status ENUM('new', 'contacted', 'qualified', 'viewing_scheduled', 'converted', 'closed', 'spam') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_enquiry_agent (agent_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NULL,
    user_one_id INT UNSIGNED NOT NULL,
    user_two_id INT UNSIGNED NOT NULL,
    last_message_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (user_one_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_two_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conv_users (user_one_id, user_two_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_msg_conv (conversation_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE viewing_appointments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    agent_id INT UNSIGNED NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    viewing_type ENUM('physical', 'virtual_video') NOT NULL DEFAULT 'physical',
    status ENUM('pending', 'confirmed', 'completed', 'rescheduled', 'cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_app_agent (agent_id, appointment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. MONETIZATION, SUBSCRIPTIONS & PAYMENTS
CREATE TABLE subscription_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    price DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(5) NOT NULL DEFAULT 'NGN',
    billing_interval ENUM('monthly', 'quarterly', 'yearly') NOT NULL DEFAULT 'monthly',
    listing_allowance INT NOT NULL DEFAULT 5,
    featured_allowance INT NOT NULL DEFAULT 1,
    team_members_limit INT NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    agency_id INT UNSIGNED NULL,
    plan_id INT UNSIGNED NOT NULL,
    status ENUM('active', 'past_due', 'cancelled', 'expired') NOT NULL DEFAULT 'active',
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    auto_renew TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE SET NULL,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL,
    tax DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(5) NOT NULL DEFAULT 'NGN',
    status ENUM('unpaid', 'paid', 'cancelled', 'refunded') NOT NULL DEFAULT 'unpaid',
    due_date DATE NOT NULL,
    paid_at DATETIME NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(100) NOT NULL UNIQUE,
    transaction_id VARCHAR(100) NULL,
    user_id INT UNSIGNED NOT NULL,
    invoice_id INT UNSIGNED NULL,
    gateway ENUM('paystack', 'flutterwave', 'bank_transfer', 'test_sandbox') NOT NULL DEFAULT 'paystack',
    amount DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(5) NOT NULL DEFAULT 'NGN',
    status ENUM('pending', 'successful', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    gateway_response TEXT NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    INDEX idx_pay_ref (reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payment_webhook_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gateway VARCHAR(50) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    reference VARCHAR(100) NOT NULL,
    payload_json LONGTEXT NOT NULL,
    is_processed TINYINT(1) NOT NULL DEFAULT 0,
    processed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_webhook_ref (reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE listing_promotions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    promotion_type ENUM('featured', 'boost', 'homepage_slider') NOT NULL DEFAULT 'featured',
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    amount_paid DECIMAL(12, 2) NOT NULL,
    payment_id INT UNSIGNED NULL,
    status ENUM('active', 'expired', 'cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. PROPERTY MANAGEMENT & RENTALS
CREATE TABLE leases (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    landlord_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    lease_start_date DATE NOT NULL,
    lease_end_date DATE NOT NULL,
    rent_amount DECIMAL(12, 2) NOT NULL,
    payment_frequency ENUM('monthly', 'quarterly', 'annually') NOT NULL DEFAULT 'annually',
    deposit_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    status ENUM('active', 'terminated', 'expired') NOT NULL DEFAULT 'active',
    agreement_document_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rent_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lease_id INT UNSIGNED NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATE NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'bank_transfer',
    status ENUM('pending', 'paid', 'overdue') NOT NULL DEFAULT 'pending',
    receipt_reference VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE maintenance_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    landlord_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'emergency') NOT NULL DEFAULT 'medium',
    status ENUM('submitted', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'submitted',
    cost DECIMAL(12, 2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. REPUTATION, AUDIT & SYSTEM SETTINGS
CREATE TABLE listing_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    reason VARCHAR(100) NOT NULL,
    details TEXT NOT NULL,
    status ENUM('pending', 'investigated', 'dismissed', 'action_taken') NOT NULL DEFAULT 'pending',
    admin_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reviewable_type ENUM('agent', 'property') NOT NULL,
    reviewable_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NOT NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(100) NULL,
    target_id INT UNSIGNED NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_action (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` LONGTEXT NULL,
    `group` VARCHAR(50) NOT NULL DEFAULT 'general',
    description VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
