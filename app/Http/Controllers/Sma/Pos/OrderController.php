<?php

namespace App\Http\Controllers\Sma\Pos;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Pos\Order;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Pos/Orders', [
            'pagination' => new Collection(
                Order::with('store:id,name')
                    ->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $order = Order::where('number', str($request->input('number'))->replace(['%', '"', "'"], ''))->first();

        $form = $request->validate([
            'number'           => 'required',
            'reference'        => 'required|unique:orders,reference,' . $order?->id,
            'user_id'          => 'nullable',
            'customer_id'      => 'required',
            'items'            => 'required|min:1',
            'total'            => 'required|numeric',
            'total_items'      => 'required|numeric',
            'total_quantity'   => 'required|numeric',
            'hall_id'          => 'nullable|exists:halls,id',
            'table_id'         => 'nullable|exists:tables,id',
            'reference_number' => 'nullable|string|max:255',
            'guests'           => 'nullable|integer|min:1',
            'notes'            => 'nullable',
        ], ['items.required' => __('Please add at least one item to the order.')]);

        $form['data'] = $request->all();
        unset($form['items']);

        if ($order?->id) {
            $order->update($form);
        } else {
            Order::create($form);
        }

        return response()->json(['success' => true, 'message' => __('{record} has been {action}.', ['record' => 'Order', 'action' => 'saved'])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $number)
    {
        $order = Order::where('number', $number)->firstOrFail();

        if ($order->forceDelete()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => __('{record} has been {action}.', ['record' => 'Order', 'action' => 'deleted'])]);
            }

            return back()->with('message', __('{record} has been {action}.', ['record' => 'Order', 'action' => 'deleted']));
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => __('The record can not be deleted.')]);
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function checkReference(Request $request)
    {
        $request->validate([
            'reference' => 'required|unique:orders,reference',
        ]);

        return response()->json(['success' => true]);
    }
}
