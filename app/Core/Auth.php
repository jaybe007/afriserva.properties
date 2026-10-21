<?php

namespace App\Core;

use PDO;

class Auth
{
    public const MAX_FAILED_ATTEMPTS = 5;
    public const LOCKOUT_MINUTES = 15;

    public static function isThrottled(string $ip, string $email): bool
    {
        $count = (int)Database::fetchColumn(
            "SELECT COUNT(*) FROM login_attempts 
             WHERE (ip_address = ? OR email = ?) 
               AND attempted_at >= (NOW() - INTERVAL " . (int)self::LOCKOUT_MINUTES . " MINUTE)",
            [$ip, trim(strtolower($email))]
        );

        return $count >= self::MAX_FAILED_ATTEMPTS;
    }

    public static function recordFailedAttempt(string $ip, string $email): void
    {
        try {
            Database::execute(
                "INSERT INTO login_attempts (ip_address, email, attempted_at) VALUES (?, ?, NOW())",
                [$ip, trim(strtolower($email))]
            );
        } catch (\Exception $e) {}
    }

    public static function clearFailedAttempts(string $ip, string $email): void
    {
        try {
            Database::execute(
                "DELETE FROM login_attempts WHERE ip_address = ? OR email = ?",
                [$ip, trim(strtolower($email))]
            );
        } catch (\Exception $e) {}
    }

    /**
     * Authenticate user credentials with status checking and rate throttling
     * Returns array with ['success' => bool, 'error' => string|null, 'requires_2fa' => bool, 'user' => array|null]
     */
    public static function authenticate(string $email, string $password, string $ip): array
    {
        $email = trim(strtolower($email));

        if (self::isThrottled($ip, $email)) {
            return [
                'success' => false,
                'error' => 'Too many failed login attempts detected. For security, account login is locked for 15 minutes.',
                'requires_2fa' => false,
                'user' => null
            ];
        }

        $user = Database::fetch(
            "SELECT * FROM users WHERE email = ? LIMIT 1",
            [$email]
        );

        if (!$user) {
            self::recordFailedAttempt($ip, $email);
            return [
                'success' => false,
                'error' => 'Invalid email address or password combination.',
                'requires_2fa' => false,
                'user' => null
            ];
        }

        // Check account suspension
        if ($user['status'] === 'suspended') {
            return [
                'success' => false,
                'error' => 'Your account has been suspended by administration. Please contact Afriserva compliance support.',
                'requires_2fa' => false,
                'user' => null
            ];
        }

        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'error' => 'Your account is pending verification or activation.',
                'requires_2fa' => false,
                'user' => null
            ];
        }

        if (!password_verify($password, $user['password_hash'])) {
            self::recordFailedAttempt($ip, $email);
            \App\Helpers\AuditLogger::log('failed_login_attempt', 'user', (int)$user['id'], null, ['ip' => $ip, 'email' => $email]);
            return [
                'success' => false,
                'error' => 'Invalid email address or password combination.',
                'requires_2fa' => false,
                'user' => null
            ];
        }

        // Clear failed attempts on successful password verification
        self::clearFailedAttempts($ip, $email);

        // Fetch user roles
        $roles = Database::query(
            "SELECT r.name FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = ?",
            [$user['id']]
        );
        $user['roles'] = array_column($roles, 'name');

        // Check 2FA requirement
        if (!empty($user['two_factor_enabled']) && (int)$user['two_factor_enabled'] === 1) {
            return [
                'success' => true,
                'error' => null,
                'requires_2fa' => true,
                'user' => $user
            ];
        }

        self::login($user);
        \App\Helpers\AuditLogger::log('user_login', 'user', (int)$user['id'], null, ['ip' => $ip]);

        return [
            'success' => true,
            'error' => null,
            'requires_2fa' => false,
            'user' => $user
        ];
    }

    public static function attempt(string $email, string $password): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $result = self::authenticate($email, $password, $ip);
        return $result['success'] && !$result['requires_2fa'];
    }

    public static function login(array $user): void
    {
        Session::start();
        Session::regenerate();
        unset($user['password_hash']);
        Session::set('user', $user);

        // Update session log in user_sessions
        try {
            $sessionId = session_id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Browser';
            Database::execute(
                "REPLACE INTO user_sessions (id, user_id, ip_address, user_agent, payload, last_activity)
                 VALUES (?, ?, ?, ?, '', ?)",
                [$sessionId, $user['id'], $ip, $ua, time()]
            );
        } catch (\Exception $e) {
            // Ignore session log table issues in dev
        }
    }

    public static function logout(): void
    {
        Session::start();
        $user = Session::get('user');
        if ($user) {
            try {
                Database::execute("DELETE FROM user_sessions WHERE id = ?", [session_id()]);
                \App\Helpers\AuditLogger::log('user_logout', 'user', (int)$user['id']);
            } catch (\Exception $e) {}
        }
        Session::remove('user');
        Session::destroy();
    }

    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function check(): bool
    {
        return Session::has('user');
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int)$u['id'] : null;
    }

    public static function hasRole(string|array $roles): bool
    {
        $user = self::user();
        if (!$user) return false;
        $userRoles = $user['roles'] ?? [];

        if (in_array('super_admin', $userRoles, true)) {
            return true; // Super admin possesses superuser bypass
        }

        if (is_string($roles)) {
            return in_array($roles, $userRoles, true);
        }

        foreach ($roles as $r) {
            if (in_array($r, $userRoles, true)) {
                return true;
            }
        }

        return false;
    }

    public static function getSessions(int $userId): array
    {
        return Database::query(
            "SELECT * FROM user_sessions WHERE user_id = ? ORDER BY last_activity DESC",
            [$userId]
        );
    }

    public static function revokeSession(int $userId, string $sessionId): bool
    {
        Database::execute(
            "DELETE FROM user_sessions WHERE user_id = ? AND id = ?",
            [$userId, $sessionId]
        );
        \App\Helpers\AuditLogger::log('session_revocation', 'user_session', $userId, null, ['session_id' => $sessionId]);
        return true;
    }

    public static function revokeOtherSessions(int $userId, string $currentSessionId): bool
    {
        Database::execute(
            "DELETE FROM user_sessions WHERE user_id = ? AND id != ?",
            [$userId, $currentSessionId]
        );
        \App\Helpers\AuditLogger::log('all_other_sessions_revoked', 'user_session', $userId);
        return true;
    }
}
