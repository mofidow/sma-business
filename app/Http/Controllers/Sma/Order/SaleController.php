<?php

namespace App\Http\Controllers\Sma\Order;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Tec\Actions\SaveSale;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Setting\Tax;
use Nnjeim\World\Models\Country;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Order\SaleRequest;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Order/Sale/Index', [
            'custom_fields'   => CustomField::ofModel('sale')->get(),
            'address_fields'  => CustomField::ofModel('address')->get(),
            'payment_fields'  => CustomField::ofModel('payment')->get(),
            'delivery_fields' => CustomField::ofModel('delivery')->get(),
            'countries'       => Country::with('states:id,name,country_id')->get(),

            'pagination' => new Collection(
                Sale::with(['customer:id,name,company', 'delivery', 'user:id,name'])->withCount('returnOrders')->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Sma/Order/Sale/Form', [
            'taxes'           => Tax::all(),
            'custom_fields'   => CustomField::ofModel('sale')->get(),
            'customer_fields' => CustomField::ofModel('customer')->get(),
            'countries'       => Country::with('states:id,name,country_id')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaleRequest $request)
    {
        $sale = (new SaveSale)->execute($request->validated());

        return to_route($request->to_pos == 1 ? 'pos' : 'sales.index', ['sale' => $sale->id])
            ->with('message', __('{record} has been {action}.', ['record' => 'Sale', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Sale $sale)
    {
        $sale->loadCount('returnOrders');
        $sale->load([
            'attachments', 'items.variations', 'items.product',
            'store', 'customer', 'payments:id,date,amount,method,reference',
            'items.unit:id,code,name', 'user:id,name', 'address',
            'items.serials:id,number',
            'returnOrders:id,reference,date,grand_total,return_payment_amount,return_payment_method,sale_id',
        ]);

        if ($request->json) {
            return response()->json($sale);
        }

        return Inertia::render('Sma/Order/Sale/View', ['sale' => $sale]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        return Inertia::render('Sma/Order/Sale/Form', [
            'taxes'         => Tax::all(),
            'custom_fields' => CustomField::ofModel('sale')->get(),

            'current' => $sale->loadMissing([
                'attachments', 'items', 'items.variations',
                'items.product.variations', 'items.product.unit.subunits',
                'items.product.validPromotions', 'items.product.category.validPromotions',
                'items.serials:id,number',
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaleRequest $request, Sale $sale)
    {
        (new SaveSale)->execute($request->validated(), $sale);
        session()->flash('message', __('{record} has been {action}.', ['record' => 'Sale', 'action' => 'updated']));

        return $request->listing == 'yes' ? to_route('sales.index') : back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        if ($sale->{$sale->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('sales.index')->with('message', __('{record} has been {action}.', ['record' => 'Sale', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(Sale $sale)
    {
        $sale->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Sale', 'action' => 'restored']));
    }

    public function destroyPermanently(Sale $sale)
    {
        if ($sale->forceDelete()) {
            return to_route('sales.index')->with('message', __('{record} has been {action}.', ['record' => 'Sale', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
