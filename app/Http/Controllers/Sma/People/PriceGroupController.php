<?php

namespace App\Http\Controllers\Sma\People;

use Inertia\Inertia;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\People\PriceGroup;
use App\Http\Requests\Sma\People\PriceGroupRequest;

class PriceGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Sma/People/PriceGroup/Index', [
            'price_groups' => new Collection(PriceGroup::latest()->orderBy('name')->paginate()->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PriceGroupRequest $request)
    {
        PriceGroup::create($request->validated());

        return redirect()->route('price_groups.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Price group'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(PriceGroup $price_group)
    {
        return $price_group;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PriceGroupRequest $request, PriceGroup $price_group)
    {
        $price_group->update($request->validated());

        return redirect()->route('price_groups.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Price group'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PriceGroup $price_group)
    {
        if ($price_group->{$price_group->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Price group'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Price group'),
            'action' => __('deleted'),
        ]));
    }
}
