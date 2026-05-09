<?php

namespace App\Http\Controllers\Sma\Order;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Tax;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Tec\Actions\SaveReturnOrder;
use App\Models\Sma\Order\ReturnOrder;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Order\ReturnOrderRequest;

class ReturnOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sale_id = $request->input('sale_id');
        $purchase_id = $request->input('purchase_id');
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Order/ReturnOrder/Index', [
            'custom_fields' => CustomField::ofModel('return_order')->get(),

            'pagination' => new Collection(
                ReturnOrder::with([
                    'user:id,name',
                    'customer:id,name,company',
                    'supplier:id,name,company',
                ])->when($sale_id, function ($query, $id) {
                    $query->where('sale_id', $id);
                })->when($purchase_id, function ($query, $id) {
                    $query->where('purchase_id', $id);
                })->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::render('Sma/Order/ReturnOrder/Form', [
            'taxes'         => Tax::all(),
            'sale_id'       => $request->input('sale_id'),
            'purchase_id'   => $request->input('purchase_id'),
            'custom_fields' => CustomField::ofModel('return_order')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReturnOrderRequest $request)
    {
        (new SaveReturnOrder)->execute($request->validated());

        return to_route('return_orders.index')->with('message', __('{record} has been {action}.', ['record' => 'ReturnOrder', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, ReturnOrder $return_order)
    {
        $return_order->load([
            'attachments', 'store', 'customer', 'supplier',
            'items.variations', 'items.product', 'items.unit:id,code,name',
        ]);

        if ($request->json) {
            return response()->json($return_order);
        }

        return Inertia::render('Sma/Order/ReturnOrder/Details', ['return_order' => $return_order]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReturnOrder $return_order)
    {
        return Inertia::render('Sma/Order/ReturnOrder/Form', [
            'taxes'         => Tax::all(),
            'custom_fields' => CustomField::ofModel('return_order')->get(),

            'current' => $return_order->loadMissing([
                'attachments', 'items', 'items.variations',
                'items.product.variations', 'items.product.unit.subunits',
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReturnOrderRequest $request, ReturnOrder $return_order)
    {
        (new SaveReturnOrder)->execute($request->validated(), $return_order);
        session()->flash('message', __('{record} has been {action}.', ['record' => 'ReturnOrder', 'action' => 'updated']));

        return $request->listing == 'yes' ? to_route('return_orders.index') : back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReturnOrder $return_order)
    {
        if ($return_order->{$return_order->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('return_orders.index')->with('message', __('{record} has been {action}.', ['record' => 'ReturnOrder', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(ReturnOrder $return_order)
    {
        $return_order->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'ReturnOrder', 'action' => 'restored']));
    }

    public function destroyPermanently(ReturnOrder $return_order)
    {
        if ($return_order->forceDelete()) {
            return to_route('return_orders.index')->with('message', __('{record} has been {action}.', ['record' => 'ReturnOrder', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
