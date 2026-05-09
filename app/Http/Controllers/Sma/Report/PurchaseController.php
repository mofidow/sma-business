<?php

namespace App\Http\Controllers\Sma\Report;

use App\Models\User;
use Inertia\Inertia;
use App\Tec\Scopes\OfStore;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Models\Sma\Order\Purchase;
use App\Http\Controllers\Controller;

class PurchaseController extends Controller
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

        $totals = Purchase::withoutGlobalScope(OfStore::class)
            ->selectRaw('COUNT(id) as count, SUM(grand_total) as total, SUM(total_tax_amount) as tax, SUM(paid) as paid')
            ->filter($filters)->first();

        return Inertia::render('Sma/Report/Purchase', [
            'totals' => $totals,
            'stores' => Store::all(['id as value', 'name as label']),
            'users'  => User::employee()->get(['id as value', 'name as label']),

            'pagination' => new Collection(
                Purchase::withoutGlobalScope(OfStore::class)
                    ->with(['supplier:id,name,company', 'store:id,name', 'user:id,name'])
                    ->filter($filters)->orderByDesc('date')->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $sale = Purchase::withoutGlobalScope(OfStore::class)->withCount('returnOrders')->with([
            'attachments', 'items.variations', 'items.product',
            'store', 'supplier', 'payments:id,date,amount,method,reference',
            'returnOrders:id,reference,date,grand_total,return_payment_amount,return_payment_method,purchase_id',
        ])->findOrFail($id);

        return response()->json($sale);
    }
}
