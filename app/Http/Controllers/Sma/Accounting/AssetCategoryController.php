<?php

namespace App\Http\Controllers\Sma\Accounting;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Accounting\AssetCategory;
use App\Http\Requests\Sma\Accounting\AssetCategoryRequest;

class AssetCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('Sma/Accounting/AssetCategory/Index', [
            'pagination' => new Collection(
                AssetCategory::withCount('assets')
                    ->filter($request->input('filters'))
                    ->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AssetCategoryRequest $request)
    {
        AssetCategory::create($request->validated());

        return redirect()->route('asset-categories.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset Category'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(AssetCategory $assetCategory)
    {
        return $assetCategory->loadCount('assets');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AssetCategoryRequest $request, AssetCategory $assetCategory)
    {
        $assetCategory->update($request->validated());

        return redirect()->route('asset-categories.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset Category'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetCategory $assetCategory)
    {
        if ($assetCategory->{$assetCategory->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset Category'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Asset Category'),
            'action' => __('deleted'),
        ]));
    }
}
