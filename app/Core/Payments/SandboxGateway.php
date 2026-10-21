<?php

namespace App\Core\Payments;

class SandboxGateway implements PaymentGatewayInterface
{
    protected string $sandboxSecret = 'sandbox_secret_signature_test_hash';

    public function initialize(array $params): array
    {
        $ref = $params['reference'];
        return [
            'success' => true,
            'reference' => $ref,
            'checkout_url' => url("checkout/verify/test_sandbox?reference=" . urlencode($ref) . "&status=successful"),
            'message' => 'Sandbox test gateway initialized successfully.'
        ];
    }

    public function verify(string $reference): array
    {
        // Deterministic sandbox verification
        $isSuccessful = !str_contains(strtolower($reference), 'fail');
        return [
            'verified' => $isSuccessful,
            'amount' => 35000.00,
            'currency' => 'NGN',
            'gateway_reference' => 'SANDBOX-' . $reference,
            'raw_response' => [
                'status' => $isSuccessful ? 'success' : 'failed',
                'gateway' => 'test_sandbox',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    public function verifyWebhookSignature(string $payloadJson, string $signatureHeader): bool
    {
        $expected = hash_hmac('sha256', $payloadJson, $this->sandboxSecret);
        return hash_equals($expected, $signatureHeader);
    }

    public function processRefund(string $transactionReference, float $amount, string $reason): array
    {
        return [
            'success' => true,
            'refund_reference' => 'REFUND-SANDBOX-' . uniqid(),
            'message' => 'Sandbox test refund processed successfully.'
        ];
    }
}
