<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Tec\Actions\SaveAdjustment;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Adjustment;
use App\Models\Sma\Setting\CustomField;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Sma\Product\AdjustmentRequest;

class AdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters');

        return Inertia::render('Sma/Product/Adjustment/Index', [
            'custom_fields' => CustomField::ofModel('adjustment')->get(),

            'pagination' => new Collection(
                Adjustment::filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Sma/Product/Adjustment/Form', [
            'custom_fields' => CustomField::ofModel('adjustment')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdjustmentRequest $request)
    {
        (new SaveAdjustment)->execute($request->validated());

        return to_route('adjustments.index')->with('message', __('{record} has been {action}.', ['record' => 'Adjustment', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Adjustment $adjustment)
    {
        $adjustment->load(['attachments', 'items.variations', 'items.product', 'store']);

        if ($request->json) {
            return response()->json($adjustment);
        }

        return Inertia::render('Sma/Product/Adjustment/Details', ['adjustment' => $adjustment]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Adjustment $adjustment)
    {
        return Inertia::render('Sma/Product/Adjustment/Form', [
            'custom_fields' => CustomField::ofModel('adjustment')->get(),

            'current' => $adjustment->loadMissing([
                'attachments', 'items', 'items.product.variations', 'items.variations',
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdjustmentRequest $request, Adjustment $adjustment)
    {
        (new SaveAdjustment)->execute($request->validated(), $adjustment);
        session()->flash('message', __('{record} has been {action}.', ['record' => 'Adjustment', 'action' => 'updated']));

        return $request->listing == 'yes' ? to_route('adjustments.index') : back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Adjustment $adjustment)
    {
        if ($adjustment->{$adjustment->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('adjustments.index')->with('message', __('{record} has been {action}.', ['record' => 'Adjustment', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(Adjustment $adjustment)
    {
        $adjustment->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Adjustment', 'action' => 'restored']));
    }

    public function destroyPermanently(Adjustment $adjustment)
    {
        if ($adjustment->forceDelete()) {
            return to_route('adjustments.index')->with('message', __('{record} has been {action}.', ['record' => 'Adjustment', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
