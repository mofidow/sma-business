<?php

namespace App\Http\Controllers\Sma\People;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Tec\Exports\SupplierExport;
use App\Tec\Imports\SupplierImport;
use App\Http\Controllers\Controller;

class SupplierPortController extends Controller
{
    public function export(Request $request)
    {
        $template = $request->has('template');

        return (new SupplierExport($template))
            ->download($template ? 'suppliers_template.xlsx' : 'suppliers.xlsx');
    }

    public function import()
    {
        return Inertia::render('Sma/People/Supplier/Import');
    }

    public function save(Request $request)
    {
        $request->validate(['excel' => 'required|file|mimes:xls,xlsx']);

        $path = $request->file('excel')->store('imports');

        (new SupplierImport($request->user()))->queue($path, 'local');

        return to_route('suppliers.index')->with('message', __('{models} are being imported in the background.', ['models' => 'Suppliers']));
    }
}
