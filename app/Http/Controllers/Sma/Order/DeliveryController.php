<?php

namespace App\Http\Controllers\Sma\Order;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Tec\Actions\SaveDelivery;
use App\Http\Resources\Collection;
use App\Models\Sma\Order\Delivery;
use App\Http\Controllers\Controller;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Order\DeliveryRequest;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Order/Delivery/Index', [
            'custom_fields' => CustomField::ofModel('delivery')->get(),

            'pagination' => new Collection(
                Delivery::with(['address', 'customer:id,name,company', 'sale:id,reference'])->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DeliveryRequest $request)
    {
        (new SaveDelivery)->execute($request->validated());

        return to_route('deliveries.index')->with('message', __('{record} has been {action}.', ['record' => 'Delivery', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Delivery $delivery)
    {
        $delivery->load(['address', 'store', 'sale:id,reference', 'customer', 'user:id,name']);

        if ($request->json) {
            return response()->json($delivery);
        }

        return Inertia::render('Sma/Order/Delivery/Details', ['delivery' => $delivery]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DeliveryRequest $request, Delivery $delivery)
    {
        (new SaveDelivery)->execute($request->validated(), $delivery);
        session()->flash('message', __('{record} has been {action}.', ['record' => 'Delivery', 'action' => 'updated']));

        return $request->listing == 'yes' ? to_route('deliveries.index') : back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Delivery $delivery)
    {
        if ($delivery->{$delivery->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('deliveries.index')->with('message', __('{record} has been {action}.', ['record' => 'Delivery', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(Delivery $delivery)
    {
        $delivery->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Delivery', 'action' => 'restored']));
    }

    public function destroyPermanently(Delivery $delivery)
    {
        if ($delivery->forceDelete()) {
            return to_route('deliveries.index')->with('message', __('{record} has been {action}.', ['record' => 'Delivery', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
