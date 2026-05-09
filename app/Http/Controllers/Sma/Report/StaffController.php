<?php

namespace App\Http\Controllers\Sma\Report;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;

class StaffController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->input('filters') ?? [];
        // $filters = $request->all('supplier_id', 'store_id', 'start', 'end', 'trashed');

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        return Inertia::render('Sma/Report/Staff', [
            'stores'     => Store::all(['id as value', 'name as label']),
            'pagination' => new Collection(
                User::employee()->select(['id', 'name', 'email', 'phone'])
                    ->withCount([
                        'sales'     => fn ($q) => $q->withoutGlobalScopes(),
                        'purchases' => fn ($q) => $q->withoutGlobalScopes(),
                    ])
                    ->withSum(['sales' => fn ($q) => $q->withoutGlobalScopes()], 'grand_total')
                    ->withSum(['purchases' => fn ($q) => $q->withoutGlobalScopes()], 'grand_total')
                    ->filter($filters)->orderBy('users.name')->paginate()->withQueryString()
            ),
        ]);
    }
}
