<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways;

use Plugins\Payments\Contracts\PaymentResult;
use Plugins\Payments\Contracts\PaymentInterface;
use Plugins\Payments\Contracts\PaymentResultInterface;
use Plugins\Payments\Contracts\PaymentContextInterface;

final class ExamplePaymentMethod implements PaymentInterface
{
    public function __construct(
        private readonly ?string $checkoutBaseUrl = 'https://payments.example.test'
    ) {}

    public static function key(): string
    {
        return 'example';
    }

    public static function displayName(): string
    {
        return 'Example Payment Gateway';
    }

    public static function settingFields(): array
    {
        return [
            'public_key' => [
                'type'   => 'text',
                'label'  => 'Public Key',
                'config' => 'services.example.public_key',
                'rules'  => 'nullable|required_if:gateway,example',
            ],
            'secret_key' => [
                'type'   => 'text',
                'label'  => 'Secret Key',
                'config' => 'services.example.secret_key',
                'rules'  => 'nullable|required_if:gateway,example',
            ],
        ];
    }

    public static function gatewayFields(): array
    {
        return [
            // 'name' => [
            //     'type'        => 'text',
            //     'label'       => 'Full Name',
            //     'placeholder' => 'John Doe',
            //     'required'    => true,
            // ],
            // 'card_number' => [
            //     'type'        => 'text',
            //     'label'       => 'Card Number',
            //     'placeholder' => '1234 5678 9012 3456',
            //     'required'    => true,
            // ],
            // 'expiry_date' => [
            //     'type'        => 'text',
            //     'label'       => 'Expiry Date',
            //     'placeholder' => 'MM/YY',
            //     'required'    => true,
            // ],
            // 'cvv' => [
            //     'type'        => 'password',
            //     'label'       => 'CVV',
            //     'placeholder' => '123',
            //     'required'    => true,
            // ],
        ];
    }

    public static function routes(): ?array
    {
        return null;
    }

    public function purchase(PaymentContextInterface $context): PaymentResultInterface
    {
        $reference = 'PAY-' . $context->sale()->getKey();
        $redirectUrl = rtrim($this->checkoutBaseUrl, '/') . '/checkout/' . $reference;

        $payload = [
            'reference'      => $reference,
            'amount'         => $context->amount(),
            'currency'       => $context->currency(),
            'sale_id'        => $context->sale()->getKey(),
            'return_url'     => $context->returnUrl(),
            'notify_url'     => $context->notifyUrl(),
            'metadata'       => $context->metadata(),
            'gateway_fields' => $context->form(),
        ];

        return PaymentResult::success('Payment intent created.', $payload, $reference, $redirectUrl);
    }

    public function refund(PaymentContextInterface $context, string $providerReference, int $amount): PaymentResultInterface
    {
        if ($amount <= 0) {
            return PaymentResult::failure('Refund amount must be greater than zero.');
        }

        $payload = [
            'reference' => $providerReference,
            'amount'    => $amount,
            'sale_id'   => $context->sale()->getKey(),
        ];

        return PaymentResult::success('Refund processed.', $payload, $providerReference);
    }

    public function supportsPartialRefunds(): bool
    {
        return true;
    }
}
