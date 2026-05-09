<?php

namespace App\Tec\Exports;

use App\Models\Sma\People\Customer;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class CustomerExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(public bool $template = false) {}

    public function collection()
    {
        if ($this->template) {
            return collect([new Customer()]);
        }

        return Customer::with([
            'priceGroup:id,name', 'customerGroup:id,name', 'state:id,name', 'country:id,name',
        ])->get();
    }

    public function headings(): array
    {
        return [
            'name', 'phone', 'email', 'company',  'due_limit', 'due_amount', 'price_group', 'customer_group',
            'lot_no', 'street', 'address_line_1', 'address_line_2', 'city', 'postal_code', 'state', 'country',
            // 'opening_balance', 'extra_attributes',
        ];
    }

    public function map($customer): array
    {
        return [
            'name'           => $customer->name,
            'phone'          => (string) $customer->phone,
            'email'          => $customer->email,
            'company'        => $customer->company,
            'due_limit'      => $customer->due_limit,
            'due_amount'     => (float) $customer->balance,
            'price_group'    => $customer->price_group_id ? $customer->price_group->name : null,
            'customer_group' => $customer->customer_group_id ? $customer->customer_group->name : null,

            'lot_no'         => $customer->lot_no,
            'street'         => $customer->street,
            'address_line_1' => $customer->address_line_1,
            'address_line_2' => $customer->address_line_2,
            'city'           => $customer->city,
            'postal_code'    => $customer->postal_code,
            'state'          => $customer->state_id ? $customer->state->name : null,
            'country'        => $customer->country_id ? $customer->country->name : null,

            // 'opening_balance'  => $customer->opening_balance,
            // 'extra_attributes' => $customer->extra_attributes,
        ];
    }
}
