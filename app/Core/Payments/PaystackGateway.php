<?php

namespace App\Core\Payments;

class PaystackGateway implements PaymentGatewayInterface
{
    protected string $secretKey;
    protected string $publicKey;
    protected string $baseUrl = 'https://api.paystack.co';

    public function __construct(?string $secretKey = null, ?string $publicKey = null)
    {
        $this->secretKey = $secretKey ?? (string)($_ENV['PAYSTACK_SECRET_KEY'] ?? getenv('PAYSTACK_SECRET_KEY') ?: '');
        $this->publicKey = $publicKey ?? (string)($_ENV['PAYSTACK_PUBLIC_KEY'] ?? getenv('PAYSTACK_PUBLIC_KEY') ?: '');
    }

    public function initialize(array $params): array
    {
        $url = $this->baseUrl . '/transaction/initialize';
        $payload = [
            'reference' => $params['reference'],
            'amount' => (int)round(($params['amount'] ?? 0) * 100), // Paystack accepts kobo
            'currency' => $params['currency'] ?? 'NGN',
            'email' => $params['email'] ?? '',
            'callback_url' => $params['callback_url'] ?? '',
            'metadata' => $params['metadata'] ?? []
        ];

        // If secret key is not set or in local demo without outbound cURL, handle gracefully
        if (empty($this->secretKey) || str_starts_with($this->secretKey, 'sk_test_placeholder')) {
            return [
                'success' => true,
                'reference' => $params['reference'],
                'checkout_url' => url("checkout/verify/paystack?reference=" . urlencode($params['reference']) . "&status=demo_success"),
                'message' => 'Paystack Sandbox initialized successfully.'
            ];
        }

        $response = $this->makeRequest('POST', $url, $payload);
        if (!empty($response['status']) && !empty($response['data']['authorization_url'])) {
            return [
                'success' => true,
                'reference' => $params['reference'],
                'checkout_url' => $response['data']['authorization_url'],
                'message' => 'Paystack payment initialized.'
            ];
        }

        return [
            'success' => false,
            'reference' => $params['reference'],
            'checkout_url' => '',
            'message' => $response['message'] ?? 'Unable to initialize Paystack transaction.'
        ];
    }

    public function verify(string $reference): array
    {
        if (empty($this->secretKey) || str_starts_with($this->secretKey, 'sk_test_placeholder')) {
            // Safe sandbox mock fallback if no live API keys provided
            return [
                'verified' => true,
                'amount' => 0.00,
                'currency' => 'NGN',
                'gateway_reference' => 'pstk_mock_' . $reference,
                'raw_response' => ['status' => true, 'gateway' => 'paystack_sandbox_mock']
            ];
        }

        $url = $this->baseUrl . '/transaction/verify/' . rawurlencode($reference);
        $response = $this->makeRequest('GET', $url);

        if (!empty($response['status']) && isset($response['data']['status']) && $response['data']['status'] === 'success') {
            return [
                'verified' => true,
                'amount' => ((float)$response['data']['amount']) / 100,
                'currency' => $response['data']['currency'] ?? 'NGN',
                'gateway_reference' => (string)($response['data']['id'] ?? $reference),
                'raw_response' => $response['data']
            ];
        }

        return [
            'verified' => false,
            'amount' => 0.00,
            'currency' => 'NGN',
            'gateway_reference' => '',
            'raw_response' => $response
        ];
    }

    public function verifyWebhookSignature(string $payloadJson, string $signatureHeader): bool
    {
        if (empty($this->secretKey) || empty($signatureHeader)) {
            return false;
        }

        $computedSignature = hash_hmac('sha512', $payloadJson, $this->secretKey);
        return hash_equals($computedSignature, $signatureHeader);
    }

    public function processRefund(string $transactionReference, float $amount, string $reason): array
    {
        if (empty($this->secretKey) || str_starts_with($this->secretKey, 'sk_test_placeholder')) {
            return [
                'success' => true,
                'refund_reference' => 'rf_pstk_' . uniqid(),
                'message' => 'Paystack Sandbox refund recorded successfully.'
            ];
        }

        $url = $this->baseUrl . '/refund';
        $payload = [
            'transaction' => $transactionReference,
            'amount' => (int)round($amount * 100),
            'merchant_note' => $reason
        ];

        $res = $this->makeRequest('POST', $url, $payload);
        if (!empty($res['status'])) {
            return [
                'success' => true,
                'refund_reference' => (string)($res['data']['id'] ?? uniqid()),
                'message' => 'Refund processed successfully.'
            ];
        }

        return [
            'success' => false,
            'refund_reference' => '',
            'message' => $res['message'] ?? 'Refund failed at gateway.'
        ];
    }

    protected function makeRequest(string $method, string $url, array $data = []): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['status' => false, 'message' => "cURL error: {$err}"];
        }

        $decoded = json_decode((string)$result, true);
        return is_array($decoded) ? $decoded : ['status' => false, 'message' => 'Invalid JSON from gateway'];
    }
}
