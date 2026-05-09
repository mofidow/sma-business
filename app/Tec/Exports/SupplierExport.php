<?php

namespace App\Tec\Exports;

use App\Models\Sma\People\Supplier;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class SupplierExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(public bool $template = false) {}

    public function collection()
    {
        if ($this->template) {
            return collect([new Supplier()]);
        }

        return Supplier::with(['state:id,name', 'country:id,name'])->get();
    }

    public function headings(): array
    {
        return [
            'name', 'phone', 'email', 'company',  'due_limit', 'due_amount',
            'lot_no', 'street', 'address_line_1', 'address_line_2', 'city', 'postal_code', 'state', 'country',
            // 'opening_balance', 'extra_attributes',
        ];
    }

    public function map($supplier): array
    {
        return [
            'name'       => $supplier->name,
            'phone'      => (string) $supplier->phone,
            'email'      => $supplier->email,
            'company'    => $supplier->company,
            'due_limit'  => $supplier->due_limit,
            'due_amount' => (float) $supplier->balance,

            'lot_no'         => $supplier->lot_no,
            'street'         => $supplier->street,
            'address_line_1' => $supplier->address_line_1,
            'address_line_2' => $supplier->address_line_2,
            'city'           => $supplier->city,
            'postal_code'    => $supplier->postal_code,
            'state'          => $supplier->state_id ? $supplier->state->name : null,
            'country'        => $supplier->country_id ? $supplier->country->name : null,

            // 'opening_balance'  => $supplier->opening_balance,
            // 'extra_attributes' => $supplier->extra_attributes,
        ];
    }
}
