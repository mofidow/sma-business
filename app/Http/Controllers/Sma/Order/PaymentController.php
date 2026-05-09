<?php

namespace App\Http\Controllers\Sma\Order;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Sma\Order\Payment;
use App\Http\Resources\Collection;
use App\Http\Controllers\Controller;
use App\Models\Sma\Setting\CustomField;
use App\Http\Requests\Sma\Order\PaymentRequest;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filters') ?? [];

        return Inertia::render('Sma/Order/Payment/Index', [
            'custom_fields' => CustomField::ofModel('payment')->get(),

            'pagination' => new Collection(
                Payment::with([
                    'sales:id,reference', 'purchases:id,reference',
                    'customer:id,company,name', 'supplier:id,company,name', 'user:id,name',
                ])->filter($filters)->latest()->paginate()->withQueryString()
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {
        Payment::create($request->validated());

        return to_route('payments.index')->with('message', __('{record} has been {action}.', ['record' => 'Payment', 'action' => 'created']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Payment $payment)
    {
        $payment->load(['store', 'customer', 'supplier', 'user:id,name', 'sales:id,reference', 'purchases:id,reference']);

        if ($request->json) {
            return response()->json($payment);
        }

        return Inertia::render('Sma/Order/Payment/Details', ['payment' => $payment]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentRequest $request, Payment $payment)
    {
        $payment->update($request->validated());
        session()->flash('message', __('{record} has been {action}.', ['record' => 'Payment', 'action' => 'updated']));

        return $request->listing == 'yes' ? to_route('payments.index') : back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        if ($payment->{$payment->deleted_at ? 'forceDelete' : 'delete'}()) {
            return to_route('payments.index')->with('message', __('{record} has been {action}.', ['record' => 'Payment', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function restore(Payment $payment)
    {
        $payment->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Payment', 'action' => 'restored']));
    }

    public function destroyPermanently(Payment $payment)
    {
        if ($payment->forceDelete()) {
            return to_route('payments.index')->with('message', __('{record} has been {action}.', ['record' => 'Payment', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }
}
