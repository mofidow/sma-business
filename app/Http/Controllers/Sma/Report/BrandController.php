<?php

namespace App\Http\Controllers\Sma\Report;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Product\Brand;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Models\Sma\Product\Product;
use App\Http\Controllers\Controller;

class BrandController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        // $products = Product::query()
        //     ->select(['products.brand_id', 'brands.name'])
        //     ->join('brands', 'brands.id', '=', 'products.brand_id')
        //     ->leftJoin('sale_items', 'sale_items.product_id', '=', 'products.id')
        //     ->leftJoin('purchase_items', 'purchase_items.product_id', '=', 'products.id')
        //     ->selectRaw('SUM(purchase_items.quantity) as total_purchased, SUM(purchase_items.total) as total_purchased_amount, SUM(sale_items.quantity) as total_sold, SUM(sale_items.total) as total_sale_amount, SUM(IFNULL(sale_items.cost, 0)*sale_items.quantity) as total_cost')
        //     ->groupBy('products.brand_id');
        // // ->having('total_purchased', '>', 0)->orHaving('total_sold', '>', 0);

        $brands = Brand::query()->select(['brands.id', 'brands.name'])
            ->withSum(['saleItems' => fn ($q) => $q->filter($filters)], 'total')
            ->withSum(['saleItems' => fn ($q) => $q->filter($filters)], 'quantity')
            ->withSum(['purchaseItems' => fn ($q) => $q->filter($filters)], 'total')
            ->withSum(['purchaseItems' => fn ($q) => $q->filter($filters)], 'quantity');

        if ($filters['brands'] ?? null) {
            $brands->whereIn('brands.id', $filters['brands']);
        }

        if (($filters['sort'] ?? null) == 'latest') {
            $brands->latest('id');
        } elseif (($filters['sort'] ?? null) && str($filters['sort'])->contains(':')) {
            [$col, $dir] = explode(':', $filters['sort']);
            $brands->orderBy(str($col)->replace(' ', '_'), $dir);
        }

        // logger(get_sql_query($brands));

        return Inertia::render('Sma/Report/Brand', [
            'stores'     => Store::all(['id as value', 'name as label']),
            'brands'     => Brand::active()->get(['id as value', 'name as label']),
            'pagination' => new Collection(
                $brands->orderBy('name')
                    ->paginate()->withQueryString()
            ),
        ]);
    }
}
