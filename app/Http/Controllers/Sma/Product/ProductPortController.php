<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Tec\Exports\ProductsExport;
use App\Tec\Imports\ProductsImport;
use App\Http\Controllers\Controller;

class ProductPortController extends Controller
{
    public function export(Request $request)
    {
        $template = $request->has('template');

        return (new ProductsExport($template))
            ->download($template ? 'template_products.xlsx' : 'products.xlsx');
    }

    public function import()
    {
        return Inertia::render('Sma/Product/Import');
    }

    public function save(Request $request)
    {
        $request->validate(['excel' => 'required|file|mimes:xls,xlsx']);

        $path = $request->file('excel')->store('imports');

        (new ProductsImport($request->user()))->queue($path, 'local');

        return to_route('products.index')->with('message', __('{models} are being imported in the background.', ['models' => 'Products']));
    }
}
