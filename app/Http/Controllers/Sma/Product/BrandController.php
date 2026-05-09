<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Product\Brand;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\Product\BrandRequest;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters');

        return Inertia::render('Sma/Product/Brand/Index', [
            'pagination' => new Collection(Brand::filter($filters)->latest()->orderBy('name')->paginate()->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BrandRequest $request)
    {
        Brand::create($request->validated());

        return redirect()->route('brands.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Brand'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        return $brand;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated());

        return redirect()->route('brands.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Brand'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        if ($brand->{$brand->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Brand'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}. The record is being used for relationships.', [
            'model'  => __('Brand'),
            'action' => __('deleted'),
        ]));
    }
}
