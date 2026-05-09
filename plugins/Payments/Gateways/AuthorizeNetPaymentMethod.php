<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways;

use Closure;
use Throwable;
use Omnipay\Omnipay;
use RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use Plugins\Payments\Contracts\PaymentResult;
use Plugins\Payments\Contracts\PaymentInterface;
use Plugins\Payments\Contracts\PaymentResultInterface;
use Plugins\Payments\Contracts\PaymentContextInterface;

final class AuthorizeNetPaymentMethod implements PaymentInterface
{
    public function __construct(
        private ?Closure $gatewayFactory = null,
        private ?string $loginId = null,
        private ?string $transactionKey = null,
        private ?bool $testMode = null,
    ) {}

    public static function key(): string
    {
        return 'authorize-net';
    }

    public static function displayName(): string
    {
        return 'Authorize.Net';
    }

    public static function settingFields(): array
    {
        return [
            'login' => [
                'type'   => 'text',
                'label'  => 'Login ID',
                'config' => 'services.authorize.login',
                'rules'  => 'nullable|required_if:gateway,authorize-net|string',
            ],
            'transaction_key' => [
                'type'   => 'text',
                'label'  => 'Transaction Key',
                'config' => 'services.authorize.transaction_key',
                'rules'  => 'nullable|required_if:gateway,authorize-net|string',
            ],
            'test_mode' => [
                'type'   => 'checkbox',
                'label'  => 'Test Mode',
                'config' => 'services.authorize.test_mode',
                'rules'  => 'nullable|boolean',
            ],
        ];
    }

    public static function gatewayFields(): array
    {
        return [
            'card.number'      => ['type' => 'text', 'label' => 'Card Number', 'required' => true],
            'card.expiryMonth' => ['type' => 'text', 'label' => 'Expiry Month (MM)', 'required' => true],
            'card.expiryYear'  => ['type' => 'text', 'label' => 'Expiry Year (YYYY)', 'required' => true],
            'card.cvv'         => ['type' => 'password', 'label' => 'Security Code (CVV)', 'required' => true],
            'card.firstName'   => ['type' => 'text', 'label' => 'First Name', 'required' => false],
            'card.lastName'    => ['type' => 'text', 'label' => 'Last Name', 'required' => false],
            'card.email'       => ['type' => 'email', 'label' => 'Email Address', 'required' => false],
        ];
    }

    public static function routes(): ?array
    {
        return null;
    }

    public function purchase(PaymentContextInterface $context): PaymentResultInterface
    {
        $gateway = $this->configuredGateway();
        $transactionId = (string) Arr::get($context->metadata(), 'transaction_id') ?: $context->sale()->getAttribute('reference') ?: $context->sale()->getKey() ?: Str::ulid();

        try {
            $card = Arr::get($context->form(), 'card', []);
            $cardDetails = ! is_array($card) ? [] : new CreditCard($card);

            $description = trim((string) Arr::get($context->metadata(), 'description', ''));
            $description = $description !== '' ? $description : 'Payment for sale ' . ($context->sale()->getAttribute('reference') ?? $context->sale()->getKey() ?? 'order');

            $response = $gateway->purchase([
                'amount'        => $this->formatAmount($context->amount()),
                'currency'      => $context->currency(),
                'transactionId' => $transactionId,
                'card'          => $cardDetails,
                'returnUrl'     => $context->returnUrl(),
                'notifyUrl'     => $context->notifyUrl(),
                'description'   => $description,
            ])->send();

            if ($response->isSuccessful()) {
                return $this->buildResultFromResponse($response, $response->getMessage(), $response->getTransactionReference());
            }

            return PaymentResult::failure(
                $response->getMessage() ?: 'Authorize.Net request failed.'
            );
        } catch (Throwable $exception) {
            return PaymentResult::failure($exception->getMessage());
        }
    }

    public function refund(PaymentContextInterface $context, string $providerReference, int $amount): PaymentResultInterface
    {
        if ($providerReference === '') {
            return PaymentResult::failure('Authorize.Net requires a transaction reference to refund payments.');
        }

        if ($amount <= 0) {
            return PaymentResult::failure('Refund amount must be greater than zero.');
        }

        $gateway = $this->configuredGateway();

        try {
            $request = $gateway->refund([
                'amount'               => $this->formatAmount($amount),
                'currency'             => $context->currency(),
                'transactionReference' => $providerReference,
            ]);

            return $this->buildResultFromResponse($request->send(), 'Authorize.Net refund processed.', $providerReference);
        } catch (Throwable $exception) {
            return PaymentResult::failure($exception->getMessage());
        }
    }

    public function supportsPartialRefunds(): bool
    {
        return true;
    }

    private function configuredGateway(): GatewayInterface
    {
        $factory = $this->gatewayFactory ?? static fn (): GatewayInterface => Omnipay::create('AuthorizeNetApi_Api');
        $gateway = $factory();

        if (! $gateway instanceof GatewayInterface) {
            throw new RuntimeException('Authorize.Net gateway factory must return an instance of GatewayInterface.');
        }

        $gateway->setAuthName($this->loginId());
        $gateway->setTransactionKey($this->transactionKey());
        $gateway->setTestMode($this->testMode());

        return $gateway;
    }

    private function loginId(): string
    {
        return $this->loginId ??= (string) config('services.authorize.login');
    }

    private function transactionKey(): string
    {
        return $this->transactionKey ??= (string) config('services.authorize.transaction_key');
    }

    private function testMode(): bool
    {
        return $this->testMode ??= (bool) config('services.authorize.test_mode', true);
    }

    private function formatAmount(int $minorUnits): string
    {
        return number_format($minorUnits / 100, 2, '.', '');
    }

    private function buildResultFromResponse(ResponseInterface $response, string $successMessage, ?string $fallbackReference = null): PaymentResultInterface
    {
        $data = $response->getData();
        if (! is_array($data)) {
            if ($data === null) {
                $data = [];
            } elseif (is_object($data)) {
                try {
                    $converted = json_decode(json_encode($data, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
                    $data = is_array($converted) ? $converted : ['raw' => (string) $data];
                } catch (Throwable) {
                    $data = ['raw' => (string) $data];
                }
            } else {
                $data = ['raw' => (string) $data];
            }
        }

        $reference = (is_string($ref = $response->getTransactionReference()) && trim($ref) !== '') ? trim($ref) : $fallbackReference;

        if ($response->isSuccessful()) {
            return PaymentResult::success($response->getMessage() ?: $successMessage, $data, $reference);
        }

        if ($response->isRedirect()) {
            $payload = array_filter(array_merge($data, [
                'redirectMethod' => $this->getRedirectValue($response, 'getRedirectMethod'),
                'redirectData'   => $this->getRedirectValue($response, 'getRedirectData'),
            ]), fn ($v) => $v !== null);

            $redirectUrl = $this->getRedirectValue($response, 'getRedirectUrl');

            return PaymentResult::success(
                $response->getMessage() ?: 'Redirect to Authorize.Net required.',
                $payload,
                $reference,
                is_string($redirectUrl) ? $redirectUrl : null
            );
        }

        return PaymentResult::failure(
            $response->getMessage() ?: 'Authorize.Net request failed.',
            array_merge($data, ['code' => $response->getCode()])
        );
    }

    private function getRedirectValue(ResponseInterface $response, string $method): mixed
    {
        return is_callable([$response, $method]) ? $response->{$method}() : null;
    }
}
