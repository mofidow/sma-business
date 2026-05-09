<?php

namespace App\Http\Controllers\Sma\Report;

use App\Models\User;
use Inertia\Inertia;
use App\Tec\Scopes\OfStore;
use Illuminate\Http\Request;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Order\Payment;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Models\Sma\Order\Purchase;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
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

        $received = Payment::withoutGlobalScope(OfStore::class)
            ->selectRaw('COUNT(id) as count, SUM(amount) as total')
            ->addSelect(
                ['due' => Sale::withoutGlobalScope(OfStore::class)
                    ->filter($filters)->selectRaw('SUM(grand_total - paid)')]
            )
            ->received()->where('payment_for', 'Customer')
            ->filter($filters)->first();
        $sent = Payment::withoutGlobalScope(OfStore::class)
            ->selectRaw('COUNT(id) as count, SUM(amount) as total')
            ->addSelect(
                ['due' => Purchase::withoutGlobalScope(OfStore::class)
                    ->filter($filters)->selectRaw('SUM(grand_total - paid)')]
            )
            ->received()->where('payment_for', 'Supplier')
            ->filter($filters)->first();

        return Inertia::render('Sma/Report/Payment', [
            'sent'     => $sent,
            'received' => $received,
            'stores'   => Store::all(['id as value', 'name as label']),
            'users'    => User::employee()->get(['id as value', 'name as label']),

            'pagination' => new Collection(
                Payment::withoutGlobalScope(OfStore::class)->with([
                    'store:id,name', 'user:id,name',
                    'customer:id,company,name', 'supplier:id,company,name',
                ])->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $sale = Payment::withoutGlobalScope(OfStore::class)->with([
            'sales:id,reference', 'purchases:id,reference',
            'store', 'customer', 'supplier', 'user:id,name',
        ])->findOrFail($id);

        return response()->json($sale);
    }
}
