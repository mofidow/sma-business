<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Flutterwave;

use Throwable;
use RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Plugins\Payments\Contracts\PaymentResult;
use Plugins\Payments\Contracts\PaymentInterface;
use Plugins\Payments\Contracts\PaymentResultInterface;
use Plugins\Payments\Contracts\PaymentContextInterface;

/**
 * Flutterwave Payment Gateway
 *
 * Implements Flutterwave v4 API payment integration.
 *
 * @see https://developer.flutterwave.com/reference
 */
final class FlutterwavePaymentMethod implements PaymentInterface
{
    public function __construct(
        private ?Flutterwave $sdk = null,
        private ?string $clientSecret = null,
        private ?string $clientId = null,
        private ?bool $testMode = null,
    ) {}

    public static function key(): string
    {
        return 'flutterwave';
    }

    public static function displayName(): string
    {
        return 'Flutterwave';
    }

    public static function settingFields(): array
    {
        return [
            'client_id' => [
                'type'   => 'text',
                'label'  => 'Client ID',
                'config' => 'services.flutterwave.client_id',
                'rules'  => 'nullable|required_if:gateway,flutterwave|string',
            ],
            'client_secret' => [
                'type'   => 'text',
                'label'  => 'Client Secret',
                'config' => 'services.flutterwave.client_secret',
                'rules'  => 'nullable|required_if:gateway,flutterwave|string',
            ],
            'encryption_key' => [
                'type'   => 'text',
                'label'  => 'Encryption Key',
                'config' => 'services.flutterwave.encryption_key',
                'rules'  => 'nullable|string',
            ],
            'webhook_secret' => [
                'type'   => 'text',
                'label'  => 'Webhook Secret Hash',
                'config' => 'services.flutterwave.webhook_secret',
                'rules'  => 'nullable|string',
            ],
            'test_mode' => [
                'type'   => 'checkbox',
                'label'  => 'Test Mode (Sandbox)',
                'config' => 'services.flutterwave.test_mode',
                'rules'  => 'nullable|boolean',
            ],
        ];
    }

    public static function gatewayFields(): array
    {
        return [
            'name' => [
                'type'        => 'text',
                'label'       => 'Full Name',
                'placeholder' => 'John Doe',
                'required'    => true,
            ],
            'email' => [
                'type'        => 'email',
                'label'       => 'Email Address',
                'placeholder' => 'john@example.com',
                'required'    => true,
            ],
            'phone' => [
                'type'        => 'text',
                'label'       => 'Phone Number',
                'placeholder' => '+2348012345678',
                'required'    => false,
            ],
        ];
    }

    public static function routes(): ?array
    {
        return [
            'callback' => [
                'method' => 'GET',
                'uri'    => 'flutterwave/callback',
                'action' => FlutterwaveController::class . '@callback',
                'name'   => 'flutterwave.callback',
            ],
            'webhook' => [
                'method' => 'POST',
                'uri'    => 'flutterwave/webhook',
                'action' => FlutterwaveController::class . '@webhook',
                'name'   => 'flutterwave.webhook',
            ],
        ];
    }

