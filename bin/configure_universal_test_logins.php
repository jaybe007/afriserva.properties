<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Helpers/AuditLogger.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Subscription.php';

use App\Core\Database;
use App\Models\User;
use App\Models\Subscription;

Database::connect();

$universalPassword = 'Password123!';
$universalHash = password_hash($universalPassword, PASSWORD_BCRYPT);

echo "======================================================================\n";
echo " AFRISERVA UNIVERSAL TEST CREDENTIALS CONFIGURATION \n";
echo "======================================================================\n\n";

// 1. Update ALL existing users in the database to use the universal password
$updatedCount = Database::execute(
    "UPDATE users 
     SET password_hash = ?, 
         status = 'active', 
         email_verified_at = COALESCE(email_verified_at, NOW()), 
         two_factor_enabled = 0,
         two_factor_code = NULL,
         two_factor_code_expires_at = NULL",
    [$universalHash]
);
echo "  [OK] Updated all {$updatedCount} existing users in database to use password: '{$universalPassword}'\n";

// Clear any lockout throttle attempts
Database::execute("TRUNCATE TABLE login_attempts");
echo "  [OK] Cleared login attempts throttle table.\n";

// 2. Define standard test persona accounts
$personas = [
    [
        'email' => 'admin@afriserva.com',
        'full_name' => 'Afriserva Super Admin',
        'role' => 'super_admin',
        'phone' => '+2348000000001'
    ],
    [
        'email' => 'officer@afriserva.com',
        'full_name' => 'Afriserva Transaction Officer',
        'role' => 'transaction_officer',
        'phone' => '+2348000000002'
    ],
    [
        'email' => 'agent@afriserva.com',
        'full_name' => 'Chioma Okonkwo (Licensed Broker)',
        'role' => 'agent',
        'phone' => '+2348000000003'
    ],
    [
        'email' => 'seller@afriserva.com',
        'full_name' => 'Babatunde Adeleke (Property Owner)',
        'role' => 'seller',
        'phone' => '+2348000000004'
    ],
    [
        'email' => 'buyer@afriserva.com',
        'full_name' => 'Zainab Aliyu (Property Investor)',
        'role' => 'client',
        'phone' => '+2348000000005'
    ],
    [
        'email' => 'landlord@afriserva.com',
        'full_name' => 'Emeka Okafor (Portfolio Landlord)',
        'role' => 'landlord',
        'phone' => '+2348000000006'
    ],
    [
        'email' => 'tenant@afriserva.com',
        'full_name' => 'Farouk Danjuma (Verified Tenant)',
        'role' => 'tenant',
        'phone' => '+2348000000007'
    ],
    [
        'email' => 'test@afriserva.com',
        'full_name' => 'Afriserva Master Test User',
        'role' => 'super_admin',
        'phone' => '+2348000000000'
    ]
];

foreach ($personas as $p) {
    $existing = Database::fetch("SELECT id FROM users WHERE email = ? LIMIT 1", [$p['email']]);

    if ($existing) {
        $userId = (int)$existing['id'];
        Database::execute(
            "UPDATE users 
             SET full_name = ?, password_hash = ?, status = 'active', email_verified_at = NOW(), two_factor_enabled = 0 
             WHERE id = ?",
            [$p['full_name'], $universalHash, $userId]
        );
        echo "  [OK] Synchronized persona: {$p['email']} (Role: {$p['role']})\n";
    } else {
        $userId = User::create([
            'email' => $p['email'],
            'full_name' => $p['full_name'],
            'password' => $universalPassword,
            'phone' => $p['phone']
        ], $p['role']);
        echo "  [OK] Created new persona: {$p['email']} (Role: {$p['role']})\n";
    }

    // Ensure role assignment in user_roles
    $roleId = Database::fetchColumn("SELECT id FROM roles WHERE name = ? LIMIT 1", [$p['role']]);
    if ($roleId) {
        Database::execute(
            "INSERT INTO user_roles (user_id, role_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE role_id = VALUES(role_id)",
            [$userId, (int)$roleId]
        );
    }

    // Assign default commission plan if eligible
    Subscription::assignDefaultPlan($userId);
}

echo "\n======================================================================\n";
echo " ALL ACCOUNTS READY WITH UNIVERSAL PASSWORD: {$universalPassword} \n";
echo "======================================================================\n";
