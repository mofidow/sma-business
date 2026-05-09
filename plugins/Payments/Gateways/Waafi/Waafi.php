<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Waafi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * WaafiPay HTTP client wrapper.
 * API: POST https://api.waafipay.net/asm
 * Response code "2001" = success.
 */
final class Waafi
{
    private const ENDPOINT = 'https://api.waafipay.net/asm';
    private const TIMEOUT  = 120; // seconds — user must approve on phone

    public function __construct(
        private readonly string $merchantUid,
        private readonly string $apiUserId,
        private readonly string $apiKey,
    ) {}

    /**
     * Initiate a mobile-wallet purchase (push notification sent to customer's phone).
     *
     * @param string $phone      Customer WAAFI phone (+252XXXXXXXXX)
     * @param float  $amount     Amount in major currency unit (e.g. 25.00 USD)
     * @param string $currency   ISO 4217 currency code (e.g. "USD")
     * @param string $referenceId Unique transaction reference (e.g. "SALE-42")
     * @param string $invoiceId   Invoice ID shown in WAAFI app
     * @param string $description Description shown in WAAFI app
     */
    public function purchase(
        string $phone,
        float  $amount,
        string $currency,
        string $referenceId,
        string $invoiceId,
        string $description,
    ): array {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        $payload = [
            'schemaVersion' => '1.0',
            'requestId'     => 'REQ-' . Str::ulid(),
            'timestamp'     => now()->toISOString(),
            'channelName'   => 'WEB',
            'serviceName'   => 'API_PURCHASE',
            'serviceParams' => [
                'merchantUid'   => $this->merchantUid,
                'apiUserId'     => $this->apiUserId,
                'apiKey'        => $this->apiKey,
                'paymentMethod' => 'MWALLET_ACCOUNT',
                'payerInfo'     => ['accountNo' => $phone],
                'transactionInfo' => [
                    'referenceId' => $referenceId,
                    'invoiceId'   => $invoiceId,
                    'amount'      => number_format($amount, 2, '.', ''),
                    'currency'    => strtoupper($currency),
                    'description' => $description,
                ],
            ],
        ];

        $response = Http::timeout(self::TIMEOUT)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post(self::ENDPOINT, $payload);

        return $response->json() ?? [];
    }

    public function isSuccess(array $response): bool
    {
        $code = $response['responseCode']
            ?? $response['params']['responseCode']
            ?? null;

        return $code === '2001';
    }

    public function message(array $response): string
    {
        return (string) ($response['responseMsg']
            ?? $response['params']['responseMsg']
            ?? 'Payment failed');
    }

    public function reference(array $response): string
    {
        return (string) ($response['params']['referenceId']
            ?? $response['params']['issuerTransactionId']
            ?? $response['params']['transactionId']
            ?? '');
    }
}
