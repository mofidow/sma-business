<?php

declare(strict_types=1);

namespace Plugins\Payments\Contracts;

use App\Models\Sma\Order\Sale;

/**
 * Describes the contextual data required for processing a payment request.
 */
interface PaymentContextInterface
{
    public function sale(): Sale;

    public function amount(): int;

    public function currency(): string;

    /**
     * @return array<string, int|float|string|bool|null>
     */
    public function form(): array;

    /**
     * @return array<string, int|float|string|bool|null>
     */
    public function metadata(): array;

    public function returnUrl(): ?string;

    public function notifyUrl(): ?string;
}
