<?php

declare(strict_types=1);

namespace Plugins\FiscalServices\Services\Concerns\CostaRica;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Hacienda API Client for Costa Rica Electronic Invoicing.
 *
 * Handles authentication and communication with the Costa Rica
 * Ministry of Finance (Hacienda) API.
 */
class HaciendaApiClient
{
    private ?string $accessToken = null;

    private const AUTH_URL_PRODUCTION = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token';

    private const AUTH_URL_SANDBOX = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token';

    private const API_URL_PRODUCTION = 'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion';

    private const API_URL_SANDBOX = 'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion';

    public function __construct(
        private string $username,
        private string $password,
        private bool $isProduction = false,
    ) {}

    /**
     * Set a pre-authenticated access token.
     */
    public function setAccessToken(string $token): void
    {
        $this->accessToken = $token;
    }

    /**
     * Authenticate with Hacienda and obtain an access token.
     *
     * @return array{success: bool, access_token?: string, expires_in?: int, error?: string, status?: int}
     */
    public function authenticate(): array
    {
        $authUrl = $this->isProduction ? self::AUTH_URL_PRODUCTION : self::AUTH_URL_SANDBOX;
        $clientId = $this->isProduction ? 'api-prod' : 'api-stag';

        /** @var Response $response */
        $response = Http::asForm()->post($authUrl, [
            'client_id'     => $clientId,
            'client_secret' => '',
            'grant_type'    => 'password',
            'username'      => $this->username,
            'password'      => $this->password,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->accessToken = $data['access_token'] ?? null;

            return [
                'success'      => true,
                'access_token' => $this->accessToken,
                'expires_in'   => $data['expires_in'] ?? 3600,
            ];
        }

        return [
            'success' => false,
            'error'   => $response->body(),
            'status'  => $response->status(),
        ];
    }

    /**
     * Submit a signed document to Hacienda.
     *
     * @return array{success: bool, status?: int, body?: string, error?: string}
     */
    public function submitDocument(
        string $clave,
        string $signedXml,
        string $issuerType,
        string $issuerNumber,
    ): array {
        if (! $this->accessToken) {
            $authResult = $this->authenticate();

            if (! $authResult['success']) {
                return $authResult;
            }
        }

        $apiUrl = $this->isProduction ? self::API_URL_PRODUCTION : self::API_URL_SANDBOX;

        $payload = [
            'clave'  => $clave,
            'fecha'  => now()->toIso8601String(),
            'emisor' => [
                'tipoIdentificacion'   => str_pad($issuerType, 2, '0', STR_PAD_LEFT),
                'numeroIdentificacion' => str_pad(
                    preg_replace('/[^0-9]/', '', $issuerNumber),
                    12,
                    '0',
                    STR_PAD_LEFT,
                ),
            ],
            'comprobanteXml' => base64_encode($signedXml),
        ];

        /** @var Response $response */
        $response = Http::withToken($this->accessToken)
            ->asJson()
            ->post($apiUrl, $payload);

        if ($response->successful()) {
            return [
                'success' => true,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ];
        }

        return [
            'success' => false,
            'error'   => $response->body(),
            'status'  => $response->status(),
        ];
    }

    /**
     * Check the status of a submitted document.
     *
     * @return array<string, mixed>|null
     */
    public function checkStatus(string $clave): ?array
    {
        if (! $this->accessToken) {
            $authResult = $this->authenticate();

            if (! $authResult['success']) {
                return null;
            }
        }

        $apiUrl = $this->isProduction ? self::API_URL_PRODUCTION : self::API_URL_SANDBOX;
        $url = "{$apiUrl}/{$clave}";

        /** @var Response $response */
        $response = Http::withToken($this->accessToken)->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
