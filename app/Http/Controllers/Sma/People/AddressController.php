<?php

namespace App\Http\Controllers\Sma\People;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Nnjeim\World\Models\Country;
use App\Http\Resources\Collection;
use App\Models\Sma\People\Address;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\People\AddressRequest;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/People/Address/Index', [
            'countries' => Country::with('states:id,name,country_id')->get(),

            'pagination' => new Collection(Address::filter($filters)->latest()->orderBy('name')->paginate()->withQueryString()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddressRequest $request)
    {
        Address::create($request->validated());

        return back()
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Address'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        return $address;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AddressRequest $request, Address $address)
    {
        $address->update($request->validated());

        return back()
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Address'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        if ($address->{$address->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Address'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Address'),
            'action' => __('deleted'),
        ]));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Address::whereIn('id', $request->selection)->get() as $record) {
            $record->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : '';
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed - $count]));
    }

    public function destroyPermanently(Address $address)
    {
        if ($address->forceDelete()) {
            return to_route('addresses.index')->with('message', __('{record} has been {action}.', ['record' => 'Address', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
