<?php

namespace App\Http\Controllers\Sma\People;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Nnjeim\World\Models\Country;
use App\Http\Resources\Collection;
use App\Models\Sma\People\Customer;
use App\Http\Controllers\Controller;
use App\Models\Sma\People\PriceGroup;
use App\Models\Sma\Setting\CustomField;
use App\Models\Sma\People\CustomerGroup;
use App\Http\Requests\Sma\People\CustomerRequest;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/People/Customer/Index', [
            'payment_fields'  => CustomField::ofModel('payment')->get(),
            'custom_fields'   => CustomField::ofModel('customer')->get(),
            'countries'       => Country::with('states:id,name,country_id')->get(),
            'price_groups'    => PriceGroup::get(['id as value', 'name as label']),
            'customer_groups' => CustomerGroup::get(['id as value', 'name as label']),

            'pagination' => new Collection(Customer::with('users')->filter($filters)->latest()->paginate()->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        if ($request->has('pos') && $request->pos == 1) {
            return to_route('pos', ['customer_id' => $customer->id])
                ->with('message', __('{model} has been successfully {action}.', [
                    'model'  => __('Customer'),
                    'action' => __('created'),
                ]));
        }

        return back()
            ->with('data', $customer)
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Customer'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Customer $customer)
    {
        if ($request->with == 'addresses') {
            $customer->load('addresses');
        }

        return $customer;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return back()
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Customer'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Display a usage statement of the resource.
     */
    public function statement(Customer $customer)
    {
        return Inertia::render('Sma/People/Customer/Statement', [
            'customer'   => $customer,
            'pagination' => new Collection(
                $customer->tracks()->balance()
                    ->orderByDesc('id')->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        if ($customer->{$customer->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Customer'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Customer'),
            'action' => __('deleted'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Customer::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function destroyPermanently(Customer $customer)
    {
        if ($customer->forceDelete()) {
            return to_route('customers.index')->with('message', __('{record} has been {action}.', ['record' => 'Customer', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
