<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

class SecurityHeadersMiddleware
{
    public static function apply(): void
    {
        if (headers_sent()) {
            return;
        }

        // Prevent Clickjacking
        header('X-Frame-Options: DENY');

        // Prevent MIME-type sniffing
        header('X-Content-Type-Options: nosniff');

        // Cross-Site Scripting Filter
        header('X-XSS-Protection: 1; mode=block');

        // Referrer Information Leakage Prevention
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Browser Hardware and Feature Restrictions
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(self)');

        // Content Security Policy (Strict with allowed trusted CDNs for Leaflet maps and Google Fonts)
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://unpkg.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com",
            "img-src 'self' data: https: blob:",
            "font-src 'self' https://fonts.gstatic.com data:",
            "connect-src 'self' https://api.paystack.co https://api.flutterwave.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self' https://checkout.paystack.com https://checkout.flutterwave.com",
            "frame-ancestors 'none'"
        ];

        header('Content-Security-Policy: ' . implode('; ', $csp));

        // Strict Transport Security (HSTS) if HTTPS is detected
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }
}
