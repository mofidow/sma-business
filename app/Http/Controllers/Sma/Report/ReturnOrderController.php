<?php

namespace App\Http\Controllers\Sma\Report;

use App\Models\User;
use Inertia\Inertia;
use App\Tec\Scopes\OfStore;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Order\ReturnOrder;

class ReturnOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        $sale_totals = ReturnOrder::withoutGlobalScope(OfStore::class)
            ->selectRaw('COUNT(id) as count, SUM(grand_total) as total, SUM(total_tax_amount) as tax')
            ->ofType('Sale')
            ->filter($filters)->first();
        $purchase_totals = ReturnOrder::withoutGlobalScope(OfStore::class)
            ->selectRaw('COUNT(id) as count, SUM(grand_total) as total, SUM(total_tax_amount) as tax')
            ->ofType('Purchase')
            ->filter($filters)->first();

        return Inertia::render('Sma/Report/ReturnOrder', [
            'sale_totals'     => $sale_totals,
            'purchase_totals' => $purchase_totals,
            'stores'          => Store::all(['id as value', 'name as label']),
            'users'           => User::employee()->get(['id as value', 'name as label']),

            'pagination' => new Collection(
                ReturnOrder::withoutGlobalScope(OfStore::class)->with([
                    'store:id,name', 'user:id,name',
                    'customer:id,name,company', 'supplier:id,name,company',
                ])->filter($filters)->orderByDesc('date')->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $sale = ReturnOrder::withoutGlobalScope(OfStore::class)->with([
            'store', 'customer', 'supplier',
            'attachments', 'items.variations', 'items.product',
        ])->findOrFail($id);

        return response()->json($sale);
    }
}
