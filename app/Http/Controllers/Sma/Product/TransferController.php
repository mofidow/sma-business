<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Setting\Store;
use App\Tec\Actions\SaveTransfer;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Transfer;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Product\TransferRequest;

class TransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters');

        return Inertia::render('Sma/Product/Transfer/Index', [
            'custom_fields' => CustomField::ofModel('transfer')->get(),

            'pagination' => new Collection(
                Transfer::with('toStore')->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Sma/Product/Transfer/Form', [
            'stores'        => Store::active()->get(),
            'custom_fields' => CustomField::ofModel('transfer')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransferRequest $request)
    {
        (new SaveTransfer)->execute($request->validated());

        return to_route('transfers.index')->with('message', __('{record} has been {action}.', ['record' => 'Transfer', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Transfer $transfer)
    {
        $transfer->load(['attachments', 'items.variations', 'items.product', 'store', 'toStore']);

        if ($request->json) {
            return response()->json($transfer);
        }

        return Inertia::render('Sma/Product/Transfer/Details', ['transfer' => $transfer]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transfer $transfer)
    {
        return Inertia::render('Sma/Product/Transfer/Form', [
            'stores'        => Store::active()->get(),
            'custom_fields' => CustomField::ofModel('transfer')->get(),

            'current' => $transfer->loadMissing([
                'attachments', 'items', 'items.product.variations', 'items.variations',
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransferRequest $request, Transfer $transfer)
    {
        (new SaveTransfer)->execute($request->validated(), $transfer);
        session()->flash('message', __('{record} has been {action}.', ['record' => 'Transfer', 'action' => 'updated']));

        return $request->listing == 'yes' ? to_route('transfers.index') : back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transfer $transfer)
    {
        if ($transfer->{$transfer->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('transfers.index')->with('message', __('{record} has been {action}.', ['record' => 'Transfer', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(Transfer $transfer)
    {
        $transfer->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Transfer', 'action' => 'restored']));
    }

    public function destroyPermanently(Transfer $transfer)
    {
        if ($transfer->forceDelete()) {
            return to_route('transfers.index')->with('message', __('{record} has been {action}.', ['record' => 'Transfer', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
