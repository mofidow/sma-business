<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Product\Track;
use App\Http\Resources\Collection;
use App\Models\Sma\Product\Product;
use App\Http\Controllers\Controller;

class StockTrackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request, Product $product)
    {
        $filters = $request->input('filters');
        $filters['product_id'] = $product->id;
        // $filters['stocks'] = $product->stocks;
        if (session('selected_store_id')) {
            $filters['store_id'] = session('selected_store_id');
        }

        return Inertia::render('Sma/Product/Track', [
            'product'    => $product,
            'pagination' => new Collection(
                Track::with(['store:id,name', 'variation:id,code,meta'])
                    ->filter($filters)->latest('id')->paginate()->withQueryString()
            ),
        ]);
    }
}
