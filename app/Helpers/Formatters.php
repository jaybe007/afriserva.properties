<?php

use App\Core\Session;
use App\Core\Auth;
use App\Core\CSRF;

if (!function_exists('format_money')) {
    function format_money(float|int|string|null $amount, string $currency = 'NGN'): string
    {
        if ($amount === null || $amount === '') {
            return 'Price on Application';
        }

        $activeCurrency = Session::get('user_currency', 'NGN');
        $num = (float)$amount;

        // If user changed preferred viewing currency from NGN to USD/GBP/EUR
        if ($activeCurrency !== 'NGN' && $currency === 'NGN') {
            try {
                $rate = \App\Core\Database::fetchColumn(
                    "SELECT exchange_rate FROM exchange_rates WHERE base_currency = ? AND target_currency = 'NGN' LIMIT 1",
                    [$activeCurrency]
                );
                if ($rate && (float)$rate > 0) {
                    $num = $num / (float)$rate;
                    $currency = $activeCurrency;
                }
            } catch (\Exception $e) {}
        }

        $symbols = [
            'NGN' => '₦',
            'USD' => '$',
            'GBP' => '£',
            'EUR' => '€',
            'AED' => 'AED ',
            'GHS' => 'GH₵ '
        ];

        $symbol = $symbols[$currency] ?? $currency . ' ';
        return $symbol . number_format($num, 0, '.', ',');
    }
}

if (!function_exists('format_sqm')) {
    function format_sqm(float|int|null $sqm, ?string $targetUnit = null): string
    {
        if (!$sqm || $sqm <= 0) {
            return '—';
        }
        $unit = $targetUnit ?? Session::get('measurement_unit', 'SQM');

        if ($unit === 'SQFT') {
            $sqft = (float)$sqm * 10.7639;
            return number_format($sqft, 0) . ' SQFT';
        }
        if ($unit === 'Plots') {
            $plots = (float)$sqm / 648.0; // standard Lagos plot is ~648 sqm
            return number_format($plots, 1) . ' Plots';
        }
        if ($unit === 'Acres') {
            $acres = (float)$sqm / 4046.86;
            return number_format($acres, 2) . ' Acres';
        }
        if ($unit === 'Hectares') {
            $ha = (float)$sqm / 10000.0;
            return number_format($ha, 2) . ' Ha';
        }

        return number_format((float)$sqm, 0) . ' SQM';
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $settings = null;
        if ($settings === null) {
            $settings = [
                'app.name' => getenv('APP_NAME') ?: 'Afriserva Real Estate',
                'brand.name' => getenv('BRAND_NAME') ?: 'Afriserva Real Estate',
                'main.website' => getenv('MAIN_WEBSITE') ?: 'https://afriserva.com',
                'company.name' => getenv('COMPANY_NAME') ?: 'Cloud Current International Limited',
                'company.website' => getenv('COMPANY_WEBSITE') ?: 'https://cloudcurrentng.com',
                'app.url' => rtrim(getenv('APP_URL') ?: 'http://localhost/properties/public', '/'),
                'app.env' => getenv('APP_ENV') ?: 'development',
                'app.debug' => filter_var(getenv('APP_DEBUG') ?: true, FILTER_VALIDATE_BOOLEAN),
                'currency.code' => getenv('APP_CURRENCY_CODE') ?: 'NGN',
                'currency.symbol' => getenv('APP_CURRENCY_SYMBOL') ?: '₦',
                'db.host' => getenv('DB_HOST') ?: '127.0.0.1',
                'db.database' => getenv('DB_DATABASE') ?: 'afriserva_properties',
                'db.username' => getenv('DB_USERNAME') ?: 'root',
                'db.password' => getenv('DB_PASSWORD') ?: '',
            ];
        }
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = config('app.url');
        $trimmed = ltrim($path, '/');
        return $trimmed === '' ? $base : $base . '/' . $trimmed;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return CSRF::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return Session::get('user');
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return Session::has('user');
    }
}

if (!function_exists('has_role')) {
    function has_role(string|array $roles): bool
    {
        $user = auth_user();
        if (!$user) return false;
        $userRoles = $user['roles'] ?? [];
        if (is_string($roles)) {
            return in_array($roles, $userRoles, true);
        }
        foreach ($roles as $r) {
            if (in_array($r, $userRoles, true)) return true;
        }
        return false;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            Session::setFlash($key, $message);
            return null;
        }
        return Session::getFlash($key);
    }
}

if (!function_exists('format_datetime_user')) {
    function format_datetime_user(?string $datetime, string $format = 'M d, Y h:i A', ?string $userTimezone = null): string
    {
        if (empty($datetime)) {
            return '—';
        }
        $tzName = $userTimezone ?? (auth_user()['timezone'] ?? Session::get('user_timezone', 'Africa/Lagos'));
        try {
            $dt = new \DateTime($datetime, new \DateTimeZone('UTC'));
            $dt->setTimezone(new \DateTimeZone($tzName));
            return $dt->format($format);
        } catch (\Exception $e) {
            return date($format, strtotime($datetime));
        }
    }
}
