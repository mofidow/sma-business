<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Tec\Exports\BrandExport;
use App\Tec\Imports\BrandImport;
use App\Http\Controllers\Controller;

class BrandPortController extends Controller
{
    public function export(Request $request)
    {
        $template = $request->has('template');

        return (new BrandExport($template))
            ->download($template ? 'brands_template.xlsx' : 'brands.xlsx');
    }

    public function import()
    {
        return Inertia::render('Sma/Product/Brand/Import');
    }

    public function save(Request $request)
    {
        $request->validate(['excel' => 'required|file|mimes:xls,xlsx']);

        $path = $request->file('excel')->store('imports');

        (new BrandImport($request->user()))->queue($path, 'local');

        return to_route('brands.index')->with('message', __('{models} are being imported in the background.', ['models' => 'Brands']));
    }
}
