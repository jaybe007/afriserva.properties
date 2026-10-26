<?php

namespace App\Models;

use App\Core\Database;
use Exception;

class User
{
    public static function findById(int $id): ?array
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = ? LIMIT 1", [$id]);
        if (!$user) return null;

        $roles = Database::query(
            "SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = ?",
            [$id]
        );
        $user['roles'] = array_column($roles, 'name');
        return $user;
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch("SELECT * FROM users WHERE email = ? LIMIT 1", [trim(strtolower($email))]);
    }

    public static function create(array $data, string $role = 'client'): int
    {
        $uuid = bin2hex(random_bytes(16));
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($uuid, 4));
        $passwordHash = password_hash($data['password'], PASSWORD_ARGON2ID);
        $verificationToken = bin2hex(random_bytes(24));
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $sql = "INSERT INTO users (
            uuid, full_name, email, phone, password_hash, status, 
            email_verification_token, email_verified_at, terms_accepted_at, privacy_accepted_at, 
            consent_ip_address, created_at
        ) VALUES (?, ?, ?, ?, ?, 'active', ?, NOW(), NOW(), NOW(), ?, NOW())";

        Database::execute($sql, [
            $uuid,
            trim($data['full_name']),
            trim(strtolower($data['email'])),
            trim($data['phone'] ?? ''),
            $passwordHash,
            $verificationToken,
            $clientIp
        ]);

        $userId = (int)Database::lastInsertId();
        self::assignRole($userId, $role);

        // If registered as an agent or agency admin, create agent profile stub
        if (in_array($role, ['agent', 'agency_admin'], true)) {
            Database::execute(
                "INSERT IGNORE INTO agent_profiles (user_id, license_no, experience_years, rating, total_reviews)
                 VALUES (?, ?, ?, 5.0, 0)",
                [$userId, 'PENDING-LIC-' . $userId, 1]
            );
        }

        // If registered as real estate agency admin, initialize agency firm and principal record
        if ($role === 'agency_admin') {
            try {
                $slug = 'agency-' . substr(bin2hex(random_bytes(4)), 0, 8);
                Database::execute(
                    "INSERT INTO agencies (name, slug, email, phone, address, city, state, is_verified, created_at) 
                     VALUES (?, ?, ?, ?, 'Headquarters Office', 'Lagos', 'Lagos', 1, NOW())",
                    [
                        trim($data['full_name']) . ' Realty Firm',
                        $slug,
                        trim(strtolower($data['email'])),
                        trim($data['phone'] ?? '+2348000000000')
                    ]
                );
                $newAgencyId = (int)Database::lastInsertId();
                Database::execute(
                    "INSERT INTO agency_members (agency_id, user_id, role_in_agency, status, created_at) 
                     VALUES (?, ?, 'principal', 'active', NOW())",
                    [$newAgencyId, $userId]
                );
                Database::execute(
                    "UPDATE agent_profiles SET agency_id = ? WHERE user_id = ?",
                    [$newAgencyId, $userId]
                );
            } catch (\Exception $e) {}
        }

        // Automatically assign default commission plan to eligible sellers, agents, and listing owners
        if (in_array($role, ['seller', 'agent', 'landlord', 'agency_admin', 'developer', 'property_manager'], true)) {
            try {
                \App\Models\Subscription::assignDefaultPlan($userId);
            } catch (\Throwable $e) {}
        }

        \App\Helpers\AuditLogger::log('user_registered', 'user', $userId, null, [
            'role' => $role,
            'ip' => $clientIp,
            'terms_consented' => true
        ]);

        return $userId;
    }

    public static function assignRole(int $userId, string $roleName): void
    {
        $roleId = Database::fetchColumn("SELECT id FROM roles WHERE name = ?", [$roleName]);
        if ($roleId) {
            Database::execute("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)", [$userId, $roleId]);
        }
    }

    public static function removeRole(int $userId, string $roleName): void
    {
        $roleId = Database::fetchColumn("SELECT id FROM roles WHERE name = ?", [$roleName]);
        if ($roleId) {
            Database::execute("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?", [$userId, $roleId]);
        }
    }

    public static function verifyEmailByToken(string $token): bool
    {
        $user = Database::fetch("SELECT id FROM users WHERE email_verification_token = ? LIMIT 1", [$token]);
        if (!$user) return false;

        Database::execute(
            "UPDATE users SET email_verified_at = NOW(), email_verification_token = NULL WHERE id = ?",
            [$user['id']]
        );

        \App\Helpers\AuditLogger::log('email_verified', 'user', (int)$user['id']);
        return true;
    }

    public static function verifyUser(int $userId): bool
    {
        Database::execute(
            "UPDATE users SET email_verified_at = NOW(), email_verification_token = NULL WHERE id = ?",
            [$userId]
        );
        \App\Helpers\AuditLogger::log('email_verified', 'user', $userId);
        return true;
    }

    public static function createPasswordReset(string $email): ?string
    {
        $user = self::findByEmail($email);
        if (!$user) return null;

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        // Clear existing resets for this email
        Database::execute("DELETE FROM password_resets WHERE email = ?", [trim(strtolower($email))]);

        Database::execute(
            "INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())",
            [trim(strtolower($email)), $token, $expiresAt]
        );

        \App\Helpers\AuditLogger::log('password_reset_requested', 'user', (int)$user['id'], null, ['email' => $email]);
        return $token;
    }

    public static function validatePasswordReset(string $token): ?array
    {
        return Database::fetch(
            "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1",
            [$token]
        );
    }

    public static function resetPasswordWithToken(string $token, string $newPassword): bool
    {
        $reset = self::validatePasswordReset($token);
        if (!$reset) return false;

        $user = self::findByEmail($reset['email']);
        if (!$user) return false;

        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        Database::execute(
            "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?",
            [$passwordHash, $user['id']]
        );

        // Delete reset token
        Database::execute("DELETE FROM password_resets WHERE email = ?", [$reset['email']]);

        \App\Helpers\AuditLogger::log('password_reset_completed', 'user', (int)$user['id']);
        return true;
    }

    public static function requestDeletion(int $userId, string $reason): int
    {
        Database::execute(
            "INSERT INTO account_deletion_requests (user_id, reason, status, created_at) VALUES (?, ?, 'pending', NOW())",
            [$userId, trim($reason)]
        );

        $id = (int)Database::lastInsertId();
        \App\Helpers\AuditLogger::log('account_deletion_requested', 'user', $userId, null, ['reason' => $reason]);
        return $id;
    }

    public static function updatePreferences(int $userId, array $prefs): bool
    {
        $email = !empty($prefs['notify_email']) ? 1 : 0;
        $sms = !empty($prefs['notify_sms']) ? 1 : 0;
        $marketing = !empty($prefs['notify_marketing']) ? 1 : 0;

        Database::execute(
            "UPDATE users SET notify_email = ?, notify_sms = ?, notify_marketing = ?, updated_at = NOW() WHERE id = ?",
            [$email, $sms, $marketing, $userId]
        );

        \App\Helpers\AuditLogger::log('preferences_updated', 'user', $userId, null, $prefs);
        return true;
    }

    public static function updatePrivacy(int $userId, array $privacy): bool
    {
        $phonePublic = !empty($privacy['is_phone_public']) ? 1 : 0;
        $emailPublic = !empty($privacy['is_email_public']) ? 1 : 0;

        Database::execute(
            "UPDATE users SET is_phone_public = ?, is_email_public = ?, updated_at = NOW() WHERE id = ?",
            [$phonePublic, $emailPublic, $userId]
        );

        \App\Helpers\AuditLogger::log('privacy_settings_updated', 'user', $userId, null, $privacy);
        return true;
    }

    public static function toggleTwoFactor(int $userId, bool $enable): bool
    {
        $val = $enable ? 1 : 0;
        $secret = $enable ? bin2hex(random_bytes(16)) : null;

        Database::execute(
            "UPDATE users SET two_factor_enabled = ?, two_factor_secret = ?, updated_at = NOW() WHERE id = ?",
            [$val, $secret, $userId]
        );

        \App\Helpers\AuditLogger::log($enable ? '2fa_enabled' : '2fa_disabled', 'user', $userId);
        return true;
    }

    public static function getAgents(int $limit = 6): array
    {
        $sql = "SELECT u.id, u.full_name, u.email, u.phone, u.avatar_url, u.bio,
                       ap.specialization, ap.experience_years, ap.rating, ap.total_reviews, ap.whatsapp_number, ap.license_no,
                       ag.name AS agency_name, ag.logo_url AS agency_logo,
                       (SELECT COUNT(*) FROM properties p WHERE p.user_id = u.id AND p.status = 'published') AS total_listings
                FROM users u
                JOIN user_roles ur ON ur.user_id = u.id
                JOIN roles r ON r.id = ur.role_id AND r.name IN ('agent', 'agency_admin')
                LEFT JOIN agent_profiles ap ON ap.user_id = u.id
                LEFT JOIN agencies ag ON ag.id = ap.agency_id
                WHERE u.status = 'active'
                GROUP BY u.id
                ORDER BY ap.rating DESC, total_listings DESC
                LIMIT {$limit}";

        return Database::query($sql);
    }

    public static function getAgencies(int $limit = 6): array
    {
        $sql = "SELECT ag.*,
                       (SELECT COUNT(*) FROM properties p WHERE p.agency_id = ag.id AND p.status = 'published') AS total_listings,
                       (SELECT COUNT(*) FROM agency_members am WHERE am.agency_id = ag.id) AS total_agents
                FROM agencies ag
                WHERE ag.is_verified = 1
                ORDER BY total_listings DESC
                LIMIT {$limit}";

        return Database::query($sql);
    }
}
