<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Razorpay;

use Throwable;
use Razorpay\Api\Api;
use RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Plugins\Payments\Contracts\PaymentResult;
use Plugins\Payments\Contracts\PaymentInterface;
use Plugins\Payments\Contracts\PaymentResultInterface;
use Plugins\Payments\Contracts\PaymentContextInterface;

final class RazorpayPaymentMethod implements PaymentInterface
{
    public function __construct(
        private ?Api $api = null,
        private ?string $keyId = null,
        private ?string $keySecret = null,
    ) {}

    public static function key(): string
    {
        return 'razorpay';
    }

    public static function displayName(): string
    {
        return 'Razorpay';
    }

    public static function settingFields(): array
    {
        return [
            'key_id' => [
                'type'   => 'text',
                'label'  => 'Key ID',
                'config' => 'services.razorpay.key_id',
                'rules'  => 'nullable|required_if:gateway,razorpay|string',
            ],
            'key_secret' => [
                'type'   => 'text',
                'label'  => 'Key Secret',
                'config' => 'services.razorpay.key_secret',
                'rules'  => 'nullable|required_if:gateway,razorpay|string',
            ],
            'webhook_secret' => [
                'type'   => 'text',
                'label'  => 'Webhook Secret',
                'config' => 'services.razorpay.webhook_secret',
                'rules'  => 'nullable|string',
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
                'required'    => false,
            ],
            'email' => [
                'type'        => 'email',
                'label'       => 'Email Address',
                'placeholder' => 'john@example.com',
                'required'    => false,
            ],
            'phone' => [
                'type'        => 'text',
                'label'       => 'Phone Number',
                'placeholder' => '+91 9876543210',
                'required'    => false,
            ],
        ];
    }

    public static function routes(): ?array
    {
        return [
            'callback' => [
                'method' => 'POST',
                'uri'    => 'razorpay/callback',
                'action' => RazorpayController::class . '@callback',
                'name'   => 'razorpay.callback',
            ],
            'webhook' => [
                'method' => 'POST',
                'uri'    => 'razorpay/webhook',
                'action' => RazorpayController::class . '@webhook',
                'name'   => 'razorpay.webhook',
            ],
        ];
    }

    public function purchase(PaymentContextInterface $context): PaymentResultInterface
    {
        $api = $this->getApi();

        try {
            $transactionId = (string) Arr::get($context->metadata(), 'transaction_id') ?: $context->sale()->getAttribute('reference') ?: $context->sale()->getKey() ?: Str::ulid();
            $description = trim((string) Arr::get($context->metadata(), 'description', ''));
            $description = $description !== '' ? $description : 'Payment for sale ' . ($context->sale()->getAttribute('reference') ?? $context->sale()->getKey() ?? 'order');

            $orderData = [
                'receipt'         => $transactionId,
                'amount'          => $context->amount(),
                'currency'        => strtoupper($context->currency()),
                'payment_capture' => 1,
                'notes'           => [
                    'sale_id'     => (string) $context->sale()->getKey(),
                    'sale_ref'    => (string) $context->sale()->getAttribute('reference'),
                    'description' => $description,
                ],
            ];

            $customerName = Arr::get($context->form(), 'name');
            $customerEmail = Arr::get($context->form(), 'email');
            $customerPhone = Arr::get($context->form(), 'phone');

            if ($customerName || $customerEmail || $customerPhone) {
                $orderData['customer_details'] = array_filter([
                    'name'    => $customerName,
                    'email'   => $customerEmail,
                    'contact' => $customerPhone,
                ]);
            }

            $order = $api->order->create($orderData);

            $checkoutData = [
                'key'          => $this->keyId(),
                'amount'       => $order->amount,
                'currency'     => $order->currency,
                'name'         => config('app.name', 'Store'),
                'description'  => $description,
                'order_id'     => $order->id,
                'callback_url' => route('payment_gateways.razorpay.callback', [
                    'sale_reference' => $transactionId,
                    'return_url'     => $context->returnUrl(),
                ]),
                'prefill' => array_filter([
                    'name'    => $customerName,
                    'email'   => $customerEmail,
                    'contact' => $customerPhone,
                ]),
                'theme' => [
                    'color' => config('services.razorpay.theme_color', '#3399cc'),
                ],
                'modal' => [
                    'ondismiss' => 'function(){window.location.href="' . $context->returnUrl() . '";}',
                ],
            ];

            $payload = [
                'order_id'      => $order->id,
                'checkout_data' => $checkoutData,
                'razorpay_key'  => $this->keyId(),
            ];

            return PaymentResult::success(
                'Razorpay order created. Redirect to checkout.',
                $payload,
                $order->id,
                null
            );
        } catch (Throwable $exception) {
            return PaymentResult::failure('Razorpay order creation failed: ' . $exception->getMessage());
        }
    }

    public function refund(PaymentContextInterface $context, string $providerReference, int $amount): PaymentResultInterface
    {
        if ($providerReference === '') {
            return PaymentResult::failure('Razorpay requires a payment ID to refund payments.');
        }

        if ($amount <= 0) {
            return PaymentResult::failure('Refund amount must be greater than zero.');
        }

        $api = $this->getApi();

        try {
            $refund = $api->payment->fetch($providerReference)->refund([
                'amount' => $amount,
                'speed'  => 'normal',
                'notes'  => [
                    'sale_id' => (string) $context->sale()->getKey(),
                ],
            ]);

            $refundData = [
                'refund_id' => $refund->id,
                'amount'    => $refund->amount,
                'currency'  => $refund->currency,
                'status'    => $refund->status,
            ];

            return PaymentResult::success(
                'Razorpay refund processed.',
                $refundData,
                $refund->id
            );
        } catch (Throwable $exception) {
            return PaymentResult::failure('Razorpay refund failed: ' . $exception->getMessage());
        }
    }

    public function supportsPartialRefunds(): bool
    {
        return true;
    }

    private function getApi(): Api
    {
        if ($this->api !== null) {
            return $this->api;
        }

        $keyId = $this->keyId();
        $keySecret = $this->keySecret();

        if ($keyId === '' || $keySecret === '') {
            throw new RuntimeException('Razorpay Key ID and Key Secret must be configured.');
        }

        return $this->api = new Api($keyId, $keySecret);
    }

    private function keyId(): string
    {
        return $this->keyId ??= (string) config('services.razorpay.key_id');
    }

    private function keySecret(): string
    {
        return $this->keySecret ??= (string) config('services.razorpay.key_secret');
    }
}
