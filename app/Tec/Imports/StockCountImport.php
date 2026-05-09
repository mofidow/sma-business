<?php

namespace App\Tec\Imports;

use Illuminate\Support\Collection;
use App\Models\Sma\Product\Product;
use App\Models\Sma\Product\StockCount;
use App\Models\Sma\Product\StockCountItem;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StockCountImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function __construct(public StockCount $stock_count) {}

    public function collection(Collection $collection)
    {
        $products = Product::query()->ofType('Standard')
            ->without('taxes')->with(['storeStock', 'variations.storeStock']);

        if ($this->stock_count->type == 'partial') {
            if ($this->stock_count->brands) {
                $products->whereIn('brand_id', $this->stock_count->brands);
            }
            if ($this->stock_count->categories) {
                $products->whereIn('category_id', $this->stock_count->categories);
            }
        }

        $products = $products->get(['id', 'code', 'has_variants']);

        foreach ($collection as $row) {
            $product = $products->where('code', $row['product_code'])->first();
            if ($row['variation_code'] ?? null) {
                $product = $product->variations->where('code', $row['variation_code'])->first();
            }

            if (! $product) {
                throw new Exception(__('No product found with code {code}', ['code' => $row['product_code']]));
            }

            StockCountItem::create([
                'stock_count_id'    => $this->stock_count->id,
                'product_code'      => $row['product_code'],
                'variation_code'    => $row['variation_code'] ?: null,
                'expected_quantity' => $product->storeStock->balance,
                'in_store_quantity' => $row['in_store_quantity'],
            ]);
        }

        // return $collection;
    }

    public function rules(): array
    {
        return [
            'product_code'      => 'required',
            'variation_code'    => 'nullable',
            'expected_quantity' => 'required',
            'in_store_quantity' => 'required',
        ];
    }
}
