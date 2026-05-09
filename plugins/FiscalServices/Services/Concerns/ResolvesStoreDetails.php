<?php

declare(strict_types=1);

namespace Plugins\FiscalServices\Services\Concerns;

use App\Models\Sma\Order\ReturnOrder;
use App\Models\Sma\Order\Sale;

trait ResolvesStoreDetails
{
    /**
     * @return array{
     *     name: string,
     *     vat_number: string|null,
     *     email: string|null,
     *     phone: string|null,
     *     street: string|null,
     *     lot_number: string|null,
     *     address_line_1: string|null,
     *     address_line_2: string|null,
     *     city: string|null,
     *     state_name: string|null,
     *     state_code: string|null,
     *     country_name: string|null,
     *     country_code: string|null,
     *     postal_code: string|null,
     * }
     */
    private function storeDetails(Sale|ReturnOrder $subject): array
    {
        $store = rescue(function () use ($subject) {
            if ($subject instanceof Sale) {
                return $subject->getRelationValue('store');
            }

            $sale = $subject->getRelationValue('sale');

            return $sale?->getRelationValue('store');
        }, report: false);

        return [
            'name'           => $this->storeValue($store?->name, 'company', 'Primary Store'),
            'vat_number'     => $store?->vat_no ?? $this->storeValue(null, 'vat_no'),
            'email'          => $this->storeValue($store?->email, 'email'),
            'phone'          => $this->storeValue($store?->phone, 'phone'),
            'street'         => $this->storeValue($store?->street, 'street'),
            'lot_number'     => $this->storeValue($store?->lot_no, 'lot_no', 'S/N'),
            'address_line_1' => $this->storeValue($store?->address_line_1, 'address_line_1'),
            'address_line_2' => $this->storeValue($store?->address_line_2, 'address_line_2'),
            'city'           => $this->storeValue($store?->city, 'city'),
            'state_name'     => $store?->state?->name ?? $this->storeValue(null, 'state'),
            'state_code'     => $store?->state?->code ?? $this->storeValue(null, 'state_code'),
            'country_name'   => $store?->country?->name ?? $this->storeValue(null, 'country'),
            'country_code'   => $store?->country?->iso2 ?? $this->storeValue(null, 'country_code'),
            'postal_code'    => $this->storeValue($store?->postal_code, 'postal_code'),
        ];
    }

    private function storeValue(?string $value, string $settingKey, ?string $default = null): ?string
    {
        if (filled($value)) {
            return $value;
        }

        return rescue(fn () => (string) get_settings($settingKey), $default, report: false) ?: $default;
    }
}