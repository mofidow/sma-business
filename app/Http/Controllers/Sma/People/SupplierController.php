<?php

namespace App\Http\Controllers\Sma\People;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Nnjeim\World\Models\Country;
use App\Http\Resources\Collection;
use App\Models\Sma\People\Supplier;
use App\Http\Controllers\Controller;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\People\SupplierRequest;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/People/Supplier/Index', [
            'payment_fields' => CustomField::ofModel('payment')->get(),
            'custom_fields'  => CustomField::ofModel('supplier')->get(),
            'countries'      => Country::with('states:id,name,country_id')->get(),

            'pagination' => new Collection(Supplier::with('users')->filter($filters)->latest()->paginate()->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return back()
            ->with('data', $supplier)
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Supplier'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return $supplier;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return back()
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Supplier'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Display a usage statement of the resource.
     */
    public function statement(Supplier $supplier)
    {
        return Inertia::render('Sma/People/Supplier/Statement', [
            'supplier'   => $supplier,
            'pagination' => new Collection(
                $supplier->tracks()->balance()
                    ->orderByDesc('id')->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->{$supplier->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Supplier'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Supplier'),
            'action' => __('deleted'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Supplier::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function destroyPermanently(Supplier $supplier)
    {
        if ($supplier->forceDelete()) {
            return to_route('suppliers.index')->with('message', __('{record} has been {action}.', ['record' => 'Supplier', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
