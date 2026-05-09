<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Tec\Exports\CategoryExport;
use App\Tec\Imports\CategoryImport;
use App\Http\Controllers\Controller;

class CategoryPortController extends Controller
{
    public function export(Request $request)
    {
        $template = $request->has('template');

        return (new CategoryExport($template))
            ->download($template ? 'categories_template.xlsx' : 'categories.xlsx');
    }

    public function import()
    {
        return Inertia::render('Sma/Product/Category/Import');
    }

    public function save(Request $request)
    {
        $request->validate(['excel' => 'required|file|mimes:xls,xlsx']);

        $path = $request->file('excel')->store('imports');

        (new CategoryImport($request->user()))->queue($path, 'local');

        return to_route('categories.index')->with('message', __('{models} are being imported in the background.', ['models' => 'Categories']));
    }
}
