<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Waafi;

use Throwable;
use RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Plugins\Payments\Contracts\PaymentResult;
use Plugins\Payments\Contracts\PaymentInterface;
use Plugins\Payments\Contracts\PaymentResultInterface;
use Plugins\Payments\Contracts\PaymentContextInterface;

/**
 * WAAFI Pay payment gateway.
 *
 * Synchronous push-to-phone: a USSD/push notification is sent to the customer's
 * WAAFI wallet and the API waits up to 120 s for approval.
 * No webhook or redirect required.
 */
final class WaafiPaymentMethod implements PaymentInterface
{
    public static function key(): string
    {
        return 'waafi';
    }

    public static function displayName(): string
    {
        return 'WAAFI Pay';
    }

    public static function settingFields(): array
    {
        return [
            'merchant_uid' => [
                'type'   => 'text',
                'label'  => 'Merchant UID',
                'config' => 'services.waafi.merchant_uid',
                'rules'  => 'nullable|string',
            ],
            'api_user_id' => [
                'type'   => 'text',
                'label'  => 'API User ID',
                'config' => 'services.waafi.api_user_id',
                'rules'  => 'nullable|string',
            ],
            'api_key' => [
                'type'   => 'password',
                'label'  => 'API Key',
                'config' => 'services.waafi.api_key',
                'rules'  => 'nullable|string',
            ],
            'enabled' => [
                'type'   => 'select',
                'label'  => 'Enable WAAFI',
                'config' => 'services.waafi.enabled',
                'rules'  => 'nullable|in:true,false',
                'options' => [
                    ['value' => 'true',  'label' => 'Live (Production)'],
                    ['value' => 'false', 'label' => 'Disabled / Demo'],
                ],
            ],
        ];
    }

    public static function gatewayFields(): array
    {
        return [
            'phone_number' => [
                'type'        => 'tel',
                'label'       => 'WAAFI Phone Number',
                'placeholder' => '+252XXXXXXXXX',
                'required'    => true,
                'pattern'     => '^\+252\d{8,9}$',
                'hint'        => 'Enter your Somali WAAFI number starting with +252',
            ],
        ];
    }

    /**
     * No webhook/redirect routes needed — this gateway is synchronous.
     */
    public static function routes(): ?array
    {
        return null;
    }

    public function purchase(PaymentContextInterface $context): PaymentResultInterface
    {
        $merchantUid = $this->decrypt(config('services.waafi.merchant_uid', ''));
        $apiUserId   = $this->decrypt(config('services.waafi.api_user_id', ''));
        $apiKey      = $this->decrypt(config('services.waafi.api_key', ''));
        $enabled     = config('services.waafi.enabled', 'true');

        // Demo mode when credentials are missing or disabled
        if (! $merchantUid || ! $apiUserId || ! $apiKey || $enabled === 'false') {
            return PaymentResult::success(
                'WAAFI demo mode — no real charge made.',
                ['demo' => true],
                'DEMO-' . Str::ulid()
            );
        }

        $phone = (string) Arr::get($context->form(), 'phone_number', '');
        if (! $phone) {
            return PaymentResult::failure('WAAFI phone number is required.');
        }

        // Validate Somali format (+252XXXXXXXX)
        if (! preg_match('/^\+?252\d{8,9}$/', preg_replace('/[^0-9+]/', '', $phone))) {
            return PaymentResult::failure(WaafiErrorHandler::humanize('invalid phone'));
        }

        $sale        = $context->sale();
        $referenceId = 'SALE-' . ($sale->getAttribute('reference') ?? $sale->getKey());
        $invoiceId   = 'INV-' . $sale->getKey();
        $description = 'Lacag bixinta - ' . $referenceId;
        // Amount in major currency unit (WaafiPay accepts decimal string like "25.00")
        $amount      = $context->amount() / 100; // context stores in minor units

        try {
            $waafi    = new Waafi($merchantUid, $apiUserId, $apiKey);
            $response = $waafi->purchase(
                phone:       $phone,
                amount:      $amount,
                currency:    $context->currency(),
                referenceId: $referenceId,
                invoiceId:   $invoiceId,
                description: $description,
            );

            if ($waafi->isSuccess($response)) {
                return PaymentResult::success(
                    'WAAFI payment successful.',
                    ['waafi_response' => $response],
                    $waafi->reference($response)
                );
            }

            $rawMessage = $waafi->message($response);

            return PaymentResult::failure(
                WaafiErrorHandler::humanize($rawMessage),
                ['waafi_response' => $response]
            );
        } catch (Throwable $e) {
            $message = str_contains(strtolower($e->getMessage()), 'timeout')
                ? WaafiErrorHandler::humanize('timeout')
                : 'WAAFI gateway error: ' . $e->getMessage();

            return PaymentResult::failure($message);
        }
    }

    public function refund(PaymentContextInterface $context, string $providerReference, int $amount): PaymentResultInterface
    {
        return PaymentResult::failure('WAAFI Pay does not support automated refunds. Please process manually through your WAAFI merchant portal.');
    }

    public function supportsPartialRefunds(): bool
    {
        return false;
    }

    /**
     * Safely decrypt a credential — falls back to plain text for legacy values.
     */
    private function decrypt(string $value): string
    {
        if (! $value) {
            return '';
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value; // already plaintext
        }
    }
}
