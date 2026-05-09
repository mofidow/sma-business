<?php

namespace App\Http\Controllers\Sma\People;

use Inertia\Inertia;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\People\PriceGroup;
use App\Models\Sma\People\CustomerGroup;
use App\Http\Requests\Sma\People\CustomerGroupRequest;

class CustomerGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Sma/People/CustomerGroup/Index', [
            'price_groups'    => PriceGroup::get(['id as value', 'name as label']),
            'customer_groups' => new Collection(CustomerGroup::with('priceGroup:id,name')->latest()->orderBy('name')->paginate()->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerGroupRequest $request)
    {
        CustomerGroup::create($request->validated());

        return redirect()->route('customer_groups.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Customer group'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerGroup $customer_group)
    {
        return $customer_group;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerGroupRequest $request, CustomerGroup $customer_group)
    {
        $customer_group->update($request->validated());

        return redirect()->route('customer_groups.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Customer group'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerGroup $customer_group)
    {
        if ($customer_group->{$customer_group->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Customer group'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Customer group'),
            'action' => __('deleted'),
        ]));
    }
}
