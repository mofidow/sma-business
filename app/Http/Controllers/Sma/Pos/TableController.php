<?php

namespace App\Http\Controllers\Sma\Pos;

use Inertia\Inertia;
use App\Models\Sma\Pos\Hall;
use Illuminate\Http\Request;
use App\Models\Sma\Pos\Order;
use App\Models\Sma\Pos\Table;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sma\Pos\TableRequest;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters');

        if (! ($filters['store_id'] ?? null) && session('selected_store_id', null)) {
            $filters['store_id'] = session('selected_store_id');
        }

        return Inertia::render('Sma/Pos/Table/Index', [
            'halls'      => Hall::ofStore()->active()->ordered()->get(['id', 'name']),
            'pagination' => new Collection(
                Table::with('hall:id,name')
                    ->filter($filters)
                    ->latest()->orderBy('sort_order')
                    ->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TableRequest $request)
    {
        Table::create($request->validated());

        return redirect()->route('pos.tables.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Table'),
                'action' => __('created'),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Table $table)
    {
        return $table->load('hall:id,name', 'currentOrder');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TableRequest $request, Table $table)
    {
        $table->update($request->validated());

        return redirect()->route('pos.tables.index')
            ->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Table'),
                'action' => __('updated'),
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Table $table)
    {
        if ($table->{$table->deleted_at ? 'forceDelete' : 'delete'}()) {
            return back()->with('message', __('{model} has been successfully {action}.', [
                'model'  => __('Table'),
                'action' => __('deleted'),
            ]));
        }

        return back()->with('error', __('{model} cannot be {action}. The record is being used for relationships.', [
            'model'  => __('Table'),
            'action' => __('deleted'),
        ]));
    }

    /**
     * Get available tables for POS.
     */
    public function available(Request $request)
    {
        if ($request->has('table_id')) {
            $order = Order::whereJsonContains('data->table_id', $request->input('table_id'))->first();

            return $order ? response()->json(['available' => false, 'order' => $order]) : response()->json(['available' => true]);
        }

        $query = Table::query()
            ->with('hall:id,name')
            ->ofStore()->active()->available();

        if ($request->has('hall_id')) {
            $query->ofHall($request->input('hall_id'));
        }

        return $query->ordered()->get(['id', 'hall_id', 'name', 'seats']);
    }
}
