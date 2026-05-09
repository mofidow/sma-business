<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Paystack;

use Throwable;
use RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Plugins\Payments\Contracts\PaymentResult;
use Plugins\Payments\Contracts\PaymentInterface;
use Plugins\Payments\Contracts\PaymentResultInterface;
use Plugins\Payments\Contracts\PaymentContextInterface;

/**
 * Paystack Payment Gateway
 *
 * Implements Paystack API payment integration.
 *
 * @see https://paystack.com/docs/payments/
 */
final class PaystackPaymentMethod implements PaymentInterface
{
    public function __construct(
        private ?Paystack $sdk = null,
        private ?string $secretKey = null,
        private ?string $publicKey = null,
    ) {}

    public static function key(): string
    {
        return 'paystack';
    }

    public static function displayName(): string
    {
        return 'Paystack';
    }

    public static function settingFields(): array
    {
        return [
            'public_key' => [
                'type'   => 'text',
                'label'  => 'Public Key',
                'config' => 'services.paystack.public_key',
                'rules'  => 'nullable|required_if:gateway,paystack|string',
            ],
            'secret_key' => [
                'type'   => 'text',
                'label'  => 'Secret Key',
                'config' => 'services.paystack.secret_key',
                'rules'  => 'nullable|required_if:gateway,paystack|string',
            ],
        ];
    }

    public static function gatewayFields(): array
    {
        return [
            'email' => [
                'type'        => 'email',
                'label'       => 'Email Address',
                'placeholder' => 'customer@example.com',
                'required'    => true,
            ],
            'name' => [
                'type'        => 'text',
                'label'       => 'Full Name',
                'placeholder' => 'John Doe',
                'required'    => false,
            ],
            'phone' => [
                'type'        => 'text',
                'label'       => 'Phone Number',
                'placeholder' => '+234XXXXXXXXXX',
                'required'    => false,
            ],
        ];
    }

    public static function routes(): ?array
    {
        return [
            'callback' => [
                'method' => 'GET',
                'uri'    => 'paystack/callback',
                'action' => PaystackController::class . '@callback',
                'name'   => 'paystack.callback',
            ],
            'webhook' => [
                'method' => 'POST',
                'uri'    => 'paystack/webhook',
                'action' => PaystackController::class . '@webhook',
                'name'   => 'paystack.webhook',
            ],
        ];
    }

    public function purchase(PaymentContextInterface $context): PaymentResultInterface
    {
        $sdk = $this->getSdk();

        try {
            $reference = (string) Arr::get($context->metadata(), 'transaction_id')
                ?: $context->sale()->getAttribute('reference')
                    ?: $context->sale()->getKey()
                        ?: Str::ulid();

            $description = trim((string) Arr::get($context->metadata(), 'description', ''));
            $description = $description !== ''
                ? $description
                : 'Payment for sale ' . ($context->sale()->getAttribute('reference') ?? $context->sale()->getKey() ?? 'order');

            // Amount should be in subunit (kobo for NGN)
            $amount = $context->amount();

            $email = Arr::get($context->form(), 'email', '');

            if ($email === '') {
                return PaymentResult::failure('Email address is required for Paystack payments.');
            }

            // Build transaction data
            $transactionData = [
                'email'        => $email,
                'amount'       => $amount,
                'reference'    => $reference,
                'currency'     => strtoupper($context->currency()),
                'callback_url' => route('payment_gateways.paystack.callback', [
                    'sale_reference' => $reference,
                    'return_url'     => $context->returnUrl(),
                ]),
                'metadata' => [
                    'sale_id'       => (string) $context->sale()->getKey(),
                    'sale_ref'      => (string) $context->sale()->getAttribute('reference'),
                    'description'   => $description,
                    'custom_fields' => [
                        [
                            'display_name'  => 'Sale Reference',
                            'variable_name' => 'sale_reference',
                            'value'         => (string) $context->sale()->getAttribute('reference'),
                        ],
                    ],
                ],
            ];

            // Add optional channels
            $channels = Arr::get($context->metadata(), 'channels');
            if (is_array($channels) && $channels !== []) {
                $transactionData['channels'] = $channels;
            }

            $response = $sdk->initializeTransaction($transactionData);

            if (($response['status'] ?? false) !== true) {
                return PaymentResult::failure(
                    'Paystack transaction initialization failed: ' . ($response['message'] ?? 'Unknown error')
                );
            }

            $data = $response['data'] ?? [];
            $authorizationUrl = $data['authorization_url'] ?? null;
            $accessCode = $data['access_code'] ?? null;
            $paystackReference = $data['reference'] ?? $reference;

            $payload = [
                'reference'         => $paystackReference,
                'access_code'       => $accessCode,
                'authorization_url' => $authorizationUrl,
                'amount'            => $amount,
                'currency'          => strtoupper($context->currency()),
                'public_key'        => $sdk->getPublicKey(),
                'email'             => $email,
                'paystack_response' => $data,
            ];

            return PaymentResult::success(
                'Paystack transaction initialized. Redirect to checkout.',
                $payload,
                $paystackReference,
                $authorizationUrl
            );
        } catch (Throwable $exception) {
            return PaymentResult::failure('Paystack payment initialization failed: ' . $exception->getMessage());
        }
    }

    public function refund(PaymentContextInterface $context, string $providerReference, int $amount): PaymentResultInterface
    {
        if ($providerReference === '') {
            return PaymentResult::failure('Paystack requires a transaction reference to process refunds.');
        }

        if ($amount <= 0) {
            return PaymentResult::failure('Refund amount must be greater than zero.');
        }

        $sdk = $this->getSdk();

        try {
            $response = $sdk->createRefund(
                transaction: $providerReference,
                amount: $amount,
                merchantNote: 'Refund for sale ' . ($context->sale()->getAttribute('reference') ?? $context->sale()->getKey())
            );

            if (($response['status'] ?? false) !== true) {
                return PaymentResult::failure(
                    'Paystack refund failed: ' . ($response['message'] ?? 'Unknown error')
                );
            }

            $refundData = $response['data'] ?? [];

            return PaymentResult::success(
                'Paystack refund initiated successfully.',
                [
                    'refund_id'   => $refundData['id'] ?? null,
                    'amount'      => $refundData['amount'] ?? $amount,
                    'status'      => $refundData['status'] ?? null,
                    'currency'    => $refundData['currency'] ?? null,
                    'transaction' => $refundData['transaction'] ?? $providerReference,
                ],
                (string) ($refundData['id'] ?? $providerReference)
            );
        } catch (Throwable $exception) {
            return PaymentResult::failure('Paystack refund failed: ' . $exception->getMessage());
        }
    }

    public function supportsPartialRefunds(): bool
    {
        return true;
    }

    /**
     * Get the Paystack SDK instance
     */
    private function getSdk(): Paystack
    {
        if ($this->sdk !== null) {
            return $this->sdk;
        }

        $secretKey = $this->secretKey();
        $publicKey = $this->publicKey();

        if ($secretKey === '' || $publicKey === '') {
            throw new RuntimeException('Paystack Secret Key and Public Key must be configured.');
        }

        return $this->sdk = new Paystack($secretKey, $publicKey);
    }

    private function secretKey(): string
    {
        return $this->secretKey ??= (string) config('services.paystack.secret_key');
    }

    private function publicKey(): string
    {
        return $this->publicKey ??= (string) config('services.paystack.public_key');
    }
}
