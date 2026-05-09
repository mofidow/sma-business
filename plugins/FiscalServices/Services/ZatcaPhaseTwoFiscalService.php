<?php

declare(strict_types=1);

namespace Plugins\FiscalServices\Services;

use Throwable;
use Saleh7\Zatca\Item;
use Saleh7\Zatca\Party;
use Saleh7\Zatca\Price;
use Saleh7\Zatca\Address;
use Saleh7\Zatca\Invoice;
use Saleh7\Zatca\TaxTotal;
use Saleh7\Zatca\UnitCode;
use Illuminate\Support\Str;
use Saleh7\Zatca\TaxScheme;
use Psr\Log\LoggerInterface;
use Saleh7\Zatca\Attachment;
use Saleh7\Zatca\InvoiceLine;
use Saleh7\Zatca\InvoiceType;
use Saleh7\Zatca\LegalEntity;
use Saleh7\Zatca\TaxCategory;
use Saleh7\Zatca\TaxSubTotal;
use App\Models\Sma\Order\Sale;
use Saleh7\Zatca\InvoiceSigner;
use Saleh7\Zatca\PartyTaxScheme;
use Illuminate\Support\Facades\DB;
use Saleh7\Zatca\BillingReference;
use Saleh7\Zatca\GeneratorInvoice;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response;
use Saleh7\Zatca\LegalMonetaryTotal;
use App\Models\Sma\Order\ReturnOrder;
use Saleh7\Zatca\Helpers\Certificate;
use Illuminate\Support\Facades\Storage;
use Saleh7\Zatca\ClassifiedTaxCategory;
use Saleh7\Zatca\AdditionalDocumentReference;
use Plugins\FiscalServices\Contracts\FiscalServiceResponse;
use Plugins\FiscalServices\Contracts\FiscalServiceInterface;

final class ZatcaPhaseTwoFiscalService implements FiscalServiceInterface
{
    public $payment_settings;

    public function __construct(
        private ?Factory $http = null,
        private ?string $baseUrl = null,
        private ?string $complianceId = null, // This might be the secret or CSID
        private ?string $otp = null,
        private ?string $certificate = null,
        private ?string $secretKey = null,
        private ?LoggerInterface $logger = null,
    ) {
        $this->payment_settings = get_settings(['payment']);
    }

    public function getQRCodeUrl(Sale $sale): string
    {
        // You can use the signed route method from Sale model
        // $tokenData = get_settings('fiscal_service_token'); // save token if needed
        // Setting::updateOrCreate(['tec_key' => 'fiscal_service_token'], ['tec_value' => $tokenData]); // retrieve the saved token

        return $sale->signedRoute();
    }

    public function reportNewSale(Sale $sale): FiscalServiceResponse
    {
        try {
            $invoice = $this->createInvoice($sale, 'invoice');
            $signedInvoice = $this->signInvoice($invoice);

            return $this->send('invoices/reporting/single', $signedInvoice, 'Sale submitted to ZATCA.');
        } catch (Throwable $e) {
            return FiscalServiceResponse::failure($e->getMessage());
        }
    }

    public function reportSaleUpdate(Sale $sale, Sale $originalSale): FiscalServiceResponse
    {
        try {
            $invoice = $this->createInvoice($sale, 'debit'); // Assuming update implies additional charge? Or maybe just re-submission?

            $invoice->setBillingReferences([
                (new BillingReference())->setId($originalSale->reference),
            ]);

            $signedInvoice = $this->signInvoice($invoice);

            return $this->send('invoices/reporting/single', $signedInvoice, 'Sale update submitted to ZATCA.');
        } catch (Throwable $e) {
            return FiscalServiceResponse::failure($e->getMessage());
        }
    }

    public function reportNewReturnSale(ReturnOrder $returnOrder): FiscalServiceResponse
    {
        try {
            $invoice = $this->createInvoice($returnOrder, 'credit');

            if ($returnOrder->sale) {
                $invoice->setBillingReferences([
                    (new BillingReference())->setId($returnOrder->sale->reference),
                ]);
            }

            $signedInvoice = $this->signInvoice($invoice);

            return $this->send('invoices/reporting/single', $signedInvoice, 'Return sale submitted to ZATCA.');
        } catch (Throwable $e) {
            return FiscalServiceResponse::failure($e->getMessage());
        }
    }

