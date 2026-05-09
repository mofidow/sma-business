<?php

declare(strict_types=1);

namespace Plugins\FiscalServices\Services;

use Throwable;
use App\Models\Setting;
use Psr\Log\LoggerInterface;
use App\Models\Sma\Order\Sale;
use Klsheng\Myinvois\Ubl\Item;
use Klsheng\Myinvois\Ubl\Party;
use Klsheng\Myinvois\Ubl\Price;
use Klsheng\Myinvois\Ubl\Address;
use Klsheng\Myinvois\Ubl\Contact;
use Klsheng\Myinvois\Ubl\Country;
use Klsheng\Myinvois\Ubl\Invoice;
use Klsheng\Myinvois\Ubl\TaxTotal;
use Klsheng\Myinvois\Ubl\TaxScheme;
use Illuminate\Http\Client\Response;
use Klsheng\Myinvois\MyInvoisClient;
use Klsheng\Myinvois\Ubl\CreditNote;
use App\Models\Sma\Order\ReturnOrder;
use Klsheng\Myinvois\Ubl\InvoiceLine;
use Klsheng\Myinvois\Ubl\LegalEntity;
use Klsheng\Myinvois\Ubl\TaxCategory;
use Klsheng\Myinvois\Ubl\TaxSubTotal;
use Klsheng\Myinvois\Ubl\CreditNoteLine;
use Klsheng\Myinvois\Ubl\PartyTaxScheme;
use Klsheng\Myinvois\Ubl\AccountingParty;
use Klsheng\Myinvois\Ubl\ItemPriceExtension;
use Klsheng\Myinvois\Ubl\LegalMonetaryTotal;
use Klsheng\Myinvois\Ubl\Constant\TaxCategoryCodes;
use Plugins\FiscalServices\Contracts\FiscalServiceResponse;
use Plugins\FiscalServices\Contracts\FiscalServiceInterface;

final class MalaysiaEinvoiceFiscalService implements FiscalServiceInterface
{
    public $payment_settings;

    private ?MyInvoisClient $client = null;

    public function __construct(
        private ?string $clientId = null,
        private ?string $clientSecret = null,
        private ?LoggerInterface $logger = null,
    ) {
        $this->payment_settings = get_settings(['payment']);
    }

