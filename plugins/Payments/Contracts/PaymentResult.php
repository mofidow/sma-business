<?php

declare(strict_types=1);

namespace Plugins\Payments\Contracts;

/**
 * @phpstan-type PaymentPayload array<string, mixed>
 */
readonly class PaymentResult implements PaymentResultInterface
{
    /**
     * @param  PaymentPayload  $payload
     */
    private function __construct(
        private bool $successful,
        private string $message,
        private array $payload = [],
        private ?string $providerReference = null,
        private ?string $redirectUrl = null,
    ) {}

    /**
     * @param  PaymentPayload  $payload
     */
    public static function success(string $message, array $payload = [], ?string $providerReference = null, ?string $redirectUrl = null): self
    {
        return new self(true, $message, $payload, $providerReference, $redirectUrl);
    }

    /**
     * @param  PaymentPayload  $payload
     */
    public static function failure(string $message, array $payload = []): self
    {
        return new self(false, $message, $payload);
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return PaymentPayload
     */
    public function payload(): array
    {
        return $this->payload;
    }

    public function providerReference(): ?string
    {
        return $this->providerReference;
    }

    public function redirectUrl(): ?string
    {
        return $this->redirectUrl;
    }
}