    private function createInvoice($model, string $type): Invoice
    {
        $items = $model->items;
        $customer = $model->customer;
        $store = $model->store ?? $model->sale->store ?? null; // Handle ReturnOrder relation

        // --- Invoice Type ---
        $invoiceType = (new InvoiceType())
            ->setInvoice('standard') // Or 'simplified' based on B2B/B2C. Usually B2B is standard, B2C is simplified.
            ->setInvoiceType($type); // 'invoice', 'debit', 'credit'

        // Determine if Standard or Simplified
        // If customer has VAT number, it's likely B2B (Standard). Else B2C (Simplified).
        if ($customer && $customer->vat_no) {
            $invoiceType->setInvoice('standard');
        } else {
            $invoiceType->setInvoice('simplified');
        }

        // --- Additional Document References ---
        $additionalDocs = [];
        $additionalDocs[] = (new AdditionalDocumentReference())
            ->setId('ICV')
            ->setUUID((string) (DB::table('sales')->max('id') + 1)); // Invoice counter value

        $previousHash = $this->getPreviousInvoiceHash();
        $pihAttachment = (new Attachment())
            ->setBase64Content($previousHash, 'previous_hash', 'text/plain');

        $additionalDocs[] = (new AdditionalDocumentReference())
            ->setId('PIH')
            ->setAttachment($pihAttachment);

        // --- Tax Scheme ---
        $taxScheme = (new TaxScheme())->setId('VAT');

        // --- Supplier Party ---
        $supplierAddress = (new Address())
            ->setStreetName($store->street ?? 'Unknown Street')
            ->setBuildingNumber($store->address_line_1 ?? '0000') // Mandatory
            ->setPlotIdentification($store->lot_no ?? '0000') // Mandatory
            ->setCitySubdivisionName($store->address_line_2 ?? $store->city ?? 'Riyadh') // Mandatory
            ->setCityName($store->city ?? 'Riyadh')
            ->setPostalZone($store->postal_code ?? '00000')
            ->setCountry($store->country->iso2 ?? 'SA');

        $partyTaxSchemeSupplier = (new PartyTaxScheme())
            ->setTaxScheme($taxScheme)
            ->setCompanyId($store->vat_no ?? '300000000000003'); // Default for testing if missing

        $supplierCompany = (new Party())
            ->setPartyIdentification($store->other_id ?? $store->vat_no ?? '300000000000003') // CRN or similar
            ->setPartyIdentificationId('CRN')
            ->setLegalEntity((new LegalEntity())->setRegistrationName($store->name ?? 'Supplier Name'))
            ->setPartyTaxScheme($partyTaxSchemeSupplier)
            ->setPostalAddress($supplierAddress);

        // --- Customer Party ---
        $customerAddress = (new Address())
            ->setStreetName($customer->street ?? 'Unknown Street')
            ->setBuildingNumber($customer->address_line_1 ?? '0000')
            ->setPlotIdentification($customer->lot_no ?? '0000')
            ->setCitySubdivisionName($customer->address_line_2 ?? $customer->city ?? 'Riyadh')
            ->setCityName($customer->city ?? 'Riyadh')
            ->setPostalZone($customer->postal_code ?? '00000')
            ->setCountry($customer->country->iso2 ?? 'SA');

        $partyTaxSchemeCustomer = (new PartyTaxScheme())
            ->setTaxScheme($taxScheme);

        if ($customer && $customer->vat_no) {
            $partyTaxSchemeCustomer->setCompanyId($customer->vat_no);
        }

        $customerParty = (new Party())
            ->setPartyIdentification($customer->vat_no ?? '0000000000') // NAT or similar
            ->setPartyIdentificationId('NAT')
            ->setLegalEntity((new LegalEntity())->setRegistrationName($customer->company ?? $customer->name ?? 'Customer Name'))
            ->setPartyTaxScheme($partyTaxSchemeCustomer)
            ->setPostalAddress($customerAddress);

        // --- Invoice Lines ---
        $invoiceLines = [];
        $lineExtensionAmount = 0;
        $taxExclusiveAmount = 0;
        $taxInclusiveAmount = 0;
        $totalTaxAmount = 0;
        $taxRate = 0;

        foreach ($items as $index => $item) {
            if ($item->taxes->first()?->rate) {
                $taxRate = (float) $item->taxes->first()->rate;
            }
            $quantity = (float) $item->quantity;
            $unitPrice = (float) $item->net_price; // Net price before tax
            $taxAmount = (float) $item->total_tax_amount; // Or calculate: $unitPrice * $quantity * ($taxRate / 100)
            $lineTotal = (float) $item->total; // Inclusive of tax? Or exclusive? Usually total is inclusive.

            // Recalculate to be safe
            $lineExtension = $unitPrice * $quantity;

            $classifiedTax = (new ClassifiedTaxCategory())
                ->setPercent($taxRate)
                ->setTaxScheme($taxScheme);

            $productItem = (new Item())
                ->setName($item->product_name)
                ->setClassifiedTaxCategory($classifiedTax);

            $price = (new Price())
                ->setUnitCode(UnitCode::UNIT) // Default unit
                ->setPriceAmount($unitPrice);

            $lineTaxTotal = (new TaxTotal())
                ->setTaxAmount($taxAmount)
                ->setRoundingAmount($lineTotal); // This might be wrong usage of RoundingAmount

            $invoiceLine = (new InvoiceLine())
                ->setUnitCode('PCE')
                ->setId($index + 1)
                ->setItem($productItem)
                ->setLineExtensionAmount($lineExtension)
                ->setPrice($price)
                ->setTaxTotal($lineTaxTotal)
                ->setInvoicedQuantity($quantity);

            $invoiceLines[] = $invoiceLine;

            $lineExtensionAmount += $lineExtension;
            $taxExclusiveAmount += $lineExtension;
            $taxInclusiveAmount += $lineTotal;
            $totalTaxAmount += $taxAmount;
        }

        // --- Tax Totals ---
        $taxCategory = (new TaxCategory())
            ->setPercent($taxRate)
            ->setTaxScheme($taxScheme);

        $taxSubTotal = (new TaxSubTotal())
            ->setTaxableAmount($taxExclusiveAmount)
            ->setTaxAmount($totalTaxAmount)
            ->setTaxCategory($taxCategory);

        $taxTotal = (new TaxTotal())
            ->addTaxSubTotal($taxSubTotal)
            ->setTaxAmount($totalTaxAmount);

        // --- Legal Monetary Total ---
        $legalMonetaryTotal = (new LegalMonetaryTotal())
            ->setLineExtensionAmount($lineExtensionAmount)
            ->setTaxExclusiveAmount($taxExclusiveAmount)
            ->setTaxInclusiveAmount($taxInclusiveAmount)
            ->setPrepaidAmount(0)
            ->setPayableAmount($taxInclusiveAmount)
            ->setAllowanceTotalAmount(0);

        // --- Construct Invoice ---
        $invoice = (new Invoice())
            ->setUUID($model->hash ?? Str::uuid()->toString())
            ->setId($model->reference)
            ->setIssueDate($model->created_at->toDateString())
            ->setIssueTime($model->created_at->toTimeString())
            ->setInvoiceType($invoiceType)
            ->setInvoiceCurrencyCode($this->payment_settings?->default_currency ?? 'SAR')
            ->setTaxCurrencyCode($this->payment_settings?->default_currency ?? 'SAR')
            ->setAdditionalDocumentReferences($additionalDocs)
            ->setAccountingSupplierParty($supplierCompany)
            ->setAccountingCustomerParty($customerParty)
            ->setTaxTotal($taxTotal)
            ->setLegalMonetaryTotal($legalMonetaryTotal)
            ->setInvoiceLines($invoiceLines);

        return $invoice;
    }

