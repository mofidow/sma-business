<?php

namespace App\Http\Controllers\Sma\Report;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Models\Sma\People\Supplier;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->input('filters') ?? [];
        // $filters = $request->all('supplier_id', 'store_id', 'start', 'end', 'trashed');

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        return Inertia::render('Sma/Report/Supplier', [
            'stores'     => Store::all(['id as value', 'name as label']),
            'users'      => User::employee()->get(['id as value', 'name as label']),
            'pagination' => new Collection(
                Supplier::join('purchases', 'purchases.supplier_id', '=', 'suppliers.id')
                    ->selectRaw('suppliers.id as id, suppliers.company, suppliers.name, suppliers.email, suppliers.phone, COUNT(purchases.id) as purchases, SUM(purchases.grand_total) as amount, SUM(purchases.paid) as paid')->groupBy('suppliers.id')
                    ->filter($filters)->orderBy('suppliers.name')->paginate()->withQueryString()
            ),
        ]);
    }
}
