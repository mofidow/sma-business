<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Flutterwave;

use RuntimeException;

/**
 * Flutterwave v4 API SDK
 *
 * @see https://developer.flutterwave.com/reference
 */
class Flutterwave
{
    private const SANDBOX_BASE_URL = 'https://developersandbox-api.flutterwave.com';

    private const PRODUCTION_BASE_URL = 'https://api.flutterwave.com/v3';

    private string $clientSecret;

    private string $clientId;

    private bool $isProduction;

    private string $baseUrl;

    public function __construct(string $clientSecret, string $clientId, bool $isProduction = false)
    {
        $this->clientSecret = $clientSecret;
        $this->clientId = $clientId;
        $this->isProduction = $isProduction;
        $this->baseUrl = $isProduction ? self::PRODUCTION_BASE_URL : self::SANDBOX_BASE_URL;
    }

    /**
     * Get the client ID for client-side integration
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Check if using production environment
     */
    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    /**
     * Create a customer
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createCustomer(array $data): array
    {
        return $this->request('POST', '/customers', $data);
    }

    /**
     * Get customer by email
     *
     * @return array<string, mixed>|null
     */
    public function getCustomerByEmail(string $email): ?array
    {
        try {
            $response = $this->request('GET', '/customers/search', ['email' => $email]);

            return $response['data'][0] ?? null;
        } catch (RuntimeException) {
            return null;
        }
    }

    /**
     * Initialize a payment using the orchestration endpoint
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function initiateCharge(array $data): array
    {
        return $this->request('POST', '/orchestration/direct-charges', $data);
    }

    /**
     * Create a standard charge
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createCharge(array $data): array
    {
        return $this->request('POST', '/charges', $data);
    }

    /**
     * Retrieve a charge by ID
     *
     * @return array<string, mixed>
     */
    public function getCharge(string $chargeId): array
    {
        return $this->request('GET', "/charges/{$chargeId}");
    }

    /**
     * List charges with optional filters
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listCharges(array $filters = []): array
    {
        return $this->request('GET', '/charges', $filters);
    }

    /**
     * Get charge by reference
     *
     * @return array<string, mixed>|null
     */
    public function getChargeByReference(string $reference): ?array
    {
        $response = $this->listCharges(['reference' => $reference]);

        return $response['data'][0] ?? null;
    }

    /**
     * Create a refund
     *
     * @return array<string, mixed>
     */
    public function createRefund(string $chargeId, float $amount, string $reason = 'requested_by_customer'): array
    {
        return $this->request('POST', '/refunds', [
            'charge_id' => $chargeId,
            'amount'    => $amount,
            'reason'    => $reason,
        ]);
    }

    /**
     * Retrieve a refund by ID
     *
     * @return array<string, mixed>
     */
    public function getRefund(string $refundId): array
    {
        return $this->request('GET', "/refunds/{$refundId}");
    }

    /**
     * List refunds
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listRefunds(array $filters = []): array
    {
        return $this->request('GET', '/refunds', $filters);
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $webhookSecret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify a transaction by ID
     *
     * @return array<string, mixed>
     */
    public function verifyTransaction(int $transactionId): array
    {
        return $this->request('GET', "/transactions/{$transactionId}/verify");
    }

    /**
     * Get a transaction by ID
     *
     * @return array<string, mixed>
     */
    public function getTransaction(int $transactionId): array
    {
        return $this->request('GET', "/transactions/{$transactionId}");
    }

    /**
     * Check if charge status is successful
     */
    public static function isChargeSuccessful(string $status): bool
    {
        return in_array(strtolower($status), ['succeeded', 'successful', 'success'], true);
    }

    /**
     * Make an API request
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;

        if ($method === 'GET' && ! empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $ch = curl_init();

        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL');
        }

        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
            'Accept: application/json',
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
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
        } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error !== '') {
            throw new RuntimeException('Flutterwave API request failed: ' . $error);
        }

        if (! is_string($response) || $response === '') {
            throw new RuntimeException('Flutterwave API returned empty response');
        }

        $decoded = json_decode($response, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Flutterwave API returned invalid JSON');
        }

        if ($httpCode >= 400) {
            $message = $decoded['message'] ?? 'Unknown error';

            throw new RuntimeException('Flutterwave API error: ' . $message);
        }

        return $decoded;
    }
}
