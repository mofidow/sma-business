<?php

namespace App\Tec\Imports;

use Maatwebsite\Excel\Row;
use Illuminate\Support\Facades\DB;
use App\Models\Sma\Product\Product;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ComboProductRowImport implements OnEachRow, WithHeadingRow
{
    use Importable, RegistersEventListeners;

    public function onRow(Row $row)
    {
        $row = $row->toArray();

        $row['main_product_code'] ??= null;
        $row['combo_product_code'] ??= null;
        $row['quantity'] ??= null;

        if ($row['main_product_code'] && $row['combo_product_code'] && $row['quantity']) {
            $mainProduct = Product::select('id')->where('code', $row['main_product_code'])->first();
            $comboProduct = Product::select('id')->where('code', $row['combo_product_code'])->first();

            if ($mainProduct && $comboProduct) {
                DB::table('product_product')->updateOrInsert(
                    ['combo_id' => $mainProduct->id, 'product_id' => $comboProduct->id, 'quantity' => $row['quantity']],
                    ['combo_id' => $mainProduct->id, 'product_id' => $comboProduct->id]
                );
            }
        }
    }
}
