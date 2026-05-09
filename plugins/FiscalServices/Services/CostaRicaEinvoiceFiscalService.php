<?php

declare(strict_types=1);

namespace Plugins\FiscalServices\Services;

use Throwable;
use App\Models\Setting;
use Psr\Log\LoggerInterface;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Order\ReturnOrder;
use Plugins\FiscalServices\Contracts\FiscalServiceResponse;
use Plugins\FiscalServices\Contracts\FiscalServiceInterface;
use Plugins\FiscalServices\Services\Concerns\CostaRica\XmlSigner;
use Plugins\FiscalServices\Services\Concerns\CostaRica\HaciendaApiClient;
use Plugins\FiscalServices\Services\Concerns\CostaRica\InvoiceXmlBuilder;

/**
 * Costa Rica Electronic Invoice Fiscal Service.
 *
 * Integrates with the Costa Rica Ministry of Finance (Hacienda) API
 * for electronic invoicing compliance.
 */
final class CostaRicaEinvoiceFiscalService implements FiscalServiceInterface
{
    private ?HaciendaApiClient $client = null;

    public function __construct(
        private ?string $username = null,
        private ?string $password = null,
        private ?string $certificatePath = null,
        private ?string $certificatePin = null,
        private ?LoggerInterface $logger = null,
    ) {}

    public function getQRCodeUrl(Sale $sale): ?string
    {
        // Costa Rica doesn't use QR codes in the same way as other systems.
        // Return the signed route for viewing the invoice.
        return $sale->signedRoute();
    }

    public function reportNewSale(Sale $sale): FiscalServiceResponse
    {
        if ($failure = $this->validateConfiguration()) {
            return $failure;
        }

        try {
            return $this->submitInvoice($sale, '01'); // 01 = Factura Electrónica
        } catch (Throwable $e) {
            $this->logger()->error('Costa Rica e-invoice submission failed.', [
                'error'   => $e->getMessage(),
                'sale_id' => $sale->id,
            ]);

            return FiscalServiceResponse::failure($e->getMessage());
        }
    }

    public function reportSaleUpdate(Sale $sale, Sale $originalSale): FiscalServiceResponse
    {
        if ($failure = $this->validateConfiguration()) {
            return $failure;
        }

        try {
            // In Costa Rica, sale updates typically require issuing a credit note
            // for the original sale and then a new invoice for the updated sale.
            $creditNoteResponse = $this->submitCreditNote($originalSale, $sale);

            if (! $creditNoteResponse->isSuccessful()) {
                return $creditNoteResponse;
            }

            return $this->submitInvoice($sale, '01');
        } catch (Throwable $e) {
            $this->logger()->error('Costa Rica e-invoice update failed.', [
                'error'   => $e->getMessage(),
                'sale_id' => $sale->id,
            ]);

            return FiscalServiceResponse::failure($e->getMessage());
        }
    }

    public function reportNewReturnSale(ReturnOrder $returnOrder): FiscalServiceResponse
    {
        if ($failure = $this->validateConfiguration()) {
            return $failure;
        }

        try {
            return $this->submitCreditNote($returnOrder->sale, $returnOrder);
        } catch (Throwable $e) {
            $this->logger()->error('Costa Rica credit note submission failed.', [
                'error'           => $e->getMessage(),
                'return_order_id' => $returnOrder->id,
            ]);

            return FiscalServiceResponse::failure($e->getMessage());
        }
    }

    /**
     * Submit an invoice to Hacienda.
     */
    private function submitInvoice(Sale $sale, string $documentType): FiscalServiceResponse
    {
        $client = $this->getClient();
        $builder = new InvoiceXmlBuilder($sale, $documentType);
        $signer = new XmlSigner($this->certificatePath(), $this->certificatePin());

        // Build XML
        $xmlData = $builder->build();
        $clave = $xmlData['clave'];

        // Sign XML
        $signedXml = $signer->sign($xmlData['xml']);

        if ($signedXml === false) {
            return FiscalServiceResponse::failure('Failed to sign XML document.');
        }

        // Submit to Hacienda
        $response = $client->submitDocument(
            clave: $clave,
            signedXml: $signedXml,
            issuerType: $sale->store->tax_id_type ?? '01',
            issuerNumber: $sale->store->tax_number,
        );

        if (! $response['success']) {
            return FiscalServiceResponse::failure(
                $response['error'] ?? 'Unknown error from Hacienda API.',
                $response,
            );
        }

        // Wait and check status
        sleep(3);
        $statusResponse = $client->checkStatus($clave);

        if ($statusResponse && ($statusResponse['ind-estado'] ?? '') === 'aceptado') {
            return FiscalServiceResponse::success(
                'Invoice accepted by Hacienda.',
                [
                    'clave'    => $clave,
                    'status'   => $statusResponse,
                    'response' => $response,
                ],
                $clave,
            );
        }

        $estado = $statusResponse['ind-estado'] ?? 'pending';

        if ($estado === 'rechazado') {
            return FiscalServiceResponse::failure(
                'Invoice rejected by Hacienda.',
                [
                    'clave'    => $clave,
                    'status'   => $statusResponse,
                    'response' => $response,
                ],
            );
        }

        // Pending or unknown status
        return FiscalServiceResponse::success(
            "Invoice submitted to Hacienda. Status: {$estado}",
            [
                'clave'    => $clave,
                'status'   => $statusResponse,
                'response' => $response,
            ],
            $clave,
        );
    }

