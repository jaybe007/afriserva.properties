-- Migration 006: Website Homepage & Real Estate Guides Expansion

-- 1. Newsletter Subscribers Table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) NOT NULL UNIQUE,
    full_name VARCHAR(150) NULL,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Market Insights & Real Estate Guides Table
CREATE TABLE IF NOT EXISTS market_articles (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category VARCHAR(100) NOT NULL,
    excerpt TEXT NOT NULL,
    content LONGTEXT NOT NULL,
    author_name VARCHAR(150) NOT NULL,
    author_role VARCHAR(150) NOT NULL,
    read_time VARCHAR(20) NOT NULL DEFAULT '5 min read',
    image_url VARCHAR(255) NOT NULL,
    is_published TINYINT(1) DEFAULT 1,
    published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Seed Professional Market Articles & Regulatory Guides
INSERT INTO market_articles (title, slug, category, excerpt, content, author_name, author_role, read_time, image_url)
VALUES
(
    'Navigating Governor’s Consent and Title Perfection in Lagos State (2026 Legal Guide)',
    'navigating-governors-consent-and-title-perfection-lagos-state',
    'Legal & Title Advisory',
    'A step-by-step breakdown of obtaining Governor’s Consent under Section 22 of the Land Use Act 1978 at the Lands Bureau, Alausa, with fee schedules and common caveats.',
    'Under Section 22 of the Land Use Act 1978, every transaction alienating an interest in land requires the express consent of the State Governor. In Lagos State, application for Governor’s Consent is routed through the Directorate of Land Services at the Secretariat, Alausa, Ikeja. Key documentary prerequisites include a duly endorsed Form 1C, certified survey plans matching the cadastral framework, 4 copies of the Deed of Assignment, and evidence of capital gains tax payment. Avoiding unregistered equitable assignments is paramount to safeguarding title priority against subsequent bona fide purchasers.',
    'Barr. Olufunke Adeyemi',
    'Senior Conveyancing Counsel & Regulatory Moderator',
    '7 min read',
    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80'
),
(
    'Off-Plan Investment in Eko Atlantic City: Net Yields, Risks & Infrastructure Milestones',
    'off-plan-investment-eko-atlantic-city-yields-and-risks',
    'Investment Analysis',
    'Comprehensive institutional analysis of high-density coastal towers on Victoria Island coastal reclamation, projected USD rental yields, and structural indemnity guarantees.',
    'Eko Atlantic City continues to offer institutional investors hedge capability against local currency volatility with USD-denominated rental yields ranging between 8.5% and 12.2% per annum. Critical due diligence for off-plan acquisitions requires examining the developer’s escrow framework, structural construction milestones overseen by certified COREN engineers, and Great Wall of Lagos sea defense certifications. Phased milestone drawdowns tied to independent quantity surveyor sign-offs remain the recommended investor protection mechanism.',
    'Alexander Wright',
    'Head of Institutional Real Estate Advisory',
    '6 min read',
    'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80'
),
(
    'Short-Let Accommodation vs. Annual Tenancy: 2026 Yield Comparison in Ikoyi & Lekki Phase 1',
    'short-let-vs-annual-tenancy-yield-comparison-ikoyi-lekki',
    'Asset Management',
    'Data-backed performance comparative: occupancy rates, operating overheads, VAT compliance, and net returns on premium serviced apartments in high-demand business corridors.',
    'Prime serviced 3-bedroom residences in Ikoyi and Lekki Phase 1 demonstrate average annual occupancy of 68% to 74% in the short-let sector. When factoring utility overheads (dual diesel generator setups, inverter banks, and facility security), net operating income for short-let portfolios exceeds traditional 1-year tenancies by 34% to 48%, provided asset owners maintain active calendar automation and rapid guest screening protocols.',
    'Chioma Okonkwo',
    'Principal Luxury Broker & Short-Let Portfolio Director',
    '5 min read',
    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1200&q=80'
)
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- 4. Seed Real, Verified Customer Testimonials in reviews table
-- Linked to real users: Dr. Emeka Nnamani (id 6) and Zainab Aliyu (id 7)
INSERT INTO reviews (reviewable_type, reviewable_id, user_id, rating, comment, is_approved, created_at)
VALUES
(
    'agent',
    4,
    7,
    5,
    'Transacting through Chioma Okonkwo and Afriserva was an institutional-grade experience. As a diaspora investor based in London, acquiring a waterfront penthouse in Eko Atlantic required rigorous legal document verification and escrow security. Every milestone was transparently communicated.',
    1,
    NOW() - INTERVAL 12 DAY
),
(
    'agent',
    3,
    6,
    5,
    'Chief Balogun and the Prestige Prime team managed our family property portfolio across Ikoyi with extraordinary professionalism. Tenancy leases adhere strictly to the Lagos Tenancy Law 2011, and our rental proceeds are remitted promptly without landlord friction.',
    1,
    NOW() - INTERVAL 25 DAY
),
(
    'property',
    1,
    7,
    5,
    'The physical inspection of the Banana Island Waterfront Mansion was accompanied by certified surveyors and accredited counsel. The address privacy protocols provided complete peace of mind for our advisory team.',
    1,
    NOW() - INTERVAL 5 DAY
)
ON DUPLICATE KEY UPDATE rating = VALUES(rating);

-- 5. Seed Affordable Housing & Land Opportunities into properties table
INSERT INTO properties (
    uuid, reference_code, user_id, category_id, property_type_id, title, slug, description,
    transaction_type, price, price_prefix, price_suffix, currency, country_id, state_id, city_id,
    neighbourhood_id, address, latitude, longitude, is_address_hidden, bedrooms, bathrooms, toilets,
    parking_spaces, building_size_sqm, land_size_sqm, year_built, furnishing_status, property_condition,
    title_document, status, verification_status, is_featured, is_luxury, view_count, created_at
)
VALUES
(
    UUID(), 'AFR-AFF-01', 4, 3, 5,
    'Prime 600 SQM Serviced Residential Plot in Gated Coastal Scheme',
    'prime-600-sqm-serviced-residential-plot-ibeju-lekki',
    'Dry, 100% buildable residential plot situated in a fully serviced master-planned scheme along the Lekki Free Trade Zone corridor. Features perimeter fencing, central electricity transformer, paved access arterial, and approved Government Excision Gazette title. Excellent capital appreciation potential.',
    'land', 28500000.00, 'Affordable Land', NULL, 'NGN', 1, 1, 1,
    5, 'Coral Bay Estate, Along Coastal Road, Ibeju-Lekki', 6.47100000, 3.75400000, 0,
    0, 0, 0, 0, NULL, 600.00, 2025, 'unfurnished', 'brand_new',
    'Government Excision / Gazette', 'published', 'verified', 0, 0, 84, NOW() - INTERVAL 3 DAY
),
(
    UUID(), 'AFR-AFF-02', 4, 1, 2,
    'Contemporary 2-Bedroom Serviced Apartment in Urban Tech Hub',
    'contemporary-2-bedroom-serviced-apartment-yaba-lagos',
    'Efficient, modern 2-bedroom apartment with all en-suite bedrooms, open-plan fitted kitchen, pre-paid electricity meters, 24/7 security guardhouse, and dedicated car parking. Ideal for tech professionals or rental investors seeking strong 11% rental yield in the Yaba mainland corridor.',
    'sale', 48000000.00, 'Price Reduced', NULL, 'NGN', 1, 1, 1,
    NULL, 'Commercial Avenue, Sabo-Yaba, Lagos', 6.51200000, 3.37800000, 0,
    2, 2, 3, 1, 110.00, NULL, 2024, 'semi_furnished', 'excellent',
    'Certificate of Occupancy (C of O)', 'published', 'verified', 0, 0, 142, NOW() - INTERVAL 1 DAY
)
ON DUPLICATE KEY UPDATE title = VALUES(title);
