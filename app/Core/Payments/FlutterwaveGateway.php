<?php

namespace App\Core\Payments;

class FlutterwaveGateway implements PaymentGatewayInterface
{
    protected string $secretKey;
    protected string $secretHash;
    protected string $baseUrl = 'https://api.flutterwave.com/v3';

    public function __construct(?string $secretKey = null, ?string $secretHash = null)
    {
        $this->secretKey = $secretKey ?? (string)($_ENV['FLW_SECRET_KEY'] ?? getenv('FLW_SECRET_KEY') ?: '');
        $this->secretHash = $secretHash ?? (string)($_ENV['FLW_SECRET_HASH'] ?? getenv('FLW_SECRET_HASH') ?: '');
    }

    public function initialize(array $params): array
    {
        $url = $this->baseUrl . '/payments';
        $payload = [
            'tx_ref' => $params['reference'],
            'amount' => (float)($params['amount'] ?? 0),
            'currency' => $params['currency'] ?? 'NGN',
            'redirect_url' => $params['callback_url'] ?? '',
            'customer' => [
                'email' => $params['email'] ?? '',
                'name' => $params['name'] ?? ''
            ],
            'customizations' => [
                'title' => 'Afriserva Properties Payment',
                'description' => $params['description'] ?? 'Property Subscription / Featured Listing'
            ]
        ];

        if (empty($this->secretKey) || str_starts_with($this->secretKey, 'flw_placeholder')) {
            return [
                'success' => true,
                'reference' => $params['reference'],
                'checkout_url' => url("checkout/verify/flutterwave?tx_ref=" . urlencode($params['reference']) . "&status=successful"),
                'message' => 'Flutterwave Sandbox initialized.'
            ];
        }

        $res = $this->makeRequest('POST', $url, $payload);
        if (!empty($res['status']) && $res['status'] === 'success' && !empty($res['data']['link'])) {
            return [
                'success' => true,
                'reference' => $params['reference'],
                'checkout_url' => $res['data']['link'],
                'message' => 'Flutterwave payment session created.'
            ];
        }

        return [
            'success' => false,
            'reference' => $params['reference'],
            'checkout_url' => '',
            'message' => $res['message'] ?? 'Unable to initialize Flutterwave payment.'
        ];
    }

    public function verify(string $reference): array
    {
        if (empty($this->secretKey) || str_starts_with($this->secretKey, 'flw_placeholder')) {
            return [
                'verified' => true,
                'amount' => 0.00,
                'currency' => 'NGN',
                'gateway_reference' => 'flw_mock_' . $reference,
                'raw_response' => ['status' => 'success', 'gateway' => 'flutterwave_sandbox_mock']
            ];
        }

        $url = $this->baseUrl . '/transactions/verify_by_reference?tx_ref=' . rawurlencode($reference);
        $res = $this->makeRequest('GET', $url);

        if (!empty($res['status']) && $res['status'] === 'success' && isset($res['data']['status']) && $res['data']['status'] === 'successful') {
            return [
                'verified' => true,
                'amount' => (float)$res['data']['amount'],
                'currency' => $res['data']['currency'] ?? 'NGN',
                'gateway_reference' => (string)($res['data']['id'] ?? $reference),
                'raw_response' => $res['data']
            ];
        }

        return [
            'verified' => false,
            'amount' => 0.00,
            'currency' => 'NGN',
            'gateway_reference' => '',
            'raw_response' => $res
        ];
    }

    public function verifyWebhookSignature(string $payloadJson, string $signatureHeader): bool
    {
        if (empty($this->secretHash) || empty($signatureHeader)) {
            return false;
        }

        return hash_equals($this->secretHash, $signatureHeader);
    }

    public function processRefund(string $transactionReference, float $amount, string $reason): array
    {
        if (empty($this->secretKey) || str_starts_with($this->secretKey, 'flw_placeholder')) {
            return [
                'success' => true,
                'refund_reference' => 'rf_flw_' . uniqid(),
                'message' => 'Flutterwave Sandbox refund recorded successfully.'
            ];
        }

        $url = $this->baseUrl . "/transactions/{$transactionReference}/refund";
        $payload = [
            'amount' => $amount,
            'comments' => $reason
        ];

        $res = $this->makeRequest('POST', $url, $payload);
        if (!empty($res['status']) && $res['status'] === 'success') {
            return [
                'success' => true,
                'refund_reference' => (string)($res['data']['id'] ?? uniqid()),
                'message' => 'Flutterwave refund submitted.'
            ];
        }

        return [
            'success' => false,
            'refund_reference' => '',
            'message' => $res['message'] ?? 'Refund failed.'
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
            return ['status' => 'error', 'message' => "cURL error: {$err}"];
        }

        $decoded = json_decode((string)$result, true);
        return is_array($decoded) ? $decoded : ['status' => 'error', 'message' => 'Invalid JSON from gateway'];
    }
}