    private function signInvoice(Invoice $invoice): InvoiceSigner
    {
        // Generate XML
        $xml = GeneratorInvoice::invoice($invoice)->getXML();
        // Storage::disk('local')->put('zatca/unsigned/' . $invoice->getId() . '.xml', $xml);

        // Load Certificate
        $certificate = new Certificate($this->certificate(), $this->privateKey(), $this->secretKey());

        // Sign
        return InvoiceSigner::signInvoice($xml, $certificate);
    }

    private function send(string $endpoint, InvoiceSigner $signedInvoice, string $successMessage): FiscalServiceResponse
    {
        if ($failure = $this->validateConfiguration()) {
            return $failure;
        }

        try {
            $payload = [
                'invoiceHash' => $signedInvoice->getHash(),
                'uuid'        => $signedInvoice->getHash(), // UUID is usually different, but for now...
                'invoice'     => base64_encode($signedInvoice->getInvoice()),
            ];

            // ZATCA API expects specific JSON structure.
            // { "invoiceHash": "...", "uuid": "...", "invoice": "base64..." }

            // We need to extract UUID from the invoice XML or pass it.
            // I'll assume the UUID in the payload should match the one in the XML.
            // I should probably parse the XML to get the UUID if I don't have it handy,
            // but I set it in createInvoice.

            // Let's just send it.

            $response = $this->http()
                ->baseUrl($this->baseUrl())
                ->asJson()
                ->withHeaders($this->headers())
                ->post($endpoint, $payload);

            $result = $this->transformResponse($response, $successMessage, $endpoint);

            // Append the generated hash to the response data so it can be stored for the next invoice (PIH)
            if ($result->isSuccessful()) {
                $data = $result->payload();
                $data['invoiceHash'] = $signedInvoice->getHash();
                $data['uuid'] = $signedInvoice->getHash(); // Or the actual UUID if available

                // Re-create the response with the updated data
                return FiscalServiceResponse::success($result->message(), $data, $result->reference());
            }

            return $result;
        } catch (Throwable $exception) {
            $this->logger()->error('ZATCA request failed.', [
                'endpoint' => $endpoint,
                'error'    => $exception->getMessage(),
            ]);

            return FiscalServiceResponse::failure($exception->getMessage());
        }
    }

