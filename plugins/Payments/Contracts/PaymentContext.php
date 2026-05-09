<?php

declare(strict_types=1);

namespace Plugins\Payments\Contracts;

use App\Models\Sma\Order\Sale;

/**
 * Encapsulates the contextual details required to process a payment request.
 */
readonly class PaymentContext implements PaymentContextInterface
{
    /**
     * @param  array<string, int|float|string|bool|null>  $metadata
     */
    public function __construct(
        private Sale $sale,
        private int $amount,
        private string $currency,
        private array $form = [],
        private array $metadata = [],
        private ?string $returnUrl = null,
        private ?string $notifyUrl = null,
    ) {}

    public function sale(): Sale
    {
        return $this->sale;
    }

    /**
     * Amount in currency minor units (for example cents).
     */
    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * @return array<string, int|float|string|bool|null>
     */
    public function form(): array
    {
        return $this->form;
    }

    /**
     * @return array<string, int|float|string|bool|null>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function returnUrl(): ?string
    {
        return $this->returnUrl;
    }

    public function notifyUrl(): ?string
    {
        return $this->notifyUrl;
    }
}
