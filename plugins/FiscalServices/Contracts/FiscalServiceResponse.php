<?php

declare(strict_types=1);

namespace Plugins\FiscalServices\Contracts;

/**
 * @phpstan-type FiscalPayload array<string, mixed>
 */
readonly class FiscalServiceResponse
{
    /**
     * @param  FiscalPayload  $payload
     */
    private function __construct(
        private bool $successful,
        private string $message,
        private array $payload = [],
        private ?string $reference = null,
    ) {}

    /**
     * @param  FiscalPayload  $payload
     */
    public static function success(string $message, array $payload = [], ?string $reference = null): self
    {
        return new self(true, $message, $payload, $reference);
    }

    /**
     * @param  FiscalPayload  $payload
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
     * @return FiscalPayload
     */
    public function payload(): array
    {
        return $this->payload;
    }

    public function reference(): ?string
    {
        return $this->reference;
    }
}
