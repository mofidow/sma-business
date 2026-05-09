<?php

namespace App\Http\Controllers\Sma\Accounting;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Accounting\Asset;
use App\Models\Sma\Accounting\AssetMaintenance;
use App\Http\Requests\Sma\Accounting\AssetMaintenanceRequest;

class AssetMaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('Sma/Accounting/AssetMaintenance/Index', [
            'assets'   => Asset::active()->orderBy('name')->get(['id', 'name']),
            'types'    => AssetMaintenance::$types,
            'statuses' => AssetMaintenance::$statuses,

            'pagination' => new Collection(
                AssetMaintenance::with('asset:id,name', 'user:id,name')
                    ->filter($request->input('filters'))
                    ->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AssetMaintenanceRequest $request)
    {
        AssetMaintenance::create($request->validated());

        return redirect()->route('asset-maintenances.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset Maintenance'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(AssetMaintenance $assetMaintenance)
    {
        return $assetMaintenance->load('asset:id,name', 'user:id,name');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AssetMaintenanceRequest $request, AssetMaintenance $assetMaintenance)
    {
        $assetMaintenance->update($request->validated());

        return redirect()->route('asset-maintenances.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset Maintenance'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetMaintenance $assetMaintenance)
    {
        if ($assetMaintenance->{$assetMaintenance->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset Maintenance'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Asset Maintenance'),
            'action' => __('deleted'),
        ]));
    }
}
