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

class ProductTaxExport extends StringValueBinder implements FromGenerator, ShouldAutoSize, WithCustomValueBinder, WithHeadings, WithTitle
{
    use Exportable;

    public function __construct(public bool $template = false) {}

    // public function collection()
    // {
    //     return Product::with(['attachments', 'taxes:id,name', 'category:id,name', 'subcategory:id,name'])->get();
    // }

    public function generator(): Generator
    {
        if ($this->template || Product::ofType('Standard')->has('taxes')->doesntExist()) {
            yield [
                'product_code' => ' ',
                'tax_names'    => '',
            ];
        } else {
            $products = Product::with(['taxes:id,name'])->cursor();

            foreach ($products as $product) {
                yield [
                    'product_code' => (string) $product->code,
                    'tax_names'    => $product->taxes->pluck('name')->implode(', '),
                ];
            }
        }
    }

    public function headings(): array
    {
        return ['product_code', 'tax_names'];
    }

    // public function map($product): array
    // {
    //     $variants = [];
    //     if ($product->variants) {
    //         foreach ($product->variants as $variant) {
    //             $variants[] = $variant['name'] . ':' . implode(',', $variant['options']);
    //         }
    //     }
    //     $variants = implode('|', $variants);

    //     return [
    //         'type'             => $product->type,
    //         'name'             => $product->name,
    //         'secondary_name'   => $product->secondary_name,
    //         'code'             => (string) $product->code,
    //         'symbology'        => $product->symbology,
    //         'category_name'    => $product->category?->name,
    //         'subcategory_name' => $product->subcategory?->name,
    //         'brand_name'       => $product->brand?->name,
    //         'unit_code'        => $product->unit?->code,

    //         'cost'         => (float) $product->cost,
    //         'price'        => (float) $product->price,
    //         'min_price'    => (float) $product->min_price,
    //         'max_price'    => (float) $product->max_price,
    //         'max_discount' => (float) $product->max_discount,

    //         'hsn_number'       => $product->hsn_number,
    //         'sac_number'       => $product->sac_number,
    //         'weight'           => $product->weight,
    //         'dimensions'       => $product->dimensions,
    //         'rack_location'    => $product->rack_location,
    //         'supplier_company' => $product->supplier?->company,
    //         'supplier_part_id' => $product->supplier_part_id,

    //         'featured'         => $product->featured ? 'yes' : 'no',
    //         'hide_in_pos'      => $product->hide_in_pos ? 'yes' : 'no',
    //         'hide_in_shop'     => $product->hide_in_shop ? 'yes' : 'no',
    //         'tax_included'     => $product->tax_included ? 'yes' : 'no',
    //         'has_serials'      => $product->has_serials ? 'yes' : 'no',
    //         'can_edit_price'   => $product->can_edit_price ? 'yes' : 'no',
    //         'has_expiry_date'  => $product->has_expiry_date ? 'yes' : 'no',
    //         'dont_track_stock' => $product->dont_track_stock ? 'yes' : 'no',

    //         'photo'     => $product->photo,
    //         'photos'    => $product->attachments->pluck('filepath')->transform(fn ($p) => url($p))->implode(', '),
    //         'video_url' => $product->video_url,

    //         'alert_quantity' => $product->alert_quantity,
    //         'has_variants'   => $product->has_variants ? 'yes' : 'no',
    //         'variants'       => $variants,
    //         'tax_names'      => $product->taxes->pluck('name')->implode(', '),
    //         'slug'           => $product->slug,
    //         'title'          => $product->title,
    //         'keywords'       => $product->keywords,
    //         'noindex'        => $product->noindex ? 'yes' : 'no',
    //         'nofollow'       => $product->nofollow ? 'yes' : 'no',
    //         'description'    => $product->description,
    //         'details'        => $product->details,

    //         'extra_attributes' => $product->extra_attributes ? str(http_build_query($product->extra_attributes->toArray(), '', ', '))->replace('=', ':') : '',
    //     ];
    // }

    public function title(): string
    {
        return 'Product Taxes';
    }
}
