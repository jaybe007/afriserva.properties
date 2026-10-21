<?php

namespace App\Core;

class CSRF
{
    public static function token(): string
    {
        Session::start();
        $token = Session::get('_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    public static function validate(?string $submittedToken): bool
    {
        Session::start();
        $sessionToken = Session::get('_csrf_token');
        if (!$sessionToken || !$submittedToken) {
            return false;
        }
        return hash_equals($sessionToken, $submittedToken);
    }

    public static function rotate(): void
    {
        Session::start();
        $newToken = bin2hex(random_bytes(32));
        Session::set('_csrf_token', $newToken);
    }
}
