<?php

namespace App\Http\Controllers\Sma\Setting;

use Inertia\Inertia;
use App\Models\Sma\Setting\Tax;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\Setting\TaxRequest;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Sma/Setting/Tax/Index', [
            'taxes' => new Collection(Tax::latest()->orderBy('name')->paginate()->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaxRequest $request)
    {
        $tax = Tax::create($request->validated());

        return redirect()->route('taxes.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Tax'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Tax $tax)
    {
        return $tax;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaxRequest $request, Tax $tax)
    {
        $tax->update($request->validated());

        return redirect()->route('taxes.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Tax'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tax $tax)
    {
        if ($tax->{$tax->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Tax'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Tax'),
            'action' => __('deleted'),
        ]));
    }
}
