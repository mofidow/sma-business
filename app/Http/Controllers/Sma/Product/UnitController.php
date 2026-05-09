<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Product\Unit;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\Product\UnitRequest;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters');

        return Inertia::render('Sma/Product/Unit/Index', [
            'parents' => Unit::onlyBase()->get(['id as value', 'name as label', 'unit_id']),

            'pagination' => new Collection(Unit::with('unit')->filter($filters)->latest()->orderBy('name')->paginate()->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnitRequest $request)
    {
        Unit::create($request->validated());

        return redirect()->route('units.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Unit'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        return $unit;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return redirect()->route('units.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Unit'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        if ($unit->{$unit->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Unit'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Unit'),
            'action' => __('deleted'),
        ]));
    }
}
