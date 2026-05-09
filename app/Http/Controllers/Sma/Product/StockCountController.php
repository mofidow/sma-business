<?php

namespace App\Http\Controllers\Sma\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Product\Brand;
use App\Http\Resources\Collection;
use App\Tec\Jobs\AdjustStoreStock;
use App\Http\Controllers\Controller;
use App\Models\Sma\Product\Category;
use Maatwebsite\Excel\Facades\Excel;
use App\Tec\Exports\StockCountExport;
use App\Models\Sma\Product\StockCount;
use App\Models\Sma\Setting\CustomField;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Sma\Product\StockCountRequest;

class StockCountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters');

        return Inertia::render('Sma/Product/StockCount/Index', [
            'custom_fields' => CustomField::ofModel('stock_count')->get(),

            'pagination' => new Collection(
                StockCount::filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Sma/Product/StockCount/Form', [
            'brands'        => Brand::active()->get(['id', 'name']),
            'custom_fields' => CustomField::ofModel('stock_count')->get(),
            'categories'    => Category::active()->onlyParent()->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockCountRequest $request)
    {
        StockCount::create($request->validated());

        return to_route('stock_counts.index')->with('message', __('{record} has been {action}.', ['record' => 'StockCount', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, StockCount $stock_count)
    {
        $stock_count->load(['attachments', 'completedBy', 'items', 'store']);

        if (! empty($stock_count->brands)) {
            $stock_count->brands = Brand::whereIn('id', $stock_count->brands)->get(['id', 'name']);
        }

        if (! empty($stock_count->categories)) {
            $stock_count->categories = Category::whereIn('id', $stock_count->categories)->get(['id', 'name']);
        }

        if ($request->json) {
            return response()->json($stock_count);
        }

        return Inertia::render('Sma/Product/StockCount/Details', ['stock_count' => $stock_count]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockCount $stock_count)
    {
        if ($stock_count->completed_at) {
            return to_route('stock_counts.index')->with('info', __('{record} has been {action}.', ['record' => 'Stock count', 'action' => 'completed']));
        }

        return Inertia::render('Sma/Product/StockCount/Form', [
            'brands'        => Brand::active()->get(['id', 'name']),
            'custom_fields' => CustomField::ofModel('stock_count')->get(),
            'categories'    => Category::active()->onlyParent()->get(['id', 'name']),

            'current' => $stock_count->loadMissing(['attachments']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StockCountRequest $request, StockCount $stock_count)
    {
        $stock_count->update($request->validated());
        session()->flash('message', __('{record} has been {action}.', ['record' => 'StockCount', 'action' => 'updated']));

        return to_route('stock_counts.index')->with('message', __('{record} has been {action}.', ['record' => 'StockCount', 'action' => 'updated']));
    }

    /**
     * Export the excel file.
     */
    public function download(StockCount $stock_count)
    {
        return Storage::disk('local')
            ->download($stock_count->file, 'stock_count_' . $stock_count->id . '.xlsx');
    }

    /**
     * Export the excel file.
     */
    public function adjust(StockCount $stock_count)
    {
        if ($stock_count->completed_at && ! $stock_count->adjusted_at) {
            AdjustStoreStock::dispatch($stock_count, auth()->user());

            return to_route('stock_counts.index')->with('message', __('{record} has been {action}.', ['record' => 'Stock count', 'action' => 'initiated']));
        }

        return back()->with('error', __('{record} has been {action}.', ['record' => 'Action', 'action' => 'failed']));
    }

    /**
     * Export the excel file.
     */
    public function export(StockCount $stock_count)
    {
        Excel::store(new StockCountExport($stock_count), 'stock_counts/' . $stock_count->id . '.xlsx');

        return Storage::disk('local')->download('stock_counts/' . $stock_count->id . '.xlsx', 'stock_count_' . $stock_count->id . '.xlsx');
        // return (new StockCountExport($stock_count))
        //     ->download('stock_count_' . $stock_count->id . '.xlsx');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockCount $stock_count)
    {
        if ($stock_count->{$stock_count->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('stock_counts.index')->with('message', __('{record} has been {action}.', ['record' => 'StockCount', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(StockCount $stock_count)
    {
        $stock_count->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'StockCount', 'action' => 'restored']));
    }

    public function destroyPermanently(StockCount $stock_count)
    {
        if ($stock_count->forceDelete()) {
            return to_route('stock_counts.index')->with('message', __('{record} has been {action}.', ['record' => 'StockCount', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
