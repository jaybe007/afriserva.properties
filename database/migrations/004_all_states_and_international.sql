-- Migration 004: All 36 Nigerian States + FCT, International Countries, Regional Units & Investment Opportunities

-- 1. Insert International Countries for Seamless Global Expansion
INSERT INTO countries (id, name, iso_code, phone_code, currency_code, currency_symbol, is_active) VALUES
(2, 'Ghana', 'GHA', '+233', 'GHS', 'GH₵', 1),
(3, 'Kenya', 'KEN', '+254', 'KES', 'KSh', 1),
(4, 'United Arab Emirates', 'ARE', '+971', 'AED', 'د.إ', 1),
(5, 'United Kingdom', 'GBR', '+44', 'GBP', '£', 1),
(6, 'United States', 'USA', '+1', 'USD', '$', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), currency_symbol = VALUES(currency_symbol);

-- 2. Populate All 36 States + Federal Capital Territory (37 total)
INSERT INTO states (country_id, name, slug, code, is_active) VALUES
(1, 'Abia', 'abia', 'AB', 1),
(1, 'Adamawa', 'adamawa', 'AD', 1),
(1, 'Akwa Ibom', 'akwa-ibom', 'AK', 1),
(1, 'Anambra', 'anambra', 'AN', 1),
(1, 'Bauchi', 'bauchi', 'BA', 1),
(1, 'Bayelsa', 'bayelsa', 'BY', 1),
(1, 'Benue', 'benue', 'BE', 1),
(1, 'Borno', 'borno', 'BO', 1),
(1, 'Cross River', 'cross-river', 'CR', 1),
(1, 'Delta', 'delta', 'DE', 1),
(1, 'Ebonyi', 'ebonyi', 'EB', 1),
(1, 'Edo', 'edo', 'ED', 1),
(1, 'Ekiti', 'ekiti', 'EK', 1),
(1, 'Enugu', 'enugu', 'EN', 1),
(1, 'Gombe', 'gombe', 'GO', 1),
(1, 'Imo', 'imo', 'IM', 1),
(1, 'Jigawa', 'jigawa', 'JI', 1),
(1, 'Kaduna', 'kaduna', 'KD', 1),
(1, 'Kano', 'kano', 'KN', 1),
(1, 'Katsina', 'katsina', 'KT', 1),
(1, 'Kebbi', 'kebbi', 'KB', 1),
(1, 'Kogi', 'kogi', 'KO', 1),
(1, 'Kwara', 'kwara', 'KW', 1),
(1, 'Lagos', 'lagos', 'LA', 1),
(1, 'Nasarawa', 'nasarawa', 'NA', 1),
(1, 'Niger', 'niger', 'NI', 1),
(1, 'Ogun', 'ogun', 'OG', 1),
(1, 'Ondo', 'ondo', 'ON', 1),
(1, 'Osun', 'osun', 'OS', 1),
(1, 'Oyo', 'oyo', 'OY', 1),
(1, 'Plateau', 'plateau', 'PL', 1),
(1, 'Rivers', 'rivers', 'RI', 1),
(1, 'Sokoto', 'sokoto', 'SO', 1),
(1, 'Taraba', 'taraba', 'TA', 1),
(1, 'Yobe', 'yobe', 'YO', 1),
(1, 'Zamfara', 'zamfara', 'ZA', 1),
(1, 'Federal Capital Territory', 'abuja-fct', 'FC', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), code = VALUES(code);

