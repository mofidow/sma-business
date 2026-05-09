<?php

namespace App\Http\Controllers\Sma\Setting;

use Inertia\Inertia;
use Nnjeim\World\Models\Country;
use App\Models\Sma\Setting\Store;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\People\PriceGroup;
use App\Models\Sma\Accounting\Account;
use App\Models\Sma\Setting\CustomField;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Sma\Setting\StoreRequest;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Sma/Setting/Store/Index', [
            'custom_fields' => CustomField::ofModel('store')->get(),
            'countries'     => Country::with('states:id,name,country_id')->get(),
            'price_groups'  => PriceGroup::get(['id as value', 'name as label']),
            'accounts'      => Account::active()->get(['id as value', 'title as label', 'reference']),

            'stores' => new Collection(
                Store::with('account:id,title')->latest()
                    ->orderBy('name')->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $store = Store::create($request->validated());

        return redirect()->route('stores.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Store'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        return $store;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Store $store)
    {
        $store->update($request->validated());

        return redirect()->route('stores.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Store'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Store $store)
    {
        if ($store->{$store->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Store'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Store'),
            'action' => __('deleted'),
        ]));
    }

    /**
     * Remove the logo of the store.
     */
    public function destroyLogo(Store $store)
    {
        if (! $store->logo) {
            return back()->with('error', __('{model} does not have a {field}.', [
                'model' => __('Store'),
                'field' => __('logo'),
            ]));
        }

        Storage::disk('asset')->delete($store->logo);
        $store->update(['logo' => null]);

        return back()->with('message', __('{model} has been successfully {action}.', [
            'model'  => __('Store logo'),
            'action' => __('deleted'),
        ]));
    }
}
