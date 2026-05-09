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

class ProductVariationExport extends StringValueBinder implements FromGenerator, ShouldAutoSize, WithCustomValueBinder, WithHeadings, WithTitle
{
    use Exportable;

    public function __construct(public bool $template = false) {}

    public function generator(): Generator
    {
        if ($this->template || Product::ofType('Standard')->where('has_variants', 1)->doesntExist()) {
            yield [
                'product_code'         => '',
                'variation_code'       => '',
                'variation_meta'       => '',
                'variation_cost'       => '',
                'variation_price'      => '',
                'variation_rack'       => '',
                'variation_weight'     => '',
                'variation_dimensions' => '',
            ];
        } else {
            $products = Product::select(['id', 'code'])->without('taxes')
                ->with('variations')->where('has_variants', 1)->cursor();

            foreach ($products as $product) {
                foreach ($product->variations as $variation) {
                    $meta = [];
                    if ($variation->meta) {
                        foreach ($variation->meta as $key => $value) {
                            $meta[] = $key . ':' . $value;
                        }
                    }
                    $meta = implode('|', $meta);

                    yield [
                        'product_code'         => (string) $product->code,
                        'variation_code'       => (string) $variation->code,
                        'variation_meta'       => $meta,
                        'variation_cost'       => (float) $variation->cost,
                        'variation_price'      => (float) $variation->price,
                        'variation_rack'       => (string) $variation->rack,
                        'variation_weight'     => (float) $variation->weight,
                        'variation_dimensions' => (string) $variation->dimensions,
                    ];
                }
            }
        }
    }

    public function headings(): array
    {
        return [
            'product_code', 'variation_code', 'variation_meta',
            'variation_cost', 'variation_price', 'variation_weight', 'variation_dimensions',
        ];
    }

    public function title(): string
    {
        return 'Product Variations';
    }
}
