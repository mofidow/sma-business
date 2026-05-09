<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Paystack;

use RuntimeException;

/**
 * Paystack API SDK
 *
 * @see https://paystack.com/docs/api/
 */
class Paystack
{
    private const BASE_URL = 'https://api.paystack.co';

    private string $secretKey;

    private string $publicKey;

    public function __construct(string $secretKey, string $publicKey)
    {
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
    }

    /**
     * Get the public key for client-side integration
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Initialize a transaction
     *
     * @param  array<string, mixed>  $data  Must contain 'email' and 'amount' (in subunit)
     * @return array<string, mixed>
     */
    public function initializeTransaction(array $data): array
    {
        return $this->request('POST', '/transaction/initialize', $data);
    }

    /**
     * Verify a transaction by reference
     *
     * @return array<string, mixed>
     */
    public function verifyTransaction(string $reference): array
    {
        return $this->request('GET', '/transaction/verify/' . urlencode($reference));
    }

    /**
     * Fetch a transaction by ID
     *
     * @return array<string, mixed>
     */
    public function fetchTransaction(int $transactionId): array
    {
        return $this->request('GET', '/transaction/' . $transactionId);
    }

    /**
     * List transactions
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listTransactions(array $filters = []): array
    {
        return $this->request('GET', '/transaction', $filters);
    }

    /**
     * Charge an authorization (for recurring payments)
     *
     * @param  array<string, mixed>  $data  Must contain 'email', 'amount', and 'authorization_code'
     * @return array<string, mixed>
     */
    public function chargeAuthorization(array $data): array
    {
        return $this->request('POST', '/transaction/charge_authorization', $data);
    }

    /**
     * Create a refund
     *
     * @param  string  $transaction  Transaction reference or ID
     * @param  int|null  $amount  Amount in subunit (optional, defaults to full amount)
     * @param  string|null  $customerNote  Customer-facing reason
     * @param  string|null  $merchantNote  Internal merchant reason
     * @return array<string, mixed>
     */
    public function createRefund(
        string $transaction,
        ?int $amount = null,
        ?string $customerNote = null,
        ?string $merchantNote = null
    ): array {
        $data = ['transaction' => $transaction];

        if ($amount !== null) {
            $data['amount'] = $amount;
        }

        if ($customerNote !== null) {
            $data['customer_note'] = $customerNote;
        }

        if ($merchantNote !== null) {
            $data['merchant_note'] = $merchantNote;
        }

        return $this->request('POST', '/refund', $data);
    }

    /**
     * Fetch a refund by ID
     *
     * @return array<string, mixed>
     */
    public function fetchRefund(int $refundId): array
    {
        return $this->request('GET', '/refund/' . $refundId);
    }

    /**
     * List refunds
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listRefunds(array $filters = []): array
    {
        return $this->request('GET', '/refund', $filters);
    }

    /**
     * Verify webhook signature using HMAC SHA512
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha512', $payload, $this->secretKey);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Check if transaction status indicates success
     */
    public static function isTransactionSuccessful(string $status): bool
    {
        return strtolower($status) === 'success';
    }

    /**
     * Make an API request
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = self::BASE_URL . $endpoint;

        if ($method === 'GET' && $data !== []) {
            $url .= '?' . http_build_query($data);
        }

        $ch = curl_init();

        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL');
        }

        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
            'Cache-Control: no-cache',
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
        } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data !== []) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error !== '') {
            throw new RuntimeException('Paystack API request failed: ' . $error);
        }

        if (! is_string($response) || $response === '') {
            throw new RuntimeException('Paystack API returned empty response');
        }

        $decoded = json_decode($response, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Paystack API returned invalid JSON');
        }

        if ($httpCode >= 400) {
            $message = $decoded['message'] ?? 'Unknown error';

            throw new RuntimeException('Paystack API error: ' . $message);
        }

        return $decoded;
    }
}
