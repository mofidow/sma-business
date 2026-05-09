<?php

namespace App\Http\Controllers\Sma\Report;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Models\Sma\Order\SaleItem;
use App\Models\Sma\Product\Product;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Category;
use App\Models\Sma\Order\PurchaseItem;

class ProductController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->input('filters') ?? [];
        // $filters = $request->all('products', 'category_id', 'store_id', 'start', 'end', 'trashed');

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        $piSQ = PurchaseItem::query()->whereColumn('product_id', 'products.id')
            ->when($filters['store_id'] ?? null, fn ($q, $store_id) => $q->ofStore($store_id))
            ->when($filters['end'] ?? null, fn ($q, $end) => $q->where('created_at', '<=', $end))
            ->when($filters['start'] ?? null, fn ($q, $start) => $q->where('created_at', '>=', $start));
        $siSQ = SaleItem::query()->whereColumn('product_id', 'products.id')
            ->when($filters['store_id'] ?? null, fn ($q, $store_id) => $q->ofStore($store_id))
            ->when($filters['end'] ?? null, fn ($q, $end) => $q->where('created_at', '<=', $end))
            ->when($filters['start'] ?? null, fn ($q, $start) => $q->where('created_at', '>=', $start));

        $products = Product::query()
            ->select(['products.id as id', 'products.code as code', 'products.name as name', 'products.cost as cost'])
            ->addSelect(['total_purchased' => $piSQ->clone()->selectRaw('SUM(quantity)')])
            ->addSelect(['total_purchased_amount' => $piSQ->clone()->selectRaw('SUM(subtotal)')])
            ->addSelect(['total_sold' => $siSQ->clone()->selectRaw('SUM(quantity)')])
            ->addSelect(['total_sale_amount' => $siSQ->clone()->selectRaw('SUM(subtotal)')])
            ->addSelect(['total_cost' => $siSQ->clone()->selectRaw('SUM(quantity*cost)')])
            // ->when($filters['products'] ?? null, fn ($q, $ids) => $q->whereIn('id', $ids))
            // ->when($filters['categories'] ?? null, fn ($q, $ids) => $q->whereIn('category_id', $ids))
            ->with(['stocks' => fn ($q) => ($request->store_id ? $q->ofStore($filters['store_id']) : $q)]);

        if ($filters['products'] ?? null) {
            $products->whereIn('products.id', $filters['products']);
        }

        if ($filters['categories'] ?? null) {
            $products->join('categories', 'categories.id', '=', 'products.category_id')->whereIn('categories.id', $filters['categories']);
        }

        if (($filters['sort'] ?? null) == 'latest') {
            $products->latest('products.id');
        } elseif (($filters['sort'] ?? null) && str($filters['sort'])->contains(':')) {
            [$col, $dir] = explode(':', $filters['sort']);
            $products->orderBy(str($col)->replace(' ', '_'), $dir);
        }
        // logger(get_sql_query($products));

        return Inertia::render('Sma/Report/Product', [
            'stores'     => Store::all(['id as value', 'name as label']),
            'categories' => Category::onlyParent()->get(['id as value', 'name as label']),
            'pagination' => new Collection(
                $products->orderBy('name')->paginate()->withQueryString()
            ),
        ]);
    }
}
