<?php

namespace App\Http\Controllers\Sma\Order;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Tax;
use Nnjeim\World\Models\Country;
use App\Models\Sma\Setting\Store;
use App\Tec\Actions\SavePurchase;
use App\Http\Resources\Collection;
use App\Models\Sma\Order\Purchase;
use App\Http\Controllers\Controller;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Order\PurchaseRequest;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Order/Purchase/Index', [
            'payment_fields' => CustomField::ofModel('payment')->get(),
            'custom_fields'  => CustomField::ofModel('purchase')->get(),

            'pagination' => new Collection(
                Purchase::with(['supplier:id,name,company', 'user:id,name'])->withCount('returnOrders')->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Sma/Order/Purchase/Form', [
            'taxes'           => Tax::all(),
            'custom_fields'   => CustomField::ofModel('purchase')->get(),
            'supplier_fields' => CustomField::ofModel('supplier')->get(),
            'countries'       => Country::with('states:id,name,country_id')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseRequest $request)
    {
        (new SavePurchase)->execute($request->validated());

        return to_route('purchases.index')->with('message', __('{record} has been {action}.', ['record' => 'Purchase', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Purchase $purchase)
    {
        $purchase->loadCount('returnOrders');
        $purchase->load([
            'attachments', 'store', 'supplier',
            'payments:id,date,amount,method,reference',
            'items.variations', 'items.product', 'items.unit:id,code,name',
            'items.serials:id,number',
            'returnOrders:id,reference,date,grand_total,return_payment_amount,return_payment_method,purchase_id',
        ]);

        if ($request->json) {
            return response()->json($purchase);
        }

        return Inertia::render('Sma/Order/Purchase/Details', ['purchase' => $purchase]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        return Inertia::render('Sma/Order/Purchase/Form', [
            'taxes'         => Tax::all(),
            'custom_fields' => CustomField::ofModel('purchase')->get(),

            'current' => $purchase->loadMissing([
                'attachments', 'items', 'items.variations',
                'items.product.variations', 'items.product.unit.subunits',
                'items.serials:id,number',
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PurchaseRequest $request, Purchase $purchase)
    {
        (new SavePurchase)->execute($request->validated(), $purchase);
        session()->flash('message', __('{record} has been {action}.', ['record' => 'Purchase', 'action' => 'updated']));

        return $request->listing == 'yes' ? to_route('purchases.index') : back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        if ($purchase->{$purchase->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('purchases.index')->with('message', __('{record} has been {action}.', ['record' => 'Purchase', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(Purchase $purchase)
    {
        $purchase->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Purchase', 'action' => 'restored']));
    }

    public function destroyPermanently(Purchase $purchase)
    {
        if ($purchase->forceDelete()) {
            return to_route('purchases.index')->with('message', __('{record} has been {action}.', ['record' => 'Purchase', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