    /**
     * Submit a credit note to Hacienda.
     */
    private function submitCreditNote(Sale $originalSale, Sale|ReturnOrder $reference): FiscalServiceResponse
    {
        $client = $this->getClient();
        $builder = new InvoiceXmlBuilder($originalSale, '03', $reference); // 03 = Nota de Crédito
        $signer = new XmlSigner($this->certificatePath(), $this->certificatePin());

        // Build XML
        $xmlData = $builder->build();
        $clave = $xmlData['clave'];

        // Sign XML
        $signedXml = $signer->sign($xmlData['xml']);

        if ($signedXml === false) {
            return FiscalServiceResponse::failure('Failed to sign credit note XML.');
        }

        // Submit to Hacienda
        $response = $client->submitDocument(
            clave: $clave,
            signedXml: $signedXml,
            issuerType: $originalSale->store->tax_id_type ?? '01',
            issuerNumber: $originalSale->store->tax_number,
        );

        if (! $response['success']) {
            return FiscalServiceResponse::failure(
                $response['error'] ?? 'Unknown error from Hacienda API.',
                $response,
            );
        }

        // Wait and check status
        sleep(3);
        $statusResponse = $client->checkStatus($clave);

        $estado = $statusResponse['ind-estado'] ?? 'pending';

        if ($estado === 'aceptado') {
            return FiscalServiceResponse::success(
                'Credit note accepted by Hacienda.',
                [
                    'clave'    => $clave,
                    'status'   => $statusResponse,
                    'response' => $response,
                ],
                $clave,
            );
        }

        if ($estado === 'rechazado') {
            return FiscalServiceResponse::failure(
                'Credit note rejected by Hacienda.',
                [
                    'clave'    => $clave,
                    'status'   => $statusResponse,
                    'response' => $response,
                ],
            );
        }

        return FiscalServiceResponse::success(
            "Credit note submitted to Hacienda. Status: {$estado}",
            [
                'clave'    => $clave,
                'status'   => $statusResponse,
                'response' => $response,
            ],
            $clave,
        );
    }

    private function getClient(): HaciendaApiClient
    {
        if ($this->client) {
            return $this->client;
        }

        $isProduction = $this->mode() === 'production';

        $this->client = new HaciendaApiClient(
            username: $this->username(),
            password: $this->password(),
            isProduction: $isProduction,
        );

        // Check for cached token
        $tokenData = get_settings('costa_rica_fiscal_token');

        if ($tokenData && isset($tokenData['expires_at'])) {
            $expiresAt = now()->parse($tokenData['expires_at']);

            if ($expiresAt->isFuture() && isset($tokenData['access_token'])) {
                $this->client->setAccessToken($tokenData['access_token']);

                return $this->client;
            }
        }

        // Authenticate and cache token
        $tokenResponse = $this->client->authenticate();

        if ($tokenResponse['success'] ?? false) {
            $tokenResponse['expires_at'] = now()
                ->addSeconds(($tokenResponse['expires_in'] ?? 3600) - 100)
                ->toDateTimeString();

            Setting::updateOrCreate(
                ['tec_key' => 'costa_rica_fiscal_token'],
                ['tec_value' => $tokenResponse],
            );
        }

        return $this->client;
    }

    private function validateConfiguration(): ?FiscalServiceResponse
    {
        $missing = [];

        if (! $this->username()) {
            $missing[] = 'username';
        }

        if (! $this->password()) {
            $missing[] = 'password';
        }

        if (! $this->certificatePath()) {
            $missing[] = 'certificate_path';
        }

        if (! $this->certificatePin()) {
            $missing[] = 'certificate_pin';
        }

        if ($missing === []) {
            return null;
        }

        return FiscalServiceResponse::failure(
            'Costa Rica e-invoice driver is missing credentials: ' . implode(', ', $missing) . '.',
        );
    }

    private function username(): ?string
    {
        return $this->getConfigValue('username', $this->username);
    }

    private function password(): ?string
    {
        return $this->getConfigValue('password', $this->password);
    }

    private function certificatePath(): ?string
    {
        return $this->getConfigValue('certificate_path', $this->certificatePath);
    }

    private function certificatePin(): ?string
    {
        return $this->getConfigValue('certificate_pin', $this->certificatePin);
    }

    private function mode(): string
    {
        return config('fiscal-services.drivers.costa-rica-einvoice.mode', 'sandbox');
    }

    private function getConfigValue(string $key, ?string $instanceValue): ?string
    {
        if ($instanceValue !== null && $instanceValue !== '') {
            return $instanceValue;
        }

        $configured = config("fiscal-services.drivers.costa-rica-einvoice.{$key}");
        $value = is_string($configured) ? trim($configured) : null;

        return $value === '' ? null : $value;
    }

    private function logger(): LoggerInterface
    {
        return $this->logger ??= app(LoggerInterface::class);
    }
}
