<?php

namespace App\Http\Controllers\Sma\Report;

use App\Models\User;
use Inertia\Inertia;
use App\Tec\Scopes\OfStore;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Transfer;

class TransferController extends Controller
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

        return Inertia::render('Sma/Report/Transfer', [
            'stores' => Store::all(['id as value', 'name as label']),
            'users'  => User::employee()->get(['id as value', 'name as label']),

            'pagination' => new Collection(
                Transfer::withoutGlobalScope(OfStore::class)
                    ->with(['toStore', 'store:id,name', 'user:id,name'])
                    ->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $transfer = Transfer::withoutGlobalScope(OfStore::class)->with([
            'attachments', 'items.variations', 'items.product', 'store', 'toStore',
        ])->findOrFail($id);

        return response()->json($transfer);
    }
}
