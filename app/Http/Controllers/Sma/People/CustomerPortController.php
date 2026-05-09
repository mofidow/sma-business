<?php

namespace App\Http\Controllers\Sma\People;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Tec\Exports\CustomerExport;
use App\Tec\Imports\CustomerImport;
use App\Http\Controllers\Controller;

class CustomerPortController extends Controller
{
    public function export(Request $request)
    {
        $template = $request->has('template');

        return (new CustomerExport($template))
            ->download($template ? 'customers_template.xlsx' : 'customers.xlsx');
    }

    public function import()
    {
        return Inertia::render('Sma/People/Customer/Import');
    }

    public function save(Request $request)
    {
        $request->validate(['excel' => 'required|file|mimes:xls,xlsx']);

        $path = $request->file('excel')->store('imports');

        (new CustomerImport($request->user()))->queue($path, 'local');

        return to_route('customers.index')->with('message', __('{models} are being imported in the background.', ['models' => 'Customers']));
    }
}
