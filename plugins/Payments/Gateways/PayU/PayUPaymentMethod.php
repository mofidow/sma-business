<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\PayU;

use Throwable;
use RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Plugins\Payments\Contracts\PaymentResult;
use Plugins\Payments\Contracts\PaymentInterface;
use Plugins\Payments\Contracts\PaymentResultInterface;
use Plugins\Payments\Contracts\PaymentContextInterface;

/**
 * PayU India Payment Gateway
 *
 * Implements PayU payment integration for Indian merchants.
 *
 * @see https://github.com/payu-intrepos/web-sdk-php
 * @see https://devguide.payu.in/
 */
final class PayUPaymentMethod implements PaymentInterface
{
    public function __construct(
        private ?PayU $sdk = null,
        private ?string $key = null,
        private ?string $salt = null,
        private ?bool $testMode = null,
    ) {}

    public static function key(): string
    {
        return 'payu';
    }

    public static function displayName(): string
    {
        return 'PayU India';
    }

    public static function settingFields(): array
    {
        return [
            'key' => [
                'type'   => 'text',
                'label'  => 'Merchant Key',
                'config' => 'services.payu.key',
                'rules'  => 'nullable|required_if:gateway,payu|string',
            ],
            'salt' => [
                'type'   => 'text',
                'label'  => 'Merchant Salt',
                'config' => 'services.payu.salt',
                'rules'  => 'nullable|required_if:gateway,payu|string',
            ],
            'test_mode' => [
                'type'   => 'checkbox',
                'label'  => 'Test Mode (Sandbox)',
                'config' => 'services.payu.test_mode',
                'rules'  => 'nullable|boolean',
            ],
        ];
    }

    public static function gatewayFields(): array
    {
        return [
            'firstname' => [
                'type'        => 'text',
                'label'       => 'First Name',
                'placeholder' => 'John',
                'required'    => true,
            ],
            'lastname' => [
                'type'        => 'text',
                'label'       => 'Last Name',
                'placeholder' => 'Doe',
                'required'    => false,
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
                'placeholder' => '9876543210',
                'required'    => true,
            ],
            'address1' => [
                'type'        => 'text',
                'label'       => 'Address',
                'placeholder' => '123 Main Street',
                'required'    => false,
            ],
            'city' => [
                'type'        => 'text',
                'label'       => 'City',
                'placeholder' => 'Mumbai',
                'required'    => false,
            ],
            'state' => [
                'type'        => 'text',
                'label'       => 'State',
                'placeholder' => 'Maharashtra',
                'required'    => false,
            ],
            'zipcode' => [
                'type'        => 'text',
                'label'       => 'Zip Code',
                'placeholder' => '400001',
                'required'    => false,
            ],
            'country' => [
                'type'        => 'text',
                'label'       => 'Country',
                'placeholder' => 'India',
                'required'    => false,
            ],
        ];
    }

