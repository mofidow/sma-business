<?php

namespace App\Tec\Exports;

use Generator;
use App\Models\Sma\Product\Product;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class ComboProductExport extends StringValueBinder implements FromGenerator, ShouldAutoSize, WithCustomValueBinder, WithHeadings, WithTitle
{
    use Exportable;

    public function __construct(public bool $template = false) {}

    public function generator(): Generator
    {
        if ($this->template || Product::ofType('Combo')->doesntExist()) {
            yield [
                'main_product_code'  => ' ',
                'combo_product_code' => '',
                'quantity'           => '',
            ];
        } else {
            $products = Product::select(['id', 'code'])
                ->with(['products' => fn ($q) => $q->select(['id', 'code'])->without('taxes')])
                ->without('taxes')->ofType('Combo')->cursor();

            foreach ($products as $product) {
                foreach ($product->products as $combo_product) {
                    yield [
                        'main_product_code'  => (string) $product->code,
                        'combo_product_code' => (string) $combo_product->code,
                        'quantity'           => $combo_product->pivot->quantity,
                    ];
                }
            }
        }
    }

    public function headings(): array
    {
        return ['main_product_code', 'combo_product_code', 'quantity'];
    }

    public function title(): string
    {
        return 'Combo Products';
    }
}
