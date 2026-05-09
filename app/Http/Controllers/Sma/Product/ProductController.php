<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Tax;
use App\Models\Sma\Product\Unit;
use App\Tec\Actions\SaveProduct;
use Nnjeim\World\Models\Country;
use App\Models\Sma\Product\Brand;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Models\Sma\Product\Product;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Category;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Product\ProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        if (! ($filters['store'] ?? null) && session('selected_store_id', null) && Store::count() > 1) {
            $filters['store'] = session('selected_store_id');
        }

        return Inertia::render('Sma/Product/Index', [
            'custom_fields' => CustomField::ofModel('product')->get(),
            'stores'        => Store::active()->get(['id as value', 'name as label']),

            'pagination' => new Collection(Product::withoutGlobalScope('active')->with(
                'supplier:id,name,company', 'taxes:id,name', 'stocks',
                'brand:id,name', 'category:id,name,category_id', 'unit:id,code,name',
            )->filter($filters)->latest('id')->orderBy('name')->paginate()->withQueryString()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Sma/Product/Form', [
            'stores'          => Store::all(['id', 'name']),
            'taxes'           => Tax::all(['id', 'name', 'rate']),
            'brands'          => Brand::active()->get(['id', 'name']),
            'custom_fields'   => CustomField::ofModel('product')->get(),
            'supplier_fields' => CustomField::ofModel('supplier')->get(),
            'countries'       => Country::with('states:id,name,country_id')->get(),
            'units'           => Unit::onlyBase()->with('subunits')->get(['id', 'name', 'unit_id']),
            'categories'      => Category::onlyParent()->with('children')->active()->get(['id', 'name', 'category_id']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $product = (new SaveProduct)->execute($request->validated());

        return redirect()->route('products.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Product'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Product $product)
    {
        $product->load([
            'unit', 'unit.subunits', 'unitPrices',
            'supplier:id,name,company', 'taxes:id,name',
            'products:id,code,name', 'stocks', 'stores', 'variations.stocks',
            'brand:id,name', 'category:id,name,category_id', 'unit:id,code,name',
        ]);

        if ($request->with == 'promotions') {
            $product->load(['validPromotions', 'category.validPromotions']);
        }

        return $product;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Product $product)
    {
        return Inertia::render('Sma/Product/Form', [
            'current' => $product->loadMissing(['stores', 'taxes', 'products', 'recipes.ingredient', 'recipes.unit', 'unitPrices', 'variations', 'attachments']),

            'stores'        => Store::all(['id', 'name']),
            'taxes'         => Tax::all(['id', 'name', 'rate']),
            'brands'        => Brand::active()->get(['id', 'name']),
            'custom_fields' => CustomField::ofModel('product')->get(),
            'units'         => Unit::onlyBase()->with('subunits')->get(['id', 'name', 'unit_id']),
            'categories'    => Category::onlyParent()->with('children')->active()->get(['id', 'name', 'category_id']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $product = (new SaveProduct)->execute($request->validated(), $product);

        // return redirect()->route('products.index')
        return back()
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Product'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->{$product->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Product'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}. The record is being used for relationships.', [
            'model'  => __('Product'),
            'action' => __('deleted'),
        ]));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Product $product)
    {
        if ($product->restore()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Product'),
                'action' => __('restored'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}. The record is being used for relationships.', [
            'model'  => __('Product'),
            'action' => __('restored'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Product::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function destroyPermanently(Product $product)
    {
        if ($product->forceDelete()) {
            return to_route('products.index')->with('message', __('{record} has been {action}.', ['record' => 'Product', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
