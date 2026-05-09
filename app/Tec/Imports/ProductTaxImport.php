<?php

namespace App\Tec\Imports;

use App\Models\User;
use Maatwebsite\Excel\Row;
use App\Models\Sma\Setting\Tax;
use App\Models\Sma\Product\Product;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductTaxImport implements OnEachRow, WithHeadingRow
{
    use Importable;

    public $taxes;

    public function __construct(public User $user)
    {
        $this->taxes = Tax::all();
    }

    public function onRow(Row $row)
    {
        $row = $row->toArray();
        if (($row['product_code'] ?? null) && ($row['tax_names'] ?? null)) {
            $taxes = array_map('trim', explode(',', $row['tax_names']));

            if ($taxes) {
                $taxes = $this->taxes->filter(fn ($t) => in_array($t->name, $taxes))?->pluck('id')->all();
                if ($taxes) {
                    $product = Product::where('code', $row['product_code'])->first();
                    $product?->taxes()->sync($taxes);
                }
            }
        }
    }
}
