<?php

namespace App\Http\Controllers\Sma\Report;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Models\Sma\People\Customer;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->input('filters') ?? [];
        // $filters = $request->all('customer_id', 'store_id', 'start', 'end', 'trashed');

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        return Inertia::render('Sma/Report/Customer', [
            'stores'     => Store::all(['id as value', 'name as label']),
            'users'      => User::employee()->get(['id as value', 'name as label']),
            'pagination' => new Collection(
                Customer::join('sales', 'sales.customer_id', '=', 'customers.id')
                    ->selectRaw('customers.id as id, customers.company, customers.name, customers.email, customers.phone, COUNT(sales.id) as sales, SUM(sales.grand_total-sales.rounding) as amount, SUM(sales.paid) as paid')->groupBy('customers.id')
                    ->filter($filters)->orderBy('customers.name')->paginate()->withQueryString()
            ),
        ]);
    }
}