    public static function routes(): ?array
    {
        return [
            'success' => [
                'method' => 'POST',
                'uri'    => 'payu/success',
                'action' => PayUController::class . '@success',
                'name'   => 'payu.success',
            ],
            'failure' => [
                'method' => 'POST',
                'uri'    => 'payu/failure',
                'action' => PayUController::class . '@failure',
                'name'   => 'payu.failure',
            ],
            'webhook' => [
                'method' => 'POST',
                'uri'    => 'payu/webhook',
                'action' => PayUController::class . '@webhook',
                'name'   => 'payu.webhook',
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

            // Amount should be in INR (major units with 2 decimal places)
            $amount = number_format($context->amount() / 100, 2, '.', '');

            // Build payment parameters
            $params = [
                'txnid'       => $transactionId,
                'amount'      => $amount,
                'productinfo' => Str::limit($description, 100),
                'firstname'   => Arr::get($context->form(), 'firstname', ''),
                'lastname'    => Arr::get($context->form(), 'lastname', ''),
                'email'       => Arr::get($context->form(), 'email', ''),
                'phone'       => Arr::get($context->form(), 'phone', ''),
                'address1'    => Arr::get($context->form(), 'address1', ''),
                'city'        => Arr::get($context->form(), 'city', ''),
                'state'       => Arr::get($context->form(), 'state', ''),
                'zipcode'     => Arr::get($context->form(), 'zipcode', ''),
                'country'     => Arr::get($context->form(), 'country', 'India'),
                'udf1'        => (string) $context->sale()->getKey(),
                'udf2'        => (string) $context->sale()->getAttribute('reference'),
                'udf3'        => '',
                'udf4'        => '',
                'udf5'        => '',
            ];

            // Generate hash
            $params['hash'] = $sdk->generateHash($params);
            $params['key'] = $sdk->getKey();
            $params['surl'] = route('payment_gateways.payu.success', [
                'return_url' => $context->returnUrl(),
            ]);
            $params['furl'] = route('payment_gateways.payu.failure', [
                'return_url' => $context->returnUrl(),
            ]);

            $payload = [
                'txnid'         => $transactionId,
                'payment_url'   => $sdk->getPaymentUrl(),
                'payment_data'  => $params,
                'payu_key'      => $sdk->getKey(),
                'redirect_form' => $this->buildRedirectForm($sdk->getPaymentUrl(), $params),
            ];

            return PaymentResult::success(
                'PayU payment initiated. Redirect to checkout.',
                $payload,
                $transactionId,
                null
            );
        } catch (Throwable $exception) {
            return PaymentResult::failure('PayU payment initiation failed: ' . $exception->getMessage());
        }
    }

    public function refund(PaymentContextInterface $context, string $providerReference, int $amount): PaymentResultInterface
    {
        if ($providerReference === '') {
            return PaymentResult::failure('PayU requires a PayU ID to process refunds.');
        }

        if ($amount <= 0) {
            return PaymentResult::failure('Refund amount must be greater than zero.');
        }

        $sdk = $this->getSdk();

        try {
            // Get transaction details to find the txnid
            $transaction = $sdk->verifyPaymentByPayuId($providerReference);

            if ($transaction === false) {
                return PaymentResult::failure('Could not find the original transaction for refund.');
            }

            $txnid = $transaction['txnid'] ?? '';
            $refundAmount = $amount / 100; // Convert from minor to major units

            $response = $sdk->initiateRefund($providerReference, $txnid, $refundAmount);

            if ($response === false) {
                return PaymentResult::failure('PayU refund request failed.');
            }

            $status = $response['status'] ?? null;

            if ($status === 1 || $status === '1' || $status === 'success') {
                $refundData = [
                    'request_id'    => $response['request_id'] ?? null,
                    'bank_ref_num'  => $response['bank_ref_num'] ?? null,
                    'amount'        => $refundAmount,
                    'status'        => $response['status'] ?? null,
                    'payu_response' => $response,
                ];

                return PaymentResult::success(
                    'PayU refund initiated successfully.',
                    $refundData,
                    $response['request_id'] ?? $providerReference
                );
            }

            $errorMessage = $response['msg'] ?? $response['message'] ?? 'Unknown error';

            return PaymentResult::failure('PayU refund failed: ' . $errorMessage);
        } catch (Throwable $exception) {
            return PaymentResult::failure('PayU refund failed: ' . $exception->getMessage());
        }
    }

    public function supportsPartialRefunds(): bool
    {
        return true;
    }

    /**
     * Get the PayU SDK instance
     */
    private function getSdk(): PayU
    {
        if ($this->sdk !== null) {
            return $this->sdk;
        }

        $key = $this->merchantKey();
        $salt = $this->merchantSalt();

        if ($key === '' || $salt === '') {
            throw new RuntimeException('PayU Merchant Key and Salt must be configured.');
        }

        return $this->sdk = new PayU($key, $salt, ! $this->testMode());
    }

    private function merchantKey(): string
    {
        return $this->key ??= (string) config('services.payu.key');
    }

    private function merchantSalt(): string
    {
        return $this->salt ??= (string) config('services.payu.salt');
    }

    private function testMode(): bool
    {
        return $this->testMode ??= (bool) config('services.payu.test_mode', true);
    }

    /**
     * Build an auto-submitting redirect form for PayU
     *
     * @param  array<string, mixed>  $params
     */
    private function buildRedirectForm(string $url, array $params): string
    {
        $html = '<form id="payu_payment_form" action="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" method="post">';

        foreach ($params as $name => $value) {
            $html .= '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '" />';
        }

        $html .= '</form>';
        $html .= '<script type="text/javascript">document.getElementById("payu_payment_form").submit();</script>';

        return $html;
    }
}
