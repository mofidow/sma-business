<?php

namespace App\Http\Controllers\Sma\Repair;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Repair\ServiceType;
use App\Http\Requests\Sma\Repair\ServiceTypeRequest;

class ServiceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters');

        return Inertia::render('Sma/Repair/ServiceType/Index', [
            'pagination' => new Collection(
                ServiceType::filter($filters)
                    ->ordered()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceTypeRequest $request)
    {
        $serviceType = ServiceType::create($request->validated());

        return back()
            ->with('data', $serviceType)
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Service Type'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceType $serviceType)
    {
        return $serviceType->load('repairOrders');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceTypeRequest $request, ServiceType $serviceType)
    {
        $serviceType->update($request->validated());

        return redirect()->route('service-types.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Service Type'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceType $serviceType)
    {
        // Check if service type has repair orders
        if ($serviceType->repairOrders()->exists()) {
            return back()->with('error', __('{model} cannot be {action}. It has associated repair orders.', [
                'model'  => __('Service Type'),
                'action' => __('deleted'),
            ]));
        }

        if ($serviceType->{$serviceType->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Service Type'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}.', [
            'model'  => __('Service Type'),
            'action' => __('deleted'),
        ]));
    }
}