    public function getQRCodeUrl(Sale $sale): string
    {
        try {
            $client = $this->getClient();

            if ($sale->fiscal_service_response['document'] ?? null) {
                $response = $client->getDocumentDetail($sale->id);
                if (! isset($response['error'])) {
                    $sale->fiscal_service_response['document'] = $response;
                    $sale->save();

                    return $client->generateDocumentQrCodeUrl($sale->id, $response['longId']);
                }
            } elseif ($longId = $sale->fiscal_service_response['document']['longId']) {
                return $client->generateDocumentQrCodeUrl($sale->id, $longId);
            }

            // if ($sale->fiscal_service_response['payload']['submissionUID'] ?? null) {
            //     $submission = $client->getSubmission($sale->fiscal_service_response['payload']['submissionUID']);
            // }

            return $sale->signedRoute();
        } catch (Throwable $e) {
            logger()->error('Failed to get Malaysia MyInvois QR code URL.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function reportNewSale(Sale $sale): FiscalServiceResponse
    {
        try {
            $invoice = $this->createInvoice($sale);

            return $this->submitDocument([$invoice], 'Invoice submitted to MyInvois.');
        } catch (Throwable $e) {
            return FiscalServiceResponse::failure($e->getMessage());
        }
    }

    public function reportSaleUpdate(Sale $sale, Sale $originalSale): FiscalServiceResponse
    {
        // MyInvois typically handles updates via Credit Notes or Debit Notes, or cancelling and re-issuing.

        try {
            $creditNote = $this->createCreditNote($sale);

            $this->submitDocument([$creditNote], 'Credit note submitted to MyInvois.');

            $invoice = $this->createInvoice($sale);

            return $this->submitDocument([$invoice], 'Invoice amendment submitted to MyInvois.');
        } catch (Throwable $e) {
            return FiscalServiceResponse::failure($e->getMessage());
        }
    }

    public function reportNewReturnSale(ReturnOrder $returnOrder): FiscalServiceResponse
    {
        try {
            $creditNote = $this->createCreditNote($returnOrder);

            return $this->submitDocument([$creditNote], 'Credit note submitted to MyInvois.');
        } catch (Throwable $e) {
            return FiscalServiceResponse::failure($e->getMessage());
        }
    }

    private function submitDocument(array $documents, string $successMessage): FiscalServiceResponse
    {
        if ($failure = $this->validateConfiguration()) {
            return $failure;
        }

        try {
            $client = $this->getClient();

            // Submit document
            $response = $client->submitDocument($documents);

            // The SDK returns the response body as an array
            if (isset($response['submissionUID'])) {
                return FiscalServiceResponse::success($successMessage, $response, $response['submissionUID']);
            }

            if (isset($response['error'])) {
                return FiscalServiceResponse::failure($response['error']['error'] ?? $response['error']['errorMS'] ?? 'Unknown error', $response);
            }

            return FiscalServiceResponse::success($successMessage, $response);
        } catch (Throwable $exception) {
            $this->logger()->error('Malaysia MyInvois request failed.', [
                'error' => $exception->getMessage(),
            ]);

            return FiscalServiceResponse::failure($exception->getMessage());
        }
    }

    private function getClient(): MyInvoisClient
    {
        if ($this->client) {
            return $this->client;
        }

        $isProduction = config('fiscal-services.drivers.malaysia-einvoice.mode', 'sandbox') === 'production';

        $this->client = new MyInvoisClient($this->clientId(), $this->clientSecret(), $isProduction);

        $expiresAt = null;
        $tokenData = get_settings('fiscal_service_token');
        if ($tokenData && isset($tokenData['expires_at'])) {
            $expiresAt = now()->parse($tokenData['expires_at']);
        }

        if ($expiresAt && $expiresAt->isFuture() && isset($tokenData['access_token'])) {
            $this->client->setAccessToken($tokenData['access_token']);
        } else {
            $response = $this->client->login();
            $response['expires_at'] = now()->addSeconds(($response['expires_in'] ?? 3600) - 100)->toDateTimeString();
            Setting::updateOrCreate(['tec_key' => 'fiscal_service_token'], ['tec_value' => $response]);
        }

        return $this->client;
    }

    private function createInvoice(Sale $sale): Invoice
    {
        $invoice = new Invoice();
        $invoice->setId($sale->id);
        $invoice->setIssueDateTime($sale->created_at);
        $invoice->setDocumentCurrencyCode($this->payment_settings?->default_currency ?? 'MYR');
        $invoice->setTaxCurrencyCode($this->payment_settings?->default_currency ?? 'MYR');

        // Supplier
        $invoice->setAccountingSupplierParty($this->createSupplierParty($sale->store));

        // Customer
        $invoice->setAccountingCustomerParty($this->createCustomerParty($sale->customer));

        // Lines
        $lines = [];
        foreach ($sale->items as $item) {
            $lines[] = $this->createInvoiceLine($item);
        }
        $invoice->setInvoiceLines($lines);

        // Totals
        $invoice->setTaxTotal($this->createTaxTotal($sale->tax, $this->payment_settings?->default_currency ?? 'MYR'));
        $invoice->setLegalMonetaryTotal($this->createLegalMonetaryTotal($sale));

        return $invoice;
    }

    private function createCreditNote(Sale|ReturnOrder $returnOrder): CreditNote
    {
        $creditNote = new CreditNote();
        $creditNote->setId($returnOrder->id);
        $creditNote->setIssueDateTime($returnOrder->created_at);
        $creditNote->setDocumentCurrencyCode($this->payment_settings?->default_currency ?? 'MYR');
        $creditNote->setTaxCurrencyCode($this->payment_settings?->default_currency ?? 'MYR');

        // Supplier
        $creditNote->setAccountingSupplierParty($this->createSupplierParty($returnOrder->store));

        // Customer
        $creditNote->setAccountingCustomerParty($this->createCustomerParty($returnOrder->customer));

        // Lines
        $lines = [];
        foreach ($returnOrder->items as $item) {
            $lines[] = $this->createCreditNoteLine($item);
        }
        $creditNote->setCreditNoteLines($lines);

        // Totals
        $creditNote->setTaxTotal($this->createTaxTotal($returnOrder->total_tax_amount, $this->payment_settings?->default_currency ?? 'MYR'));
        $creditNote->setLegalMonetaryTotal($this->createLegalMonetaryTotal($returnOrder));

        return $creditNote;
    }

    private function createSupplierParty($store): AccountingParty
    {
        $party = new Party();
        $party->setName($store->name);

        // Address
        $address = new Address();
        $address->setCityName($store->city ?? 'City');
        $address->setPostalZone($store->postal_code ?? '00000');
        $address->setCountry((new Country())->setIdentificationCode($store->country->iso3 ?? 'MYS'));
        // Add lines if available
        // $address->setAddressLines([...]);
        $party->setPostalAddress($address);

        // Legal Entity
        $legalEntity = new LegalEntity();
        $legalEntity->setRegistrationName($store->name);
        $party->setLegalEntity($legalEntity);

        // Contact
        $contact = new Contact();
        $contact->setTelephone($store->phone);
        $contact->setElectronicMail($store->email);
        $party->setContact($contact);

        // Industry Classification (Placeholder - needs to be configured)
        $party->setIndustryClassificationCode('008', 'General'); // Placeholder: https://sdk.myinvois.hasil.gov.my/codes/classification-codes/

        // Tax Scheme
        $partyTaxScheme = new PartyTaxScheme();
        $partyTaxScheme->setCompanyID($store->vat_no ?? '00000000000');
        $partyTaxScheme->setTaxScheme((new TaxScheme())->setID('VAT'));
        $party->setPartyTaxScheme($partyTaxScheme);

        $accountingParty = new AccountingParty();
        $accountingParty->setParty($party);

        return $accountingParty;
    }

    private function createCustomerParty($customer): AccountingParty
    {
        $party = new Party();
        $party->setName($customer->name ?? 'Walk-in Customer');

        // Address
        $address = new Address();
        $address->setCityName($customer->city ?? 'City');
        $address->setPostalZone($customer->postal_code ?? '00000');
        $address->setCountry((new Country())->setIdentificationCode($customer->country->iso3 ?? 'MYS'));
        $party->setPostalAddress($address);

        // Legal Entity
        $legalEntity = new LegalEntity();
        $legalEntity->setRegistrationName($customer->company ?? $customer->name ?? 'Walk-in Customer');
        $party->setLegalEntity($legalEntity);

        // Contact
        $contact = new Contact();
        $contact->setTelephone($customer->phone);
        $contact->setElectronicMail($customer->email);
        $party->setContact($contact);

        // Tax Scheme
        $partyTaxScheme = new PartyTaxScheme();
        $partyTaxScheme->setCompanyID($customer->vat_no ?? '00000000000');
        $partyTaxScheme->setTaxScheme((new TaxScheme())->setID('VAT'));
        $party->setPartyTaxScheme($partyTaxScheme);

        $accountingParty = new AccountingParty();
        $accountingParty->setParty($party);

        return $accountingParty;
    }

    private function createInvoiceLine($item): InvoiceLine
    {
        $line = new InvoiceLine();
        $line->setID((string) $item->id);
        $line->setInvoicedQuantity($item->quantity);
        $line->setLineExtensionAmount($item->subtotal);

        // Item
        $ublItem = new Item();
        $ublItem->setDescription($item->product_name);
        $ublItem->setName($item->product_name);

        // dummy classification for now.
        $classification = new \Klsheng\Myinvois\Ubl\CommodityClassification();
        $classification->setItemClassificationCode('008'); // Placeholder
        $ublItem->addCommodityClassification($classification);

        $line->setItem($ublItem);

        // Price
        $price = new Price();
        $price->setPriceAmount($item->net_price);
        $line->setPrice($price);

        // Tax Total for Line
        $lineTaxTotal = new TaxTotal();
        $lineTaxTotal->setTaxAmount($item->total_tax_amount ?? 0);

        $subTotal = new TaxSubTotal();
        $subTotal->setTaxableAmount($item->subtotal);
        $subTotal->setTaxAmount($item->total_tax_amount ?? 0);

        $taxCategory = new TaxCategory();
        $taxCategory->setID(TaxCategoryCodes::STANDARD_RATE);
        $taxCategory->setPercent($item->taxes->first()?->rate || 0); // Placeholder logic
        $taxCategory->setTaxScheme((new TaxScheme())->setID('VAT'));
        $subTotal->setTaxCategory($taxCategory);

        $lineTaxTotal->addTaxSubTotal($subTotal);
        $line->setTaxTotal($lineTaxTotal);

        // Item Price Extension
        $itemPriceExtension = new ItemPriceExtension();
        $itemPriceExtension->setAmount($item->net_price);
        $line->setItemPriceExtension($itemPriceExtension);

        return $line;
    }

    private function createCreditNoteLine($item): CreditNoteLine
    {
        $line = new CreditNoteLine();
        $line->setID((string) $item->id);
        $line->setCreditedQuantity($item->quantity);
        $line->setLineExtensionAmount($item->subtotal);

        // Item
        $ublItem = new Item();
        $ublItem->setDescription($item->product_name);
        $ublItem->setName($item->product_name);

        $classification = new \Klsheng\Myinvois\Ubl\CommodityClassification();
        $classification->setItemClassificationCode('008'); // Placeholder: https://sdk.myinvois.hasil.gov.my/codes/classification-codes/
        $ublItem->addCommodityClassification($classification);

        $line->setItem($ublItem);

        // Price
        $price = new Price();
        $price->setPriceAmount($item->net_price);
        $line->setPrice($price);

        // Tax Total for Line
        $lineTaxTotal = new TaxTotal();
        $lineTaxTotal->setTaxAmount($item->total_tax_amount ?? 0);

        $subTotal = new TaxSubTotal();
        $subTotal->setTaxableAmount($item->subtotal);
        $subTotal->setTaxAmount($item->total_tax_amount ?? 0);

        $taxCategory = new TaxCategory();
        $taxCategory->setID(TaxCategoryCodes::STANDARD_RATE);
        $taxCategory->setPercent($item->taxes->first()?->rate || 0); // Placeholder logic
        $taxCategory->setTaxScheme((new TaxScheme())->setID('VAT'));
        $subTotal->setTaxCategory($taxCategory);

        $lineTaxTotal->addTaxSubTotal($subTotal);
        $line->setTaxTotal($lineTaxTotal);

        // Item Price Extension
        $itemPriceExtension = new ItemPriceExtension();
        $itemPriceExtension->setAmount($item->net_price);
        $line->setItemPriceExtension($itemPriceExtension);

        return $line;
    }

    private function createTaxTotal($taxAmount, $currency): TaxTotal
    {
        $taxTotal = new TaxTotal();
        $taxTotal->setTaxAmount($taxAmount);

        // Subtotal (Simplified - assuming one tax category)
        $subTotal = new TaxSubTotal();
        $subTotal->setTaxableAmount(0); // Needs calculation
        $subTotal->setTaxAmount($taxAmount);

        $taxCategory = new TaxCategory();
        $taxCategory->setID(TaxCategoryCodes::STANDARD_RATE);
        $taxCategory->setTaxScheme((new TaxScheme())->setID('VAT'));
        $subTotal->setTaxCategory($taxCategory);

        $taxTotal->addTaxSubTotal($subTotal);

        return $taxTotal;
    }

    private function createLegalMonetaryTotal($sale): LegalMonetaryTotal
    {
        $total = new LegalMonetaryTotal();
        $total->setLineExtensionAmount($sale->total);
        $total->setTaxExclusiveAmount($sale->total);
        $total->setTaxInclusiveAmount($sale->grand_total);
        $total->setPayableAmount($sale->grand_total);

        return $total;
    }

    private function validateConfiguration(): ?FiscalServiceResponse
    {
        $missing = [];

        if ($this->clientId() === null) {
            $missing[] = 'client_id';
        }

        if ($this->clientSecret() === null) {
            $missing[] = 'client_secret';
        }

        if ($missing === []) {
            return null;
        }

        return FiscalServiceResponse::failure(
            'Malaysia MyInvois driver is missing credentials: ' . implode(', ', $missing) . '.',
        );
    }

    private function clientId(): ?string
    {
        if ($this->clientId !== null && $this->clientId !== '') {
            return $this->clientId;
        }

        $configured = config('fiscal-services.drivers.malaysia-einvoice.client_id');
        $value = is_string($configured) ? trim($configured) : null;

        $this->clientId = $value === '' ? null : $value;

        return $this->clientId;
    }

    private function clientSecret(): ?string
    {
        if ($this->clientSecret !== null && $this->clientSecret !== '') {
            return $this->clientSecret;
        }

        $configured = config('fiscal-services.drivers.malaysia-einvoice.client_secret');
        $value = is_string($configured) ? trim($configured) : null;

        $this->clientSecret = $value === '' ? null : $value;

        return $this->clientSecret;
    }

    private function logger(): LoggerInterface
    {
        return $this->logger ??= app(LoggerInterface::class);
    }
}
