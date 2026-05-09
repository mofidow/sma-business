<?php

namespace App\Http\Controllers\Sma\Order;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Tax;
use Nnjeim\World\Models\Country;
use App\Http\Resources\Collection;
use App\Tec\Actions\SaveQuotation;
use App\Models\Sma\Order\Quotation;
use App\Http\Controllers\Controller;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Order\QuotationRequest;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Order/Quotation/Index', [
            'custom_fields' => CustomField::ofModel('quotation')->get(),

            'pagination' => new Collection(
                Quotation::with(['customer:id,name,company', 'user:id,name'])->withCount('sales')->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Sma/Order/Quotation/Form', [
            'taxes'           => Tax::all(),
            'custom_fields'   => CustomField::ofModel('quotation')->get(),
            'customer_fields' => CustomField::ofModel('customer')->get(),
            'countries'       => Country::with('states:id,name,country_id')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuotationRequest $request)
    {
        (new SaveQuotation)->execute($request->validated());

        return to_route('quotations.index')->with('message', __('{record} has been {action}.', ['record' => 'Quotation', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Quotation $quotation)
    {
        $quotation->loadCount('sales');
        $quotation->load([
            'attachments', 'store', 'customer',
            'items.variations', 'items.product', 'items.unit:id,code,name',
        ]);

        if ($request->json) {
            return response()->json($quotation);
        }

        return Inertia::render('Sma/Order/Quotation/Details', ['quotation' => $quotation]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quotation $quotation)
    {
        return Inertia::render('Sma/Order/Quotation/Form', [
            'taxes'         => Tax::all(),
            'custom_fields' => CustomField::ofModel('quotation')->get(),

            'current' => $quotation->loadMissing([
                'attachments', 'items', 'items.variations',
                'items.product.variations', 'items.product.unit.subunits',
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuotationRequest $request, Quotation $quotation)
    {
        (new SaveQuotation)->execute($request->validated(), $quotation);
        session()->flash('message', __('{record} has been {action}.', ['record' => 'Quotation', 'action' => 'updated']));

        return $request->listing == 'yes' ? to_route('quotations.index') : back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quotation $quotation)
    {
        if ($quotation->{$quotation->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('quotations.index')->with('message', __('{record} has been {action}.', ['record' => 'Quotation', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(Quotation $quotation)
    {
        $quotation->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Quotation', 'action' => 'restored']));
    }

    public function destroyPermanently(Quotation $quotation)
    {
        if ($quotation->forceDelete()) {
            return to_route('quotations.index')->with('message', __('{record} has been {action}.', ['record' => 'Quotation', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