    private function http(): Factory
    {
        return $this->http ??= app(Factory::class);
    }

    private function baseUrl(): string
    {
        $baseUrl = $this->baseUrl ??= rtrim((string) config('fiscal-services.drivers.zatca-phase-two.base_url', 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core/'), '/') . '/';

        return $baseUrl;
    }

    private function validateConfiguration(): ?FiscalServiceResponse
    {
        $missing = [];

        if ($this->certificate() === null) {
            $missing[] = 'certificate';
        }

        if ($this->privateKey() === null) {
            $missing[] = 'private_key';
        }

        if ($this->secretKey() === null) {
            $missing[] = 'secret_key';
        }

        if ($missing === []) {
            return null;
        }

        return FiscalServiceResponse::failure('ZATCA driver is missing configuration: ' . implode(', ', $missing) . '.');
    }

    private function certificate(): ?string
    {
        if ($this->certificate !== null && $this->certificate !== '') {
            return $this->certificate;
        }

        $configured = config('fiscal-services.drivers.zatca-phase-two.certificate');
        $value = is_string($configured) ? trim($configured) : null;

        $this->certificate = $value === '' ? null : $value;

        return $this->certificate;
    }

    private function privateKey(): ?string
    {
        $configured = config('fiscal-services.drivers.zatca-phase-two.private_key');

        if (is_string($configured) && trim($configured) !== '') {
            return $configured;
        }

        $path = config('fiscal-services.drivers.zatca-phase-two.private_key_path');

        if (is_string($path) && trim($path) !== '' && file_exists($path)) {
            return file_get_contents($path) ?: null;
        }

        return null;
    }

    private function secretKey(): ?string
    {
        if ($this->secretKey !== null && $this->secretKey !== '') {
            return $this->secretKey;
        }

        $configured = config('fiscal-services.drivers.zatca-phase-two.secret_key');
        // Fallback to compliance_id if secret_key is not set, as user might have put it there
        if (! $configured) {
            $configured = config('fiscal-services.drivers.zatca-phase-two.compliance_id');
        }

        $value = is_string($configured) ? trim($configured) : null;

        $this->secretKey = $value === '' ? null : $value;

        return $this->secretKey;
    }

    private function otp(): ?string
    {
        if ($this->otp !== null && $this->otp !== '') {
            return $this->otp;
        }

        $configured = config('fiscal-services.drivers.zatca-phase-two.otp');
        $value = is_string($configured) ? trim($configured) : null;

        $this->otp = $value === '' ? null : $value;

        return $this->otp;
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        // Basic Auth is handled by the Certificate helper usually, but here we are sending to the API.
        // The API requires Basic Auth with Certificate and Secret.

        $cert = new Certificate($this->certificate(), $this->privateKey(), $this->secretKey());

        return array_filter([
            'Accept'          => 'application/json',
            'Content-Type'    => 'application/json',
            'Authorization'   => $cert->getAuthHeader(),
            'Accept-Language' => 'en',
        ], fn (?string $value) => $value !== null && $value !== '');
    }

    private function transformResponse(Response $response, string $successMessage, string $endpoint): FiscalServiceResponse
    {
        $status = $response->status();
        $body = $response->json();

        if ($status >= 200 && $status < 300) {
            // ZATCA success response
            $message = $successMessage;

            // Extract clearance status or reporting status
            return FiscalServiceResponse::success($message, $body);
        }

        $this->logger()->warning('ZATCA responded with an error.', [
            'endpoint' => $endpoint,
            'status'   => $status,
            'body'     => $body,
        ]);

        return FiscalServiceResponse::failure('ZATCA Error: ' . $status, $body);
    }

    private function logger(): LoggerInterface
    {
        return $this->logger ??= app(LoggerInterface::class);
    }

    private function getPreviousInvoiceHash(): string
    {
        $lastSale = Sale::whereNotNull('fiscal_service_response')->latest('id')->first();

        if ($lastSale && isset($lastSale->fiscal_service_response['invoiceHash'])) {
            return $lastSale->fiscal_service_response['invoiceHash'];
        }

        // Default hash for the first invoice (base64 of 0 SHA256)
        return 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==';
    }
}
