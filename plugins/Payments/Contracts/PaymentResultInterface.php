<?php

declare(strict_types=1);

namespace Plugins\Payments\Contracts;

/**
 * Represents the outcome of interacting with an external payment gateway.
 */
interface PaymentResultInterface
{
    public function isSuccessful(): bool;

    public function message(): string;

    /**
     * @return array<string, mixed>
     */
    public function payload(): array;

    public function providerReference(): ?string;

    public function redirectUrl(): ?string;
}
