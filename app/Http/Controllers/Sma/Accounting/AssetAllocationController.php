<?php

namespace App\Http\Controllers\Sma\Accounting;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Accounting\Asset;
use App\Models\Sma\Accounting\AssetAllocation;
use App\Http\Requests\Sma\Accounting\AssetAllocationRequest;

class AssetAllocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('Sma/Accounting/AssetAllocation/Index', [
            'assets' => Asset::active()->orderBy('name')->get(['id', 'name']),
            'users'  => User::orderBy('name')->get(['id', 'name']),

            'pagination' => new Collection(
                AssetAllocation::with('asset:id,name', 'allocatedTo:id,name', 'allocatedBy:id,name')
                    ->filter($request->input('filters'))
                    ->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AssetAllocationRequest $request)
    {
        $data = $request->validated();
        $data['allocated_by'] = auth()->id();

        AssetAllocation::create($data);

        return redirect()->route('asset-allocations.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset Allocation'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(AssetAllocation $assetAllocation)
    {
        return $assetAllocation->load('asset:id,name', 'allocatedTo:id,name', 'allocatedBy:id,name');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AssetAllocationRequest $request, AssetAllocation $assetAllocation)
    {
        $assetAllocation->update($request->validated());

        return redirect()->route('asset-allocations.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset Allocation'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetAllocation $assetAllocation)
    {
        if ($assetAllocation->{$assetAllocation->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Asset Allocation'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Asset Allocation'),
            'action' => __('deleted'),
        ]));
    }
}
