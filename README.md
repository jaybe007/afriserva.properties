# Afriserva Real Estate: Enterprise Property Marketplace & Asset Management Ecosystem

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![Database](https://img.shields.io/badge/Database-MySQL%208.0%20%2F%20MariaDB%2010.4%2B-orange.svg)](https://mariadb.org)
[![Security](https://img.shields.io/badge/Security-Argon2id%20%7C%20CSRF%20%7C%20RBAC-green.svg)](#security-architecture)
[![License](https://img.shields.io/badge/License-Proprietary-gold.svg)](#)

- **Brand Name**: **Afriserva Real Estate**
- **Main Website**: [afriserva.com](https://afriserva.com)
- **Corporate Entity**: **Cloud Current International Limited**
- **Company Website**: [cloudcurrentng.com](https://cloudcurrentng.com)

An institutional-grade, multi-tenant real estate marketplace and property asset management ecosystem engineered for the luxury, residential, commercial, and diaspora investment sectors. Built with modern Object-Oriented PHP 8.2+, MySQL InnoDB relational database, vanilla ES6+ modules, Leaflet/OpenStreetMap geospatial mapping, and an institutional design system.

---

## 1. System Overview & Technology Stack

- **Backend Architecture**: Modern Object-Oriented PHP (8.2+) with strict MVC separation, custom router with parameter pattern matching, dependency injection, and PSR-4 autoloading via Composer.
- **Frontend & Aesthetics**: Semantic HTML5, modern CSS3 design system with custom tokens (`--color-primary: #0B192C`, `--color-accent: #D4AF37`), Vanilla ES modules, and Leaflet interactive map clustering.
- **Database Architecture**: MySQL 8.0+ / MariaDB 10.4+ using InnoDB storage engine, 43 normalized tables, foreign key constraints with cascade/restrict rules, decimal monetary fields (`DECIMAL(15,2)`), and composite indexing.
- **Security & Data Integrity**: Argon2id password hashing, cryptographic CSRF tokens, strict prepared statements, Content Security Policy (CSP), HTTP security headers, role-based access control (RBAC), and server-side payment verification.

---

## 2. Core Personas & System Roles

The platform enforces strict Role-Based Access Control (RBAC) with cryptographic Argon2id password hashing.

| Role / Persona | Username / Email | Access Level & Capabilities |
| :--- | :--- | :--- |
| **Super Administrator** | `alexander.wright@afriserva.com` | Root system control, listing moderation, user management, global settings |
| **Compliance Moderator** | `olufunke.adeyemi@afriserva.com` | Listing document verification, review queues, legal title auditing |
| **Elite Agent** | `chioma.okonkwo@prestigeprime.ng` | Luxury portfolio listings, inbound client CRM leads, inspection scheduler |
| **Abuja Diplomatic Agent**| `musa.agent@afriserva.com` | Diplomatic zone estates, viewing appointments, WhatsApp integrations |
| **Agency Principal** | `babatunde.balogun@prestigeprime.ng` | Corporate brokerage branding, team members, enterprise subscription |
| **Private Landlord** | `dr.emeka.nnamani@afriserva.com` | Direct residential listings, rental management |
| **Client / Investor** | `zainab.aliyu@afriserva.com` | Property discovery, inspection tour bookings, inquiry dispatches |

> **Production Security Notice**: All production passwords must meet high-entropy complexity standards (minimum 16 characters with symbols, uppercase, lowercase, and digits). Production password hashes are stored in the database using `PASSWORD_ARGON2ID`. Use `database/010_update_production_credentials.sql` to apply credential updates to your live deployment database.

---

## 3. Core Modules & Production Capabilities

### 1. Public Marketplace & Discovery
- **Hero Showcase**: Multi-tab search engine (Buy, Rent, Short Lets, Commercial, Land & Plots).
- **Advanced Filtering**: Full-text keywords, State, City, Neighbourhood, Min/Max Price, Bedrooms, Bathrooms, Furnishing status, and Title document type.
- **Interactive Map Discovery**: Geospatial map powered by Leaflet & OpenStreetMap, displaying clustered pins, price badges, and preview cards without third-party API costs.
- **Detailed Property Pages**: High-resolution photography gallery, architectural specs, verified amenities, vicinity map, interactive mortgage financing calculator, and similar properties.

### 2. Client Enquiries & Viewing Workflows
- **Inbound Lead Generation**: In-app inquiries routed directly to the property's assigned advisor.
- **Inspection Tour Scheduling**: Integrated modal allowing prospective clients to book accompanied physical or live guided video inspections.
- **Direct Messaging**: Instant WhatsApp click-to-chat integration.

### 3. Agent & Landlord Portal
- **KPI Summary Cards**: Real-time counters for active listings, inbound leads, pending inspection tours, and portfolio views.
- **Multi-Step Listing Wizard**: Add properties with title, classification, pricing terms, GPS coordinates, dimensions, amenities checklist, and title document proof.
- **CRM Leads Manager**: Real-time table of prospective client inquiries with email and WhatsApp follow-up actions.
- **Viewing Calendar**: Schedule of confirmed, pending, and rescheduled walkthroughs.

### 4. Monetization & Payment Gateways
- **Subscription Tiers**:
  - *Starter Agent* (Free / ₦0) - 3 listings
  - *Professional Agent* (₦35,000 / month) - 25 listings + 3 featured slots
  - *Corporate Agency* (₦120,000 / month) - 100 listings + 12 featured slots
  - *Developer Signature* (₦350,000 / quarter) - 250 listings + 30 featured slots
- **Payment Abstraction**: Supports Paystack, Flutterwave, and an Instant Test Sandbox Evaluation mode.
- **Webhooks**: Idempotent event logging in `payment_webhook_events` with HMAC signature validation.

### 5. Administrative Command Center
- **System Metrics**: Total registered accounts, active listings, pending legal verifications, and lead volume.
- **Moderation Queue**: Review submitted properties, approve for public listing, reject with reviewer notes, or toggle featured badges.
- **Account Clearance**: Suspend fraudulent accounts or reactivate verified professionals.
- **Global Settings Editor**: Customize platform branding, support hotlines, currency symbols, and gateway toggles.

---

## 4. Installation & Local Development Setup

### Prerequisites
- PHP 8.2 or higher with extensions: `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `gd`.
- MySQL 8.0 or MariaDB 10.4+.
- XAMPP / Apache / Nginx.

### Step-by-Step Installation

1. **Clone or Navigate to the Project Root**:
   ```bash
   cd c:\xampp\htdocs\properties
   ```

2. **Environment Configuration**:
   ```bash
   copy .env.example .env
   ```
   Verify database credentials in `.env`:
   ```ini
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=afriserva_properties
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Database Schema & Seeders**:
   ```bash
   # Import schema
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS afriserva_properties;"
   mysql -u root afriserva_properties < database/schema.sql

   # Run comprehensive real estate seeder
   php database/seed.php
   ```

4. **Run Automated Tests**:
   ```bash
   php tests/run_tests.php
   ```

5. **Start Application**:
   - Access directly through XAMPP Apache: `http://localhost/properties/`
   - Or run the built-in development server:
     ```bash
     php -S 127.0.0.1:8080 -t public
     ```
     and open `http://127.0.0.1:8080/`.

---

## 5. Security Architecture & Threat Mitigation

- **SQL Injection**: 100% prepared statements via PDO wrapper. Zero raw SQL string interpolations.
- **Cross-Site Scripting (XSS)**: Context-aware HTML escaping using `e()` helper (`htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`).
- **Cross-Site Request Forgery (CSRF)**: Cryptographic 64-character hex tokens validated on all state-altering requests (`POST`, `PUT`, `DELETE`).
- **Session Security**: Cookie attributes configured with `HttpOnly`, `SameSite=Lax`, and configurable `Secure` flag. Session regeneration on privilege elevation.
- **Access Control (RBAC)**: Route middleware validates authentication and user role clearance before controller execution.
- **Financial Precision**: Monetary amounts stored as `DECIMAL(15,2)`, preventing floating-point rounding errors.

---

## 6. Directory Structure

```
properties/
├── .env.example              # Environment variables template
├── composer.json             # PSR-4 Autoloading definition
├── composer.phar             # Composer binary
├── index.php                 # Root forwarder to public/
├── app/
│   ├── Core/                 # Framework: Database, Session, CSRF, Router, Auth, View, Request, Response
│   ├── Controllers/          # HomeController, PropertyController, AuthController, DashboardController, AdminController, PaymentController
│   ├── Models/               # Property, User, Enquiry, ViewingAppointment, Subscription, Location
│   ├── Middlewares/          # AuthMiddleware, RoleMiddleware, CsrfMiddleware
│   └── Helpers/              # Formatters & view helper functions
├── database/
│   ├── schema.sql            # Normalized 43-table MySQL DDL
│   ├── seed.php              # Seeder execution script
│   └── seeds/                # DatabaseSeeder.php with Nigerian locations, luxury properties, agents
├── public/
│   ├── .htaccess             # URL rewrite rules
│   ├── index.php             # Front-controller entry point
│   └── assets/
│       ├── css/              # variables.css, base.css, components.css, dashboard.css
│       └── js/               # app.js, modules/map.js
├── tests/
│   └── run_tests.php         # Automated unit & integration test suite
└── views/
    ├── layouts/              # main.php, dashboard.php, admin.php
    ├── home/                 # index.php
    ├── properties/           # index.php, show.php, map.php
    ├── auth/                 # login.php, register.php
    ├── dashboard/            # index.php, client.php, listings.php, create_listing.php, enquiries.php, viewings.php, subscription.php, checkout.php
    ├── admin/                # index.php, listings.php, users.php, settings.php
    └── errors/               # 404.php
```