-- 3. Populate Additional Key Commercial & Residential Cities Across Nigeria
INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Uyo', 'uyo', 1 FROM states s WHERE s.slug = 'akwa-ibom' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'uyo');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Calabar', 'calabar', 1 FROM states s WHERE s.slug = 'cross-river' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'calabar');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Warri', 'warri', 1 FROM states s WHERE s.slug = 'delta' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'warri');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Asaba', 'asaba', 1 FROM states s WHERE s.slug = 'delta' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'asaba');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Benin City', 'benin-city', 1 FROM states s WHERE s.slug = 'edo' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'benin-city');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Enugu Metro', 'enugu-metro', 1 FROM states s WHERE s.slug = 'enugu' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'enugu-metro');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Kaduna Central', 'kaduna-central', 1 FROM states s WHERE s.slug = 'kaduna' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'kaduna-central');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Kano Municipal', 'kano-municipal', 1 FROM states s WHERE s.slug = 'kano' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'kano-municipal');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Ilorin', 'ilorin', 1 FROM states s WHERE s.slug = 'kwara' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'ilorin');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Abeokuta', 'abeokuta', 1 FROM states s WHERE s.slug = 'ogun' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'abeokuta');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Ibadan Central', 'ibadan-central', 1 FROM states s WHERE s.slug = 'oyo' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'ibadan-central');

-- 4. International Cities (Ghana, UAE, UK)
INSERT INTO states (country_id, name, slug, code, is_active) VALUES
(2, 'Greater Accra', 'greater-accra', 'AA', 1),
(4, 'Dubai Emirate', 'dubai-emirate', 'DXB', 1),
(5, 'Greater London', 'greater-london', 'LDN', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Accra Metropolitan', 'accra', 1 FROM states s WHERE s.slug = 'greater-accra' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'accra');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Downtown Dubai', 'downtown-dubai', 1 FROM states s WHERE s.slug = 'dubai-emirate' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'downtown-dubai');

INSERT INTO cities (state_id, name, slug, is_active)
SELECT s.id, 'Central London', 'central-london', 1 FROM states s WHERE s.slug = 'greater-london' AND NOT EXISTS (SELECT 1 FROM cities WHERE slug = 'central-london');

-- 5. Expand System Settings for Measurement Units, Regional Settings, & Taxes
INSERT INTO system_settings (`key`, `value`, `group`, description) VALUES
('measurement_unit', 'SQM', 'localization', 'Primary measurement unit (SQM, SQFT, Acres, Hectares, Plots)'),
('secondary_measurement_unit', 'Plots', 'localization', 'Secondary regional land measurement unit'),
('supported_currencies', 'NGN,USD,GBP,EUR,AED,GHS', 'localization', 'Whitelisted currencies for international listings'),
('statutory_vat_rate', '7.5', 'finance', 'Statutory Value Added Tax percentage'),
('withholding_tax_rate', '10.0', 'finance', 'Statutory withholding tax percentage on rental leases'),
('date_display_format', 'M d, Y', 'localization', 'Standard user-facing date format'),
('default_country_id', '1', 'localization', 'Default operational country (Nigeria = 1)'),
('off_plan_investments_active', '1', 'features', 'Enable New Developments & Off-Plan Investment Showcase'),
('property_management_active', '1', 'features', 'Enable Tenancy & Property Management Module')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- 6. Add New Developments and Off-Plan Property Types & Properties
SET @devCatId = (SELECT id FROM property_categories WHERE slug = 'new-developments');
SET @resCatId = (SELECT id FROM property_categories WHERE slug = 'residential');
SET @comCatId = (SELECT id FROM property_categories WHERE slug = 'commercial');
SET @landCatId = (SELECT id FROM property_categories WHERE slug = 'land');

SET @devTypeId = (SELECT id FROM property_types WHERE slug = 'luxury-penthouse');
SET @officeTypeId = (SELECT id FROM property_types WHERE slug = 'office-building');
SET @landTypeId = (SELECT id FROM property_types WHERE slug = 'commercial-land');

SET @agentId = (SELECT id FROM users WHERE email = 'chioma@prestigeprime.ng');
SET @agencyId = (SELECT id FROM agencies WHERE slug = 'prestige-prime-luxury-realty');
SET @lagosId = (SELECT id FROM states WHERE slug = 'lagos');
SET @etiOsaId = (SELECT id FROM cities WHERE slug = 'eti-osa');
SET @ekoAtlanticId = (SELECT id FROM neighbourhoods WHERE slug = 'eko-atlantic');
SET @ikoyiId = (SELECT id FROM neighbourhoods WHERE slug = 'ikoyi');

