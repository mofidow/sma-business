<?php

namespace App\Http\Controllers\Sma\Accounting;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Accounting\Asset;
use App\Models\Sma\Accounting\AssetCategory;
use App\Http\Requests\Sma\Accounting\AssetRequest;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('Sma/Accounting/Asset/Index', [
            'categories' => AssetCategory::active()->orderBy('name')->get(['id', 'name']),
            'conditions' => Asset::$conditions,

            'pagination' => new Collection(
                Asset::with('category:id,name')
                    ->filter($request->input('filters'))
                    ->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AssetRequest $request)
    {
        Asset::create($request->validated());

        return redirect()->route('assets.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset)
    {
        return $asset->load('category:id,name', 'currentAllocation.allocatedTo:id,name');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AssetRequest $request, Asset $asset)
    {
        $asset->update($request->validated());

        return redirect()->route('assets.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        if ($asset->{$asset->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Asset'),
            'action' => __('deleted'),
        ]));
    }
}