    public function purchase(PaymentContextInterface $context): PaymentResultInterface
    {
        $sdk = $this->getSdk();

        try {
            $transactionId = (string) Arr::get($context->metadata(), 'transaction_id')
                ?: $context->sale()->getAttribute('reference')
                    ?: $context->sale()->getKey()
                        ?: Str::ulid();

            $description = trim((string) Arr::get($context->metadata(), 'description', ''));
            $description = $description !== ''
                ? $description
                : 'Payment for sale ' . ($context->sale()->getAttribute('reference') ?? $context->sale()->getKey() ?? 'order');

            // Amount should be in major units (decimal)
            $amount = $context->amount() / 100;

            // Parse customer name
            $fullName = Arr::get($context->form(), 'name', '');
            $nameParts = explode(' ', $fullName, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            // Build customer data
            $customerData = [
                'email' => Arr::get($context->form(), 'email', ''),
                'name'  => [
                    'first' => $firstName,
                    'last'  => $lastName,
                ],
            ];

            $phone = Arr::get($context->form(), 'phone', '');
            if ($phone !== '') {
                $customerData['phone'] = [
                    'country_code' => '',
                    'number'       => $phone,
                ];
            }

            // Build payment data for orchestration endpoint
            $paymentData = [
                'amount'       => $amount,
                'currency'     => strtoupper($context->currency()),
                'reference'    => $transactionId,
                'redirect_url' => route('payment_gateways.flutterwave.callback', [
                    'sale_reference' => $transactionId,
                    'return_url'     => $context->returnUrl(),
                ]),
                'customer' => $customerData,
                'meta'     => [
                    'sale_id'     => (string) $context->sale()->getKey(),
                    'sale_ref'    => (string) $context->sale()->getAttribute('reference'),
                    'description' => $description,
                ],
                'payment_method' => [
                    'card' => new \stdClass, // Empty object to enable card payments
                ],
            ];

            $response = $sdk->initiateCharge($paymentData);

            if (($response['status'] ?? '') !== 'success') {
                return PaymentResult::failure(
                    'Flutterwave charge initiation failed: ' . ($response['message'] ?? 'Unknown error')
                );
            }

            $chargeData = $response['data'] ?? [];
            $chargeId = $chargeData['id'] ?? null;
            $nextAction = $chargeData['next_action'] ?? [];
            $redirectUrl = $nextAction['redirect_url'] ?? null;

            // For inline checkout, return the necessary data
            $payload = [
                'charge_id'       => $chargeId,
                'reference'       => $transactionId,
                'amount'          => $amount,
                'currency'        => strtoupper($context->currency()),
                'client_id'       => $sdk->getClientId(),
                'redirect_url'    => $redirectUrl,
                'customer_email'  => $customerData['email'],
                'customer_name'   => $fullName,
                'customer_phone'  => $phone,
                'payment_options' => 'card,banktransfer,ussd,mobilemoney',
                'customizations'  => [
                    'title'       => config('app.name', 'Store'),
                    'description' => Str::limit($description, 100),
                ],
                'flutterwave_response' => $chargeData,
            ];

            return PaymentResult::success(
                'Flutterwave charge initiated. Redirect to checkout.',
                $payload,
                $chargeId ?? $transactionId,
                $redirectUrl
            );
        } catch (Throwable $exception) {
            return PaymentResult::failure('Flutterwave payment initiation failed: ' . $exception->getMessage());
        }
    }

    public function refund(PaymentContextInterface $context, string $providerReference, int $amount): PaymentResultInterface
    {
        if ($providerReference === '') {
            return PaymentResult::failure('Flutterwave requires a charge ID to process refunds.');
        }

        if ($amount <= 0) {
            return PaymentResult::failure('Refund amount must be greater than zero.');
        }

        $sdk = $this->getSdk();

        try {
            $refundAmount = $amount / 100; // Convert from minor to major units

            $response = $sdk->createRefund($providerReference, $refundAmount, 'requested_by_customer');

            if (($response['status'] ?? '') !== 'success') {
                return PaymentResult::failure(
                    'Flutterwave refund failed: ' . ($response['message'] ?? 'Unknown error')
                );
            }

            $refundData = $response['data'] ?? [];

            return PaymentResult::success(
                'Flutterwave refund initiated successfully.',
                [
                    'refund_id'       => $refundData['id'] ?? null,
                    'amount_refunded' => $refundData['amount_refunded'] ?? $refundAmount,
                    'status'          => $refundData['status'] ?? null,
                    'charge_id'       => $refundData['charge_id'] ?? $providerReference,
                ],
                $refundData['id'] ?? $providerReference
            );
        } catch (Throwable $exception) {
            return PaymentResult::failure('Flutterwave refund failed: ' . $exception->getMessage());
        }
    }

    public function supportsPartialRefunds(): bool
    {
        return true;
    }

    /**
     * Get the Flutterwave SDK instance
     */
    private function getSdk(): Flutterwave
    {
        if ($this->sdk !== null) {
            return $this->sdk;
        }

        $clientSecret = $this->clientSecret();
        $clientId = $this->clientId();

        if ($clientSecret === '' || $clientId === '') {
            throw new RuntimeException('Flutterwave Client ID and Client Secret must be configured.');
        }

        return $this->sdk = new Flutterwave($clientSecret, $clientId, ! $this->testMode());
    }

    private function clientSecret(): string
    {
        return $this->clientSecret ??= (string) config('services.flutterwave.client_secret');
    }

    private function clientId(): string
    {
        return $this->clientId ??= (string) config('services.flutterwave.client_id');
    }

    private function testMode(): bool
    {
        return $this->testMode ??= (bool) config('services.flutterwave.test_mode', true);
    }
}
