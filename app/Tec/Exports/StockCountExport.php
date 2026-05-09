<?php

namespace App\Tec\Exports;

use Generator;
use Maatwebsite\Excel\Excel;
use App\Models\Sma\Product\Product;
use App\Models\Sma\Product\StockCount;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class StockCountExport implements FromGenerator, WithColumnWidths, WithCustomValueBinder, WithHeadings, WithMapping
{
    use Exportable;

    private $writerType = Excel::XLSX;

    private $headers = ['Content-Type' => 'text/csv'];

    public function __construct(public StockCount $stock_count) {}

    public function columnWidths(): array
    {
        return ['A' => 40, 'B' => 20, 'C' => 20];
    }

    public function bindValue(Cell $cell, $value)
    {
        // if (is_numeric($value)) {
        //     $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);

        //     return true;
        // }

        $cell->setValueExplicit($value, DataType::TYPE_STRING);

        return true;
        // return parent::bindValue($cell, $value);
    }

    public function headings(): array
    {
        return ['product_code', 'variation_code', 'expected_quantity', 'in_store_quantity'];
    }

    public function map($product): array
    {
        return $product; // [$product['code'], $product->storeStock->balance, ''];
    }

    // public function collection()
    // {
    //     return Product::query()->without('taxes')->with('storeStock')
    //         ->whereHas('storeStock', fn ($query) => $query->whereHasBalance())->get(['id', 'code']);
    // }

    public function generator(): Generator
    {
        $products = Product::query()->ofType('Standard')
            ->without('taxes')->with(['storeStock', 'variations.storeStock']);
        // ->whereHas('storeStock', fn ($query) => $query->whereHasBalance());

        if ($this->stock_count->type == 'partial') {
            if ($this->stock_count->brands) {
                $products->whereIn('brand_id', $this->stock_count->brands);
            }
            if ($this->stock_count->categories) {
                $products->whereIn('category_id', $this->stock_count->categories);
            }
        }

        $products = $products->get(['id', 'code', 'has_variants']);

        foreach ($products as $product) {
            if ($product->has_variants) {
                foreach ($product->variations as $variation) {
                    yield [$product->code, $variation->code, $variation->storeStock->balance, ''];
                }
            } else {
                yield [$product->code, '', $product->storeStock->balance, ''];
            }
        }
    }
}
