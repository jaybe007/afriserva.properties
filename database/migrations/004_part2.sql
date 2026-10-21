-- Part 2 of Migration 004: Insert Off-Plan Properties, Leases, and Property Management records

SET @devCatId = (SELECT id FROM property_categories WHERE slug = 'new-developments' LIMIT 1);
SET @devTypeId = (SELECT id FROM property_types WHERE slug = 'luxury-penthouse' LIMIT 1);
SET @agentId = (SELECT id FROM users WHERE email = 'chioma@prestigeprime.ng' LIMIT 1);
SET @agencyId = (SELECT id FROM agencies WHERE slug = 'prestige-prime-luxury-realty' LIMIT 1);
SET @lagosId = (SELECT id FROM states WHERE slug = 'lagos' LIMIT 1);
SET @etiOsaId = (SELECT id FROM cities WHERE slug = 'eti-osa' LIMIT 1);
SET @ekoAtlanticId = (SELECT id FROM neighbourhoods WHERE slug = 'eko-atlantic' LIMIT 1);

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

SET @devPropId = (SELECT id FROM properties WHERE slug = 'grand-horizon-waterfront-towers-eko-atlantic' LIMIT 1);

INSERT INTO property_images (property_id, image_url, thumbnail_url, caption, is_cover, display_order)
VALUES (@devPropId, 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1600&q=80', 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=600&q=80', 'Tower Rendering', 1, 1)
ON DUPLICATE KEY UPDATE caption = VALUES(caption);

-- Insert Property Management Demo Lease & Maintenance Ticket
SET @landlordId = (SELECT id FROM users WHERE email = 'emeka.nnamani@example.com' LIMIT 1);
SET @tenantId = (SELECT id FROM users WHERE email = 'zainab.aliyu@example.com' LIMIT 1);
SET @leasedPropId = (SELECT id FROM properties WHERE slug = 'executive-4-bedroom-serviced-terrace-duplex-lekki-phase-1' LIMIT 1);

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
