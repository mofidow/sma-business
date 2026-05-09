<?php

namespace App\Http\Controllers\Sma\Pos;

use App\Models\User;
use Inertia\Inertia;
use App\Tec\Scopes\OfStore;
use Illuminate\Http\Request;
use App\Models\Sma\Pos\Register;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;

class RegisterReportController extends Controller
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

        return Inertia::render('Sma/Pos/RegistersReport', [
            'stores' => Store::all(['id as value', 'name as label']),
            'users'  => User::employee()->get(['id as value', 'name as label']),

            'pagination' => new Collection(
                Register::withoutGlobalScope(OfStore::class)
                    ->with(['user:id,name', 'store', 'closedBy:id,name'])
                    ->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $sale = Register::withoutGlobalScope(OfStore::class)->with([
            'user:id,name', 'store', 'closedBy:id,name',
        ])->findOrFail($id);

        return response()->json($sale);
    }
}
