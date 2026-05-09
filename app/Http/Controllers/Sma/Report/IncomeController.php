<?php

namespace App\Http\Controllers\Sma\Report;

use App\Models\User;
use Inertia\Inertia;
use App\Tec\Scopes\OfStore;
use Illuminate\Http\Request;
use App\Models\Sma\Order\Income;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;

class IncomeController extends Controller
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

        $totals = Income::withoutGlobalScope(OfStore::class)
            ->selectRaw('COUNT(id) as count, SUM(amount) as total')
            ->filter($filters)->first();

        return Inertia::render('Sma/Report/Income', [
            'totals' => $totals,
            'stores' => Store::all(['id as value', 'name as label']),
            'users'  => User::employee()->get(['id as value', 'name as label']),

            'pagination' => new Collection(
                Income::withoutGlobalScope(OfStore::class)
                    ->with(['customer:id,name,company', 'store:id,name', 'user:id,name'])
                    ->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $income = Income::withoutGlobalScope(OfStore::class)->with([
            'store', 'customer', 'user:id,name',
        ])->findOrFail($id);

        return response()->json($income);
    }
}
