<?php

namespace App\Core\Payments;

interface PaymentGatewayInterface
{
    /**
     * Initialize a payment session.
     *
     * @param array $params [reference, amount, currency, email, callback_url, metadata]
     * @return array [success => bool, reference => string, checkout_url => string, message => string]
     */
    public function initialize(array $params): array;

    /**
     * Verify payment status server-side.
     *
     * @param string $reference
     * @return array [verified => bool, amount => float, currency => string, gateway_reference => string, raw_response => array]
     */
    public function verify(string $reference): array;

    /**
     * Verify signed incoming webhook payload.
     *
     * @param string $payloadJson
     * @param string $signatureHeader
     * @return bool
     */
    public function verifyWebhookSignature(string $payloadJson, string $signatureHeader): bool;

    /**
     * Process a refund.
     *
     * @param string $transactionReference
     * @param float $amount
     * @param string $reason
     * @return array [success => bool, refund_reference => string, message => string]
     */
    public function processRefund(string $transactionReference, float $amount, string $reason): array;
}