-- Insert Off-Plan Signature Development
INSERT INTO properties (
    uuid, reference_code, user_id, agency_id, category_id, property_type_id, title, slug, description,
    transaction_type, price, price_prefix, price_suffix, currency, country_id, state_id, city_id,
    neighbourhood_id, address, latitude, longitude, bedrooms, bathrooms, toilets, parking_spaces,
    building_size_sqm, land_size_sqm, year_built, furnishing_status, property_condition, title_document,
    status, verification_status, is_featured, is_luxury, view_count, favourite_count, created_at
) VALUES (
    'd0000001-0000-0000-0000-000000000001', 'DEV-EKO-01', @agentId, @agencyId, @devCatId, @devTypeId,
    'The Grand Horizon Waterfront Towers (Phase 1 Off-Plan Investment)',
    'grand-horizon-waterfront-towers-eko-atlantic',
    'An elite 32-storey mixed-use coastal tower located in the prestigious Marina District of Eko Atlantic City. Offering pre-construction pricing for institutional investors and high-net-worth buyers.\n\nInvestment Highlights:\n- Projected 28% capital appreciation between foundation and structural completion\n- Flexible 30-month milestone payment schedule (20% initial commitment)\n- Dedicated private marina berths, Olympic infinity pool, and concierge\n- Built to LEED Gold environmental and flood-resilience engineering standards.',
    'sale', 850000000.00, 'Starting from', '(Pre-Launch Price)', 'NGN', 1, @lagosId, @etiOsaId,
    @ekoAtlanticId, 'Marina Promenade, Eko Atlantic City, Lagos', 6.406100, 3.407900, 3, 4, 4, 3,
    380.00, 0.00, 2026, 'semi_furnished', 'under_construction', 'Federal Certificate of Occupancy',
    'published', 'verified', 1, 1, 310, 64, NOW()
) ON DUPLICATE KEY UPDATE title = VALUES(title);

SET @devPropId = (SELECT id FROM properties WHERE slug = 'grand-horizon-waterfront-towers-eko-atlantic');
INSERT INTO property_images (property_id, image_url, thumbnail_url, caption, is_cover, display_order)
VALUES (@devPropId, 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1600&q=80', 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=600&q=80', 'Tower Rendering', 1, 1)
ON DUPLICATE KEY UPDATE caption = VALUES(caption);

-- Insert Property Management Demo Lease & Maintenance Ticket
SET @landlordId = (SELECT id FROM users WHERE email = 'emeka.nnamani@example.com');
SET @tenantId = (SELECT id FROM users WHERE email = 'zainab.aliyu@example.com');
SET @leasedPropId = (SELECT id FROM properties WHERE slug = 'executive-4-bedroom-serviced-terrace-duplex-lekki-phase-1');

INSERT INTO leases (
    property_id, landlord_id, tenant_id, lease_start_date, lease_end_date,
    rent_amount, payment_frequency, deposit_amount, status, created_at
) VALUES (
    @leasedPropId, @landlordId, @tenantId, '2026-01-01', '2026-12-31',
    18000000.00, 'annually', 1800000.00, 'active', NOW()
) ON DUPLICATE KEY UPDATE rent_amount = VALUES(rent_amount);

SET @leaseId = (SELECT id FROM leases WHERE property_id = @leasedPropId LIMIT 1);

INSERT INTO rent_payments (lease_id, amount, due_date, paid_date, payment_method, status, receipt_reference, created_at)
VALUES (@leaseId, 18000000.00, '2026-01-01', '2026-01-03', 'bank_transfer', 'paid', 'RCPT-2026-LEK01', NOW())
ON DUPLICATE KEY UPDATE status = VALUES(status);

INSERT INTO maintenance_requests (
    property_id, tenant_id, landlord_id, title, description, priority, status, cost, created_at
) VALUES (
    @leasedPropId, @tenantId, @landlordId,
    'Central Water Filtration Plant Membrane Servicing',
    'Routine semi-annual replacement of industrial water filtration sediment cartridges and UV sterilizer lamps.',
    'medium', 'resolved', 145000.00, NOW()
) ON DUPLICATE KEY UPDATE title = VALUES(title);
