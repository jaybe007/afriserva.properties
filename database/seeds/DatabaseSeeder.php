<?php

namespace Database\Seeds;

use PDO;
use Exception;

class DatabaseSeeder
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function run(): void
    {
        echo "Seeding database...\n";
        $this->seedRolesAndPermissions();
        $this->seedCountriesAndStates();
        $this->seedCategoriesAndTypes();
        $this->seedAmenities();
        $this->seedSubscriptionPlans();
        $this->seedSystemSettings();
        $this->seedUsersAndAgencies();
        $this->seedProperties();
        echo "Database seeded successfully!\n";
    }

    private function seedRolesAndPermissions(): void
    {
        $roles = [
            ['name' => 'super_admin', 'label' => 'Super Administrator', 'description' => 'Full administrative access to entire platform'],
            ['name' => 'moderator', 'label' => 'Compliance Moderator', 'description' => 'Moderates listings, verifications and complaints'],
            ['name' => 'finance_admin', 'label' => 'Finance Administrator', 'description' => 'Manages subscriptions, payments and invoices'],
            ['name' => 'agency_admin', 'label' => 'Agency Principal', 'description' => 'Manages corporate agency profile and team agents'],
            ['name' => 'agent', 'label' => 'Licensed Real Estate Agent', 'description' => 'Lists properties, receives leads and schedules viewings'],
            ['name' => 'developer', 'label' => 'Property Developer', 'description' => 'Develops and showcases off-plan estates and phases'],
            ['name' => 'landlord', 'label' => 'Property Owner / Landlord', 'description' => 'Direct owner listing properties and managing leases'],
            ['name' => 'client', 'label' => 'Property Buyer / Investor', 'description' => 'Searches, saves favorites, books tours and sends enquiries'],
            ['name' => 'tenant', 'label' => 'Tenant', 'description' => 'Leases residential or commercial properties and submits maintenance requests'],
            ['name' => 'seller', 'label' => 'Property Seller', 'description' => 'Direct owner listing properties and land plots for outright sale'],
            ['name' => 'property_manager', 'label' => 'Property Manager', 'description' => 'Oversees facility management, rent collection and tenancy operations'],
            ['name' => 'support', 'label' => 'Customer Support', 'description' => 'Handles buyer/tenant enquiries, reviews disputes and assists users']
        ];

        $stmt = $this->pdo->prepare("INSERT IGNORE INTO roles (name, label, description) VALUES (?, ?, ?)");
        foreach ($roles as $role) {
            $stmt->execute([$role['name'], $role['label'], $role['description']]);
        }
    }

    private function seedCountriesAndStates(): void
    {
        $this->pdo->exec("INSERT IGNORE INTO countries (id, name, iso_code, phone_code, currency_code, currency_symbol, is_active) 
            VALUES (1, 'Nigeria', 'NGA', '+234', 'NGN', '₦', 1)");

        $states = [
            ['name' => 'Lagos', 'slug' => 'lagos', 'code' => 'LA'],
            ['name' => 'Abuja FCT', 'slug' => 'abuja-fct', 'code' => 'FC'],
            ['name' => 'Rivers', 'slug' => 'rivers', 'code' => 'RI'],
            ['name' => 'Oyo', 'slug' => 'oyo', 'code' => 'OY'],
            ['name' => 'Ogun', 'slug' => 'ogun', 'code' => 'OG'],
            ['name' => 'Enugu', 'slug' => 'enugu', 'code' => 'EN'],
            ['name' => 'Delta', 'slug' => 'delta', 'code' => 'DE'],
            ['name' => 'Edo', 'slug' => 'edo', 'code' => 'ED'],
            ['name' => 'Anambra', 'slug' => 'anambra', 'code' => 'AN'],
            ['name' => 'Kano', 'slug' => 'kano', 'code' => 'KN']
        ];

        $stmt = $this->pdo->prepare("INSERT IGNORE INTO states (country_id, name, slug, code, is_active) VALUES (1, ?, ?, ?, 1)");
        foreach ($states as $state) {
            $stmt->execute([$state['name'], $state['slug'], $state['code']]);
        }

        // Cities
        $lagosId = $this->pdo->query("SELECT id FROM states WHERE slug = 'lagos'")->fetchColumn();
        $abujaId = $this->pdo->query("SELECT id FROM states WHERE slug = 'abuja-fct'")->fetchColumn();
        $riversId = $this->pdo->query("SELECT id FROM states WHERE slug = 'rivers'")->fetchColumn();

        $cities = [
            ['state_id' => $lagosId, 'name' => 'Lagos Island', 'slug' => 'lagos-island'],
            ['state_id' => $lagosId, 'name' => 'Eti-Osa', 'slug' => 'eti-osa'],
            ['state_id' => $lagosId, 'name' => 'Ikeja', 'slug' => 'ikeja'],
            ['state_id' => $lagosId, 'name' => 'Ibeju-Lekki', 'slug' => 'ibeju-lekki'],
            ['state_id' => $abujaId, 'name' => 'Abuja Municipal', 'slug' => 'abuja-municipal'],
            ['state_id' => $riversId, 'name' => 'Port Harcourt', 'slug' => 'port-harcourt']
        ];

        $stmtCity = $this->pdo->prepare("INSERT IGNORE INTO cities (state_id, name, slug, is_active) VALUES (?, ?, ?, 1)");
        foreach ($cities as $c) {
            $stmtCity->execute([$c['state_id'], $c['name'], $c['slug']]);
        }

        // Neighbourhoods
        $etiOsaId = $this->pdo->query("SELECT id FROM cities WHERE slug = 'eti-osa'")->fetchColumn();
        $lagosIslandId = $this->pdo->query("SELECT id FROM cities WHERE slug = 'lagos-island'")->fetchColumn();
        $ikejaId = $this->pdo->query("SELECT id FROM cities WHERE slug = 'ikeja'")->fetchColumn();
        $abujaCityId = $this->pdo->query("SELECT id FROM cities WHERE slug = 'abuja-municipal'")->fetchColumn();

        $neighbourhoods = [
            ['city_id' => $etiOsaId, 'name' => 'Ikoyi', 'slug' => 'ikoyi', 'lat' => 6.4504, 'lng' => 3.4358],
            ['city_id' => $etiOsaId, 'name' => 'Banana Island', 'slug' => 'banana-island', 'lat' => 6.4632, 'lng' => 3.4442],
            ['city_id' => $etiOsaId, 'name' => 'Victoria Island', 'slug' => 'victoria-island', 'lat' => 6.4281, 'lng' => 3.4219],
            ['city_id' => $etiOsaId, 'name' => 'Lekki Phase 1', 'slug' => 'lekki-phase-1', 'lat' => 6.4474, 'lng' => 3.4735],
            ['city_id' => $ikejaId, 'name' => 'Ikeja GRA', 'slug' => 'ikeja-gra', 'lat' => 6.5898, 'lng' => 3.3570],
            ['city_id' => $abujaCityId, 'name' => 'Maitama', 'slug' => 'maitama', 'lat' => 9.0882, 'lng' => 7.4984],
            ['city_id' => $abujaCityId, 'name' => 'Asokoro', 'slug' => 'asokoro', 'lat' => 9.0436, 'lng' => 7.5244],
            ['city_id' => $abujaCityId, 'name' => 'Guzape', 'slug' => 'guzape', 'lat' => 9.0192, 'lng' => 7.5113],
            ['city_id' => $abujaCityId, 'name' => 'Wuse II', 'slug' => 'wuse-2', 'lat' => 9.0778, 'lng' => 7.4719],
            ['city_id' => $lagosIslandId, 'name' => 'Eko Atlantic City', 'slug' => 'eko-atlantic', 'lat' => 6.4061, 'lng' => 3.4079]
        ];

        $stmtNeigh = $this->pdo->prepare("INSERT IGNORE INTO neighbourhoods (city_id, name, slug, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
        foreach ($neighbourhoods as $n) {
            $stmtNeigh->execute([$n['city_id'], $n['name'], $n['slug'], $n['lat'], $n['lng']]);
        }
    }

    private function seedCategoriesAndTypes(): void
    {
        $categories = [
            ['name' => 'Residential', 'slug' => 'residential', 'icon' => 'home', 'description' => 'Luxury homes, penthouses, apartments and duplexes'],
            ['name' => 'Commercial', 'slug' => 'commercial', 'icon' => 'building', 'description' => 'Prime office towers, retail storefronts and corporate spaces'],
            ['name' => 'Land & Plots', 'slug' => 'land', 'icon' => 'map-pin', 'description' => 'Verified residential, commercial and mixed-use plots with clear titles'],
            ['name' => 'Short-Let Hospitality', 'slug' => 'short-let', 'icon' => 'key', 'description' => 'Furnished luxury serviced suites for executive stays'],
            ['name' => 'New Developments', 'slug' => 'new-developments', 'icon' => 'layers', 'description' => 'Off-plan developments, phase handovers and pre-construction offers']
        ];

        $stmtCat = $this->pdo->prepare("INSERT IGNORE INTO property_categories (name, slug, icon, description, display_order) VALUES (?, ?, ?, ?, ?)");
        $order = 1;
        foreach ($categories as $cat) {
            $stmtCat->execute([$cat['name'], $cat['slug'], $cat['icon'], $cat['description'], $order++]);
        }

        $resId = $this->pdo->query("SELECT id FROM property_categories WHERE slug = 'residential'")->fetchColumn();
        $comId = $this->pdo->query("SELECT id FROM property_categories WHERE slug = 'commercial'")->fetchColumn();
        $landId = $this->pdo->query("SELECT id FROM property_categories WHERE slug = 'land'")->fetchColumn();
        $shortLetId = $this->pdo->query("SELECT id FROM property_categories WHERE slug = 'short-let'")->fetchColumn();

        $types = [
            ['category_id' => $resId, 'name' => 'Detached Duplex', 'slug' => 'detached-duplex'],
            ['category_id' => $resId, 'name' => 'Semi-Detached Duplex', 'slug' => 'semi-detached-duplex'],
            ['category_id' => $resId, 'name' => 'Luxury Penthouse', 'slug' => 'luxury-penthouse'],
            ['category_id' => $resId, 'name' => 'Terraced House', 'slug' => 'terraced-house'],
            ['category_id' => $resId, 'name' => 'Serviced Apartment', 'slug' => 'serviced-apartment'],
            ['category_id' => $comId, 'name' => 'Grade-A Office Building', 'slug' => 'office-building'],
            ['category_id' => $comId, 'name' => 'Retail Mall / Showroom', 'slug' => 'retail-showroom'],
            ['category_id' => $landId, 'name' => 'Residential Land Plot', 'slug' => 'residential-land'],
            ['category_id' => $landId, 'name' => 'Commercial Land Plot', 'slug' => 'commercial-land'],
            ['category_id' => $shortLetId, 'name' => 'Luxury Serviced Studio', 'slug' => 'short-let-studio'],
            ['category_id' => $shortLetId, 'name' => 'Executive 3-Bed Shortlet', 'slug' => 'executive-shortlet']
        ];

        $stmtType = $this->pdo->prepare("INSERT IGNORE INTO property_types (category_id, name, slug) VALUES (?, ?, ?)");
        foreach ($types as $t) {
            $stmtType->execute([$t['category_id'], $t['name'], $t['slug']]);
        }
    }

    private function seedAmenities(): void
    {
        $amenities = [
            ['name' => '24/7 Security & CCTV', 'icon' => 'shield', 'group' => 'security'],
            ['name' => 'Access Control & Perimeter Electric Fence', 'icon' => 'lock', 'group' => 'security'],
            ['name' => 'Dedicated Heavy-duty Generator (24/7 Power)', 'icon' => 'zap', 'group' => 'utilities'],
            ['name' => 'Industrial Water Treatment Plant', 'icon' => 'droplet', 'group' => 'utilities'],
            ['name' => 'Infinity Swimming Pool', 'icon' => 'life-buoy', 'group' => 'luxury'],
            ['name' => 'State-of-the-Art Private Cinema', 'icon' => 'film', 'group' => 'luxury'],
            ['name' => 'Smart Home Automation & Alexa Integration', 'icon' => 'cpu', 'group' => 'luxury'],
            ['name' => 'Fully Equipped Gym & Wellness Spa', 'icon' => 'activity', 'group' => 'luxury'],
            ['name' => 'High-Speed Fiber Optic Internet', 'icon' => 'wifi', 'group' => 'convenience'],
            ['name' => 'High-Speed Panoramic Elevators', 'icon' => 'arrow-up-circle', 'group' => 'convenience'],
            ['name' => 'Covered Multi-Car Garage (4+ cars)', 'icon' => 'truck', 'group' => 'convenience'],
            ['name' => 'Boys’ Quarters (BQ) / Maid Suite', 'icon' => 'user-check', 'group' => 'convenience'],
            ['name' => 'Rooftop Lounge & Ocean View Deck', 'icon' => 'sunset', 'group' => 'outdoor'],
            ['name' => 'Landscaped Garden & Children Play Zone', 'icon' => 'sun', 'group' => 'outdoor']
        ];

        $stmt = $this->pdo->prepare("INSERT IGNORE INTO amenities (name, icon, group_name) VALUES (?, ?, ?)");
        foreach ($amenities as $am) {
            $stmt->execute([$am['name'], $am['icon'], $am['group']]);
        }
    }

    private function seedSubscriptionPlans(): void
    {
        $plans = [
            [
                'name' => 'Starter Agent',
                'slug' => 'starter-agent',
                'description' => 'Ideal for independent agents beginning their verified portfolio.',
                'price' => 0.00,
                'billing_interval' => 'monthly',
                'listing_allowance' => 3,
                'featured_allowance' => 0,
                'team_members_limit' => 1,
                'display_order' => 1
            ],
            [
                'name' => 'Professional Agent',
                'slug' => 'professional-agent',
                'description' => 'Comprehensive toolkit for high-performing real estate advisors.',
                'price' => 35000.00,
                'billing_interval' => 'monthly',
                'listing_allowance' => 25,
                'featured_allowance' => 3,
                'team_members_limit' => 2,
                'display_order' => 2
            ],
            [
                'name' => 'Corporate Agency',
                'slug' => 'corporate-agency',
                'description' => 'Full enterprise features for licensed brokerages and teams.',
                'price' => 120000.00,
                'billing_interval' => 'monthly',
                'listing_allowance' => 100,
                'featured_allowance' => 12,
                'team_members_limit' => 10,
                'display_order' => 3
            ],
            [
                'name' => 'Developer Signature',
                'slug' => 'developer-signature',
                'description' => 'Tailored for real estate developers launching off-plan estates and phases.',
                'price' => 350000.00,
                'billing_interval' => 'quarterly',
                'listing_allowance' => 250,
                'featured_allowance' => 30,
                'team_members_limit' => 25,
                'display_order' => 4
            ]
        ];

        $stmt = $this->pdo->prepare("INSERT IGNORE INTO subscription_plans 
            (name, slug, description, price, currency, billing_interval, listing_allowance, featured_allowance, team_members_limit, display_order)
            VALUES (?, ?, ?, ?, 'NGN', ?, ?, ?, ?, ?)");
        foreach ($plans as $p) {
            $stmt->execute([
                $p['name'], $p['slug'], $p['description'], $p['price'],
                $p['billing_interval'], $p['listing_allowance'], $p['featured_allowance'],
                $p['team_members_limit'], $p['display_order']
            ]);
        }
    }

    private function seedSystemSettings(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'Afriserva Properties', 'group' => 'general', 'description' => 'Platform display name'],
            ['key' => 'app_tagline', 'value' => 'Nigeria’s Premier Luxury & Institutional Real Estate Marketplace', 'group' => 'general', 'description' => 'Platform tagline'],
            ['key' => 'contact_email', 'value' => 'support@properties.afriserva.com', 'group' => 'contact', 'description' => 'Support email address'],
            ['key' => 'contact_phone', 'value' => '+234 1 800 2374', 'group' => 'contact', 'description' => 'Primary hotline phone'],
            ['key' => 'office_address', 'value' => 'Level 14, Heritage Towers, Victoria Island, Lagos, Nigeria', 'group' => 'contact', 'description' => 'Corporate office headquarters'],
            ['key' => 'default_currency', 'value' => 'NGN', 'group' => 'localization', 'description' => 'Default system currency ISO'],
            ['key' => 'currency_symbol', 'value' => '₦', 'group' => 'localization', 'description' => 'Currency symbol'],
            ['key' => 'paystack_active', 'value' => '1', 'group' => 'payment', 'description' => 'Enable Paystack gateway integration'],
            ['key' => 'flutterwave_active', 'value' => '1', 'group' => 'payment', 'description' => 'Enable Flutterwave gateway integration'],
            ['key' => 'listing_auto_approve', 'value' => '0', 'group' => 'moderation', 'description' => 'Require administrative review before listing publish']
        ];

        $stmt = $this->pdo->prepare("INSERT INTO system_settings (`key`, `value`, `group`, description) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        foreach ($settings as $s) {
            $stmt->execute([$s['key'], $s['value'], $s['group'], $s['description']]);
        }
    }

    private function seedUsersAndAgencies(): void
    {
        // 1. Super Admin
        $this->createUserWithRole(
            'a0000001-0000-0000-0000-000000000001',
            'Alexander Wright',
            'alexander.wright@afriserva.com',
            '+2348030000001',
            password_hash('Afriserva#2026!Xw9$kL7m', PASSWORD_ARGON2ID),
            'super_admin',
            'Executive Director of Technology & Infrastructure at Afriserva Real Estate Group.'
        );

        // 2. Compliance Moderator
        $this->createUserWithRole(
            'a0000001-0000-0000-0000-000000000002',
            'Olufunke Adeyemi',
            'olufunke.adeyemi@afriserva.com',
            '+2348030000002',
            password_hash('Compliance*2026@P9q#vR4z', PASSWORD_ARGON2ID),
            'moderator',
            'Legal & Property Documentation Verification Specialist.'
        );

        // 3. Top Agency Principal
        $agencyAdminId = $this->createUserWithRole(
            'a0000001-0000-0000-0000-000000000003',
            'Chief Babatunde Balogun',
            'babatunde.balogun@prestigeprime.ng',
            '+2348021112233',
            password_hash('PrestigePrime#99!B7x$mQ2', PASSWORD_ARGON2ID),
            'agency_admin',
            'Managing Partner at Prestige Prime Luxury Realty, specialized in high-net-worth acquisitions across Ikoyi and Banana Island.'
        );

        // Create Agency
        $stmtAgency = $this->pdo->prepare("INSERT INTO agencies 
            (name, slug, email, phone, cac_number, address, city, state, description, is_verified)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE name = VALUES(name)");
        $stmtAgency->execute([
            'Prestige Prime Luxury Realty',
            'prestige-prime-luxury-realty',
            'info@prestigeprime.ng',
            '+2348021112233',
            'RC-1498302',
            'Plot 12A, Kingsway Road, Ikoyi, Lagos',
            'Ikoyi',
            'Lagos',
            'Prestige Prime Luxury Realty is West Africa’s premier luxury real estate advisory with over $150M in closed residential and commercial deals.'
        ]);
        $agencyId = $this->pdo->lastInsertId() ?: $this->pdo->query("SELECT id FROM agencies WHERE slug = 'prestige-prime-luxury-realty'")->fetchColumn();

        // Associate agency member
        $this->pdo->exec("INSERT IGNORE INTO agency_members (agency_id, user_id, role_in_agency, status) 
            VALUES ($agencyId, $agencyAdminId, 'principal', 'active')");

        // 4. Elite Real Estate Agents
        $agent1Id = $this->createUserWithRole(
            'a0000001-0000-0000-0000-000000000004',
            'Chioma Okonkwo',
            'chioma.okonkwo@prestigeprime.ng',
            '+2348035557788',
            password_hash('LuxuryRealty*88@C4w#nK5', PASSWORD_ARGON2ID),
            'agent',
            'Senior Luxury Property Consultant specializing in waterfront mansions, penthouses, and private estates in Banana Island and Eko Atlantic.'
        );
        $this->pdo->exec("INSERT INTO agent_profiles (user_id, agency_id, license_no, specialization, experience_years, whatsapp_number, rating, total_reviews, is_verified, bio)
            VALUES ($agent1Id, $agencyId, 'NIESV-2018-941', 'Luxury Waterfront & Penthouses', 9, '+2348035557788', 4.95, 34, 1, 'Top producing agent for 4 consecutive years.')
            ON DUPLICATE KEY UPDATE rating = VALUES(rating)");
        $this->pdo->exec("INSERT IGNORE INTO agency_members (agency_id, user_id, role_in_agency, status) VALUES ($agencyId, $agent1Id, 'associate_agent', 'active')");

        $agent2Id = $this->createUserWithRole(
            'a0000001-0000-0000-0000-000000000005',
            'Musa Danjuma',
            'musa.agent@afriserva.com',
            '+2348094443322',
            password_hash('AbujaEnclave#77!M3v$jP8', PASSWORD_ARGON2ID),
            'agent',
            'Abuja Diplomatic Zone Real Estate Advisor representing institutional investors, embassies, and private individuals in Maitama and Asokoro.'
        );
        $this->pdo->exec("INSERT INTO agent_profiles (user_id, agency_id, license_no, specialization, experience_years, whatsapp_number, rating, total_reviews, is_verified, bio)
            VALUES ($agent2Id, NULL, 'ESVARBON-2015-1102', 'Diplomatic Enclave & Commercial Land', 12, '+2348094443322', 4.90, 48, 1, 'Trusted advisor to diplomatic missions and corporate leaders in Abuja.')
            ON DUPLICATE KEY UPDATE rating = VALUES(rating)");

        // 5. Landlord / Property Owner
        $this->createUserWithRole(
            'a0000001-0000-0000-0000-000000000006',
            'Dr. Emeka Nnamani',
            'dr.emeka.nnamani@afriserva.com',
            '+2348057771122',
            password_hash('LekkiPortfolio*55@E2x#bV9', PASSWORD_ARGON2ID),
            'landlord',
            'Private real estate investor with residential portfolio in Lekki Phase 1 and Victoria Island.'
        );

        // 6. Registered Buyer / Client
        $this->createUserWithRole(
            'a0000001-0000-0000-0000-000000000007',
            'Zainab Aliyu',
            'zainab.aliyu@afriserva.com',
            '+2348068889900',
            password_hash('InvestorClub#33!Z8q$kM4', PASSWORD_ARGON2ID),
            'client',
            'Corporate executive actively acquiring off-plan and luxury residential properties in Lagos and Abuja.'
        );
    }

    private function createUserWithRole(string $uuid, string $name, string $email, string $phone, string $passwordHash, string $roleName, string $bio): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (uuid, full_name, email, phone, password_hash, status, bio, email_verified_at)
            VALUES (?, ?, ?, ?, ?, 'active', ?, NOW())
            ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), status = VALUES(status)");
        $stmt->execute([$uuid, $name, $email, $phone, $passwordHash, $bio]);

        $userId = $this->pdo->lastInsertId() ?: $this->pdo->query("SELECT id FROM users WHERE email = '$email'")->fetchColumn();

        $roleId = $this->pdo->query("SELECT id FROM roles WHERE name = '$roleName'")->fetchColumn();
        if ($roleId) {
            $this->pdo->exec("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES ($userId, $roleId)");
        }

        return (int)$userId;
    }

    private function seedProperties(): void
    {
        $agent1Id = $this->pdo->query("SELECT id FROM users WHERE email = 'chioma.okonkwo@prestigeprime.ng'")->fetchColumn();
        $agent2Id = $this->pdo->query("SELECT id FROM users WHERE email = 'musa.agent@afriserva.com'")->fetchColumn();
        $agencyId = $this->pdo->query("SELECT id FROM agencies WHERE slug = 'prestige-prime-luxury-realty'")->fetchColumn();

        $lagosId = $this->pdo->query("SELECT id FROM states WHERE slug = 'lagos'")->fetchColumn();
        $abujaId = $this->pdo->query("SELECT id FROM states WHERE slug = 'abuja-fct'")->fetchColumn();

        $etiOsaId = $this->pdo->query("SELECT id FROM cities WHERE slug = 'eti-osa'")->fetchColumn();
        $ikejaId = $this->pdo->query("SELECT id FROM cities WHERE slug = 'ikeja'")->fetchColumn();
        $abujaCityId = $this->pdo->query("SELECT id FROM cities WHERE slug = 'abuja-municipal'")->fetchColumn();
        $lagosIslandId = $this->pdo->query("SELECT id FROM cities WHERE slug = 'lagos-island'")->fetchColumn();

        $ikoyiId = $this->pdo->query("SELECT id FROM neighbourhoods WHERE slug = 'ikoyi'")->fetchColumn();
        $bananaIslandId = $this->pdo->query("SELECT id FROM neighbourhoods WHERE slug = 'banana-island'")->fetchColumn();
        $viId = $this->pdo->query("SELECT id FROM neighbourhoods WHERE slug = 'victoria-island'")->fetchColumn();
        $lekkiId = $this->pdo->query("SELECT id FROM neighbourhoods WHERE slug = 'lekki-phase-1'")->fetchColumn();
        $maitamaId = $this->pdo->query("SELECT id FROM neighbourhoods WHERE slug = 'maitama'")->fetchColumn();
        $asokoroId = $this->pdo->query("SELECT id FROM neighbourhoods WHERE slug = 'asokoro'")->fetchColumn();
        $ekoAtlanticId = $this->pdo->query("SELECT id FROM neighbourhoods WHERE slug = 'eko-atlantic'")->fetchColumn();

        $resCatId = $this->pdo->query("SELECT id FROM property_categories WHERE slug = 'residential'")->fetchColumn();
        $comCatId = $this->pdo->query("SELECT id FROM property_categories WHERE slug = 'commercial'")->fetchColumn();
        $landCatId = $this->pdo->query("SELECT id FROM property_categories WHERE slug = 'land'")->fetchColumn();
        $shortLetCatId = $this->pdo->query("SELECT id FROM property_categories WHERE slug = 'short-let'")->fetchColumn();

        $detachedTypeId = $this->pdo->query("SELECT id FROM property_types WHERE slug = 'detached-duplex'")->fetchColumn();
        $penthouseTypeId = $this->pdo->query("SELECT id FROM property_types WHERE slug = 'luxury-penthouse'")->fetchColumn();
        $terraceTypeId = $this->pdo->query("SELECT id FROM property_types WHERE slug = 'terraced-house'")->fetchColumn();
        $officeTypeId = $this->pdo->query("SELECT id FROM property_types WHERE slug = 'office-building'")->fetchColumn();
        $landTypeId = $this->pdo->query("SELECT id FROM property_types WHERE slug = 'residential-land'")->fetchColumn();
        $shortLetTypeId = $this->pdo->query("SELECT id FROM property_types WHERE slug = 'executive-shortlet'")->fetchColumn();

        $properties = [
            [
                'uuid' => 'p0000001-0000-0000-0000-000000000001',
                'reference_code' => 'AFR-LAG-1001',
                'user_id' => $agent1Id,
                'agency_id' => $agencyId,
                'category_id' => $resCatId,
                'property_type_id' => $detachedTypeId,
                'title' => 'Ultra-Luxury 6-Bedroom Waterfront Mansion with Private Jetty & Infinity Pool',
                'slug' => 'ultra-luxury-6-bedroom-waterfront-mansion-banana-island',
                'description' => "Positioned on an elite waterfront parcel in Zone J, Banana Island, this palatial 6-bedroom residence represents the pinnacle of West African luxury architectural engineering. Spanning over 1,200 square meters of covered internal living area, the home integrates panoramic floor-to-ceiling double-glazed thermal glass curtains overlooking the serene Lagos lagoon.\n\nKey features include:\n- Private reinforced deep-water yacht jetty and shoreline promenade\n- 14-seat state-of-the-art Dolby Atmos cinema with acoustic isolation\n- Custom Italian bespoke chef kitchen with Miele & Gaggenau integrated appliances\n- Olympic-grade glass mosaic heated infinity pool with sunken poolside firepit\n- Complete Lutron automated smart home lighting, multi-room sound, and climate system\n- 4-car air-conditioned display garage plus compound parking for 8 guest vehicles\n- Two distinct en-suite 2-bedroom Boys' Quarters and separate security control lodge.",
                'transaction_type' => 'sale',
                'price' => 3800000000.00,
                'price_prefix' => '',
                'price_suffix' => '',
                'state_id' => $lagosId,
                'city_id' => $etiOsaId,
                'neighbourhood_id' => $bananaIslandId,
                'address' => 'Zone J, Banana Island, Ikoyi, Lagos',
                'lat' => 6.463200,
                'lng' => 3.444200,
                'bedrooms' => 6,
                'bathrooms' => 7,
                'toilets' => 8,
                'parking_spaces' => 12,
                'building_size_sqm' => 1200.00,
                'land_size_sqm' => 1850.00,
                'year_built' => 2024,
                'furnishing_status' => 'fully_furnished',
                'property_condition' => 'brand_new',
                'title_document' => 'Federal Certificate of Occupancy',
                'is_featured' => 1,
                'is_luxury' => 1,
                'verification_status' => 'verified',
                'images' => [
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1600&q=80'
                ]
            ],
            [
                'uuid' => 'p0000001-0000-0000-0000-000000000002',
                'reference_code' => 'AFR-ABJ-2001',
                'user_id' => $agent2Id,
                'agency_id' => NULL,
                'category_id' => $resCatId,
                'property_type_id' => $detachedTypeId,
                'title' => 'Diplomatic 7-Bedroom Contemporary Villa with Helipad & Heated Pool',
                'slug' => 'diplomatic-7-bedroom-contemporary-villa-maitama-abuja',
                'description' => "Constructed to strict diplomatic security specifications on an elevated hillside within Maitama’s most exclusive residential enclave. Featuring bullet-resistant ballistic glazing, dual fortified security gates, and sweeping panoramic views of the Aso Rock vista.\n\nHighlights:\n- 7 expansive en-suite bedrooms with Italian walk-in dressing suites\n- Commercial-grade Otis hydraulic elevator servicing all 3 structural levels\n- 25-meter Olympic-length heated swimming pool and cascading waterfall\n- Autonomous power infrastructure with synchronized 100kVA soundproof Cummins generators\n- Rooftop executive cigar lounge and entertainment pavilion\n- Fully registered Federal Capital Territory Certificate of Occupancy with clean title search.",
                'transaction_type' => 'sale',
                'price' => 2400000000.00,
                'price_prefix' => '',
                'price_suffix' => '',
                'state_id' => $abujaId,
                'city_id' => $abujaCityId,
                'neighbourhood_id' => $maitamaId,
                'address' => 'Amazon Street, Maitama Diplomatic Enclave, Abuja',
                'lat' => 9.088200,
                'lng' => 7.498400,
                'bedrooms' => 7,
                'bathrooms' => 8,
                'toilets' => 9,
                'parking_spaces' => 10,
                'building_size_sqm' => 1400.00,
                'land_size_sqm' => 2200.00,
                'year_built' => 2023,
                'furnishing_status' => 'semi_furnished',
                'property_condition' => 'brand_new',
                'title_document' => 'FCT Certificate of Occupancy',
                'is_featured' => 1,
                'is_luxury' => 1,
                'verification_status' => 'verified',
                'images' => [
                    'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=1600&q=80'
                ]
            ],
            [
                'uuid' => 'p0000001-0000-0000-0000-000000000003',
                'reference_code' => 'AFR-EKO-3001',
                'user_id' => $agent1Id,
                'agency_id' => $agencyId,
                'category_id' => $resCatId,
                'property_type_id' => $penthouseTypeId,
                'title' => 'Sky Residence: 4-Bedroom Duplex Penthouse with Ocean View',
                'slug' => 'sky-residence-4-bedroom-duplex-penthouse-eko-atlantic',
                'description' => "Perched on the 28th and 29th floors of Eko Atlantic City’s signature high-rise tower, this extraordinary penthouse offers boundless 360-degree vistas of the Atlantic Ocean and the Lagos skyline.\n\nFeatures:\n- Double-height 7-meter ceilings in the grand living salon\n- Private sky terrace with Jacuzzi pool and outdoor summer kitchen\n- High-speed biometric elevator opening directly into private residence foyer\n- Triple-glazed hurricane acoustic windows\n- 24/7 dedicated city-wide independent power grid and fiber connectivity\n- Access to world-class resident amenities: tennis courts, Olympic indoor lap pool, and concierge.",
                'transaction_type' => 'sale',
                'price' => 1750000000.00,
                'price_prefix' => '',
                'price_suffix' => '',
                'state_id' => $lagosId,
                'city_id' => $lagosIslandId,
                'neighbourhood_id' => $ekoAtlanticId,
                'address' => 'Tower A, Boulevard District, Eko Atlantic City, Lagos',
                'lat' => 6.406100,
                'lng' => 3.407900,
                'bedrooms' => 4,
                'bathrooms' => 5,
                'toilets' => 5,
                'parking_spaces' => 4,
                'building_size_sqm' => 650.00,
                'land_size_sqm' => 0.00,
                'year_built' => 2024,
                'furnishing_status' => 'fully_furnished',
                'property_condition' => 'brand_new',
                'title_document' => 'Deed of Sublease & Governor Consent',
                'is_featured' => 1,
                'is_luxury' => 1,
                'verification_status' => 'verified',
                'images' => [
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1512915922686-57c11dde9b6b?auto=format&fit=crop&w=1600&q=80'
                ]
            ],
            [
                'uuid' => 'p0000001-0000-0000-0000-000000000004',
                'reference_code' => 'AFR-LEK-4001',
                'user_id' => $agent1Id,
                'agency_id' => $agencyId,
                'category_id' => $resCatId,
                'property_type_id' => $terraceTypeId,
                'title' => 'Executive 4-Bedroom Fully Serviced Terraced Duplex with BQ',
                'slug' => 'executive-4-bedroom-serviced-terrace-duplex-lekki-phase-1',
                'description' => "Located within a secure, gated private residential court off Admiralty Way, Lekki Phase 1. Designed for modern executive families seeking safety, uninterrupted electricity, and close proximity to top international schools and lifestyle centers.\n\nProperty Details:\n- 4 spacious en-suite bedrooms with fitted wardrobes\n- Contemporary open-plan kitchen with granite counters and heat extractor\n- 24-hour centralized generator service and treated potable water\n- Dedicated 2-car paved parking plus visitor parking bays\n- En-suite domestic staff quarter (BQ).",
                'transaction_type' => 'rent',
                'price' => 18000000.00,
                'price_prefix' => '',
                'price_suffix' => '/ annum',
                'state_id' => $lagosId,
                'city_id' => $etiOsaId,
                'neighbourhood_id' => $lekkiId,
                'address' => 'Off Admiralty Way, Lekki Phase 1, Lagos',
                'lat' => 6.447400,
                'lng' => 3.473500,
                'bedrooms' => 4,
                'bathrooms' => 4,
                'toilets' => 5,
                'parking_spaces' => 2,
                'building_size_sqm' => 320.00,
                'land_size_sqm' => 400.00,
                'year_built' => 2022,
                'furnishing_status' => 'semi_furnished',
                'property_condition' => 'excellent',
                'title_document' => 'Governor Consent',
                'is_featured' => 0,
                'is_luxury' => 0,
                'verification_status' => 'verified',
                'images' => [
                    'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=1600&q=80'
                ]
            ],
            [
                'uuid' => 'p0000001-0000-0000-0000-000000000005',
                'reference_code' => 'AFR-SL-5001',
                'user_id' => $agent1Id,
                'agency_id' => $agencyId,
                'category_id' => $shortLetCatId,
                'property_type_id' => $shortLetTypeId,
                'title' => 'The Onyx Suite: 3-Bedroom Executive Waterfront Shortlet',
                'slug' => 'the-onyx-suite-3-bedroom-executive-waterfront-shortlet-ikoyi',
                'description' => "Designed for international business travelers, diplomats, and luxury vacationers. The Onyx Suite offers 5-star hotel comfort with the privacy of a grand waterfront apartment in Old Ikoyi.\n\nInclusions:\n- Daily professional housekeeping and 24/7 dedicated concierge\n- High-speed 300 Mbps Starlink internet connection\n- Smart Netflix/DSTV 75-inch OLED displays in all bedrooms and lounge\n- Fully equipped gourmet kitchen with espresso machine\n- Lagoon-facing private balcony with sunset dining setting.",
                'transaction_type' => 'short_let',
                'price' => 350000.00,
                'price_prefix' => '',
                'price_suffix' => '/ night',
                'state_id' => $lagosId,
                'city_id' => $etiOsaId,
                'neighbourhood_id' => $ikoyiId,
                'address' => 'Alexander Avenue, Old Ikoyi, Lagos',
                'lat' => 6.450400,
                'lng' => 3.435800,
                'bedrooms' => 3,
                'bathrooms' => 3,
                'toilets' => 4,
                'parking_spaces' => 2,
                'building_size_sqm' => 240.00,
                'land_size_sqm' => 0.00,
                'year_built' => 2023,
                'furnishing_status' => 'fully_furnished',
                'property_condition' => 'brand_new',
                'title_document' => 'Deed of Assignment',
                'is_featured' => 1,
                'is_luxury' => 1,
                'verification_status' => 'verified',
                'images' => [
                    'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1502005229762-ee1524e8679f?auto=format&fit=crop&w=1600&q=80'
                ]
            ],
            [
                'uuid' => 'p0000001-0000-0000-0000-000000000006',
                'reference_code' => 'AFR-COM-6001',
                'user_id' => $agent1Id,
                'agency_id' => $agencyId,
                'category_id' => $comCatId,
                'property_type_id' => $officeTypeId,
                'title' => 'Grade-A Commercial Office Tower (8 Floors Open-Plan)',
                'slug' => 'grade-a-commercial-office-tower-victoria-island',
                'description' => "Architectural landmark located in the heart of Victoria Island’s prime banking and multinational corporate corridor. Providing 4,800 sqm of LEED-certified open-plan office accommodation.\n\nSpecifications:\n- 3 high-speed 16-passenger destination-controlled KONE elevators\n- Multi-tier CCTV, biometric turnstiles, and metal detectors\n- Tri-fuel 500kVA backup power plant\n- Dedicated basement parking for 120 corporate vehicles\n- Raised access flooring and centralized VRV climate control.",
                'transaction_type' => 'commercial',
                'price' => 650000000.00,
                'price_prefix' => '',
                'price_suffix' => '/ annum',
                'state_id' => $lagosId,
                'city_id' => $etiOsaId,
                'neighbourhood_id' => $viId,
                'address' => 'Adeola Odeku Street, Victoria Island, Lagos',
                'lat' => 6.428100,
                'lng' => 3.421900,
                'bedrooms' => 0,
                'bathrooms' => 16,
                'toilets' => 20,
                'parking_spaces' => 120,
                'building_size_sqm' => 4800.00,
                'land_size_sqm' => 2500.00,
                'year_built' => 2021,
                'furnishing_status' => 'unfurnished',
                'property_condition' => 'excellent',
                'title_document' => 'Governor Consent',
                'is_featured' => 1,
                'is_luxury' => 1,
                'verification_status' => 'verified',
                'images' => [
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1600&q=80'
                ]
            ],
            [
                'uuid' => 'p0000001-0000-0000-0000-000000000007',
                'reference_code' => 'AFR-LND-7001',
                'user_id' => $agent2Id,
                'agency_id' => NULL,
                'category_id' => $landCatId,
                'property_type_id' => $landTypeId,
                'title' => 'Prime 2,500 SQM Hillside Residential Plot with Clear C of O',
                'slug' => 'prime-2500-sqm-hillside-residential-plot-asokoro-abuja',
                'description' => "A rare opportunity to acquire a pristine, build-ready hillside corner parcel in Asokoro, Abuja. Fully demarcated with cadastral beacons, asphalt paved access road, and immediate connection to municipal power and water trunk lines.\n\nDocumentation:\n- Original FCT Minister Allocation and Certificate of Occupancy\n- Verified AGIS (Abuja Geographic Information Systems) search report\n- Topographical survey plan with approved building permit.",
                'transaction_type' => 'land',
                'price' => 1200000000.00,
                'price_prefix' => '',
                'price_suffix' => '',
                'state_id' => $abujaId,
                'city_id' => $abujaCityId,
                'neighbourhood_id' => $asokoroId,
                'address' => 'Yakubu Gowon Crescent, Asokoro, Abuja',
                'lat' => 9.043600,
                'lng' => 7.524400,
                'bedrooms' => 0,
                'bathrooms' => 0,
                'toilets' => 0,
                'parking_spaces' => 0,
                'building_size_sqm' => 0.00,
                'land_size_sqm' => 2500.00,
                'year_built' => NULL,
                'furnishing_status' => 'unfurnished',
                'property_condition' => 'brand_new',
                'title_document' => 'FCT Certificate of Occupancy',
                'is_featured' => 1,
                'is_luxury' => 1,
                'verification_status' => 'verified',
                'images' => [
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1600&q=80'
                ]
            ],
            [
                'uuid' => 'p0000001-0000-0000-0000-000000000008',
                'reference_code' => 'AFR-IKY-8001',
                'user_id' => $agent1Id,
                'agency_id' => $agencyId,
                'category_id' => $resCatId,
                'property_type_id' => $detachedTypeId,
                'title' => 'Contemporary 5-Bedroom Detached Villa with Private Cinema & Pool',
                'slug' => 'contemporary-5-bedroom-detached-villa-old-ikoyi',
                'description' => "Nestled on a quiet, tree-lined avenue in Old Ikoyi, this masterfully crafted villa balances understated contemporary elegance with superior family functionality.\n\nFeatures:\n- 5 luxurious en-suite bedrooms with custom German cabinetry\n- Private heated swimming pool with teak wood sundeck\n- Fully integrated automated audio-visual entertainment system\n- 10-seater cinema room with velvet acoustic paneling\n- 2-room staff quarters and security gatehouse.",
                'transaction_type' => 'sale',
                'price' => 1950000000.00,
                'price_prefix' => '',
                'price_suffix' => '',
                'state_id' => $lagosId,
                'city_id' => $etiOsaId,
                'neighbourhood_id' => $ikoyiId,
                'address' => 'Bourdillon Road, Old Ikoyi, Lagos',
                'lat' => 6.452000,
                'lng' => 3.438000,
                'bedrooms' => 5,
                'bathrooms' => 6,
                'toilets' => 7,
                'parking_spaces' => 6,
                'building_size_sqm' => 850.00,
                'land_size_sqm' => 1100.00,
                'year_built' => 2023,
                'furnishing_status' => 'semi_furnished',
                'property_condition' => 'brand_new',
                'title_document' => 'Federal Certificate of Occupancy',
                'is_featured' => 1,
                'is_luxury' => 1,
                'verification_status' => 'verified',
                'images' => [
                    'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?auto=format&fit=crop&w=1600&q=80'
                ]
            ]
        ];

        $stmtProp = $this->pdo->prepare("INSERT INTO properties 
            (uuid, reference_code, user_id, agency_id, category_id, property_type_id, title, slug, description,
             transaction_type, price, price_prefix, price_suffix, currency, country_id, state_id, city_id,
             neighbourhood_id, address, latitude, longitude, bedrooms, bathrooms, toilets, parking_spaces,
             building_size_sqm, land_size_sqm, year_built, furnishing_status, property_condition, title_document,
             status, verification_status, is_featured, is_luxury, featured_until, view_count, favourite_count)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'NGN', 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), 142, 28)
            ON DUPLICATE KEY UPDATE title = VALUES(title), price = VALUES(price)");

        $stmtImg = $this->pdo->prepare("INSERT INTO property_images 
            (property_id, image_url, thumbnail_url, caption, is_cover, display_order)
            VALUES (?, ?, ?, ?, ?, ?)");

        $stmtAm = $this->pdo->prepare("INSERT IGNORE INTO property_amenities (property_id, amenity_id) VALUES (?, ?)");

        $allAmenityIds = $this->pdo->query("SELECT id FROM amenities")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($properties as $prop) {
            $stmtProp->execute([
                $prop['uuid'], $prop['reference_code'], $prop['user_id'], $prop['agency_id'],
                $prop['category_id'], $prop['property_type_id'], $prop['title'], $prop['slug'],
                $prop['description'], $prop['transaction_type'], $prop['price'], $prop['price_prefix'],
                $prop['price_suffix'], $prop['state_id'], $prop['city_id'], $prop['neighbourhood_id'],
                $prop['address'], $prop['lat'], $prop['lng'], $prop['bedrooms'], $prop['bathrooms'],
                $prop['toilets'], $prop['parking_spaces'], $prop['building_size_sqm'], $prop['land_size_sqm'],
                $prop['year_built'], $prop['furnishing_status'], $prop['property_condition'],
                $prop['title_document'], $prop['verification_status'], $prop['is_featured'], $prop['is_luxury']
            ]);

            $propId = $this->pdo->lastInsertId() ?: $this->pdo->query("SELECT id FROM properties WHERE slug = '{$prop['slug']}'")->fetchColumn();

            // Insert images
            $order = 1;
            foreach ($prop['images'] as $imgUrl) {
                $isCover = ($order === 1) ? 1 : 0;
                $stmtImg->execute([$propId, $imgUrl, $imgUrl, $prop['title'], $isCover, $order++]);
            }

            // Attach 6 random amenities
            shuffle($allAmenityIds);
            for ($i = 0; $i < min(6, count($allAmenityIds)); $i++) {
                $stmtAm->execute([$propId, $allAmenityIds[$i]]);
            }
        }
    }
}
