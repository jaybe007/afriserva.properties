<?php

namespace App\Core\Payments;

use InvalidArgumentException;

class PaymentGatewayManager
{
    protected static array $instances = [];

    public static function gateway(?string $name = null): PaymentGatewayInterface
    {
        $name = strtolower($name ?: ($_ENV['DEFAULT_PAYMENT_GATEWAY'] ?? 'test_sandbox'));

        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        switch ($name) {
            case 'paystack':
                $gateway = new PaystackGateway();
                break;
            case 'flutterwave':
                $gateway = new FlutterwaveGateway();
                break;
            case 'sandbox':
            case 'test_sandbox':
            default:
                $gateway = new SandboxGateway();
                break;
        }

        self::$instances[$name] = $gateway;
        return $gateway;
    }
}
