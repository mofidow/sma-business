<?php

namespace App\Http\Controllers\Sma\Pos;

use Illuminate\Http\Request;
use App\Models\Sma\Pos\Register;
use App\Http\Controllers\Controller;

class PosRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function details(Request $request, Register $register)
    {
        $user = $request->user();

        if ($register?->id && ! $user->hasRole('Super Admin')) {
            abort(403);
        }

        if (! $register?->id) {
            $register = $request->user()->openedRegister()->without(['store', 'user'])->first();
        }

        if ($register?->id) {
            $register = $this->getRegisterWithDetails($register->id);

            return response()->json(['success' => true, 'register' => $register]);
        }

        return response()->json(['success' => false, 'register' => null]);
    }

    public function close(Request $request, Register $register)
    {
        if ($register?->id && ! $request->user()->hasRole('Super Admin')) {
            abort(403);
        }

        $form = $request->validate([
            'note'                      => 'nullable',
            'cash_in_register'          => 'nullable|numeric',
            'cash_submitted'            => 'nullable|numeric',
            'cc_payments_submitted'     => 'nullable|numeric',
            'stripe_payments_submitted' => 'nullable|numeric',
            'other_payments_submitted'  => 'nullable|numeric',
        ]);

        $form['cash_in_register']          = $form['cash_in_register'] ?? 0;
        $form['cash_submitted']            = $form['cash_submitted'] ?? 0;
        $form['cc_payments_submitted']     = $form['cc_payments_submitted'] ?? 0;
        $form['stripe_payments_submitted'] = $form['stripe_payments_submitted'] ?? 0;
        $form['other_payments_submitted']  = $form['other_payments_submitted'] ?? 0;

        if (! $register?->id) {
            $register = $request->user()->openedRegister()->without('store')->first();
        }

        if ($register?->id) {
            $register_details = $this->getRegisterWithDetails($register->id);

            $form['closed_at'] = now();
            $form['closed_by'] = $request->user()->id;
            $form['cash_amount'] = $register_details->cash_payments;
            $form['cc_payments_amount'] = $register_details->cc_payments;
            $form['total_sales'] = $register_details->sales_sum_grand_total;
            $form['total_expenses'] = $register_details->expenses_sum_amount;
            $form['gift_card_amount'] = $register_details->gift_card_payments;
            $form['other_payments_amount'] = $register_details->other_payments;
            $form['stripe_payments_amount'] = $register_details->stripe_payments;
            $form['total_purchases'] = $register_details->purchases_sum_grand_total;

            $register->update($form);
            session()->forget('open_register_id');
            session()->flash('message', __('The register has been closed!'));

            return response()->json(['success' => true, 'message' => __('The register has been closed!')]);
        }

        return response()->json(['success' => false, 'error' => __('No register is open!')], 422);

        return response()->json(['success' => false, 'error' => __('Unable to close register!')], 422);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $register = $user->openedRegister()->with('store')->first();

        if ($register) {
            session(['open_register_id' => $register->id]);

            return back()->with('message', __('Register is already opened at store ' . $register->store->name));
        }

        $form = $request->validate(['cash_in_hand' => 'required|numeric|min:0']);

        $register = $user->registers()->create([
            'cash_in_hand' => $form['cash_in_hand'],
            'store_id'     => session('selected_store_id'),
        ]);

        session(['open_register_id' => $register->id]);

        return back()->with('message', __('Register is opened at store ' . $register->store->name));
    }

    protected function getRegisterWithDetails($id)
    {
        return Register::withoutGlobalScope('of_store')->without(['store', 'user'])
            ->withSum(['expenses' => fn ($q) => $q->withoutGlobalScope('of_store')], 'amount')
            ->withSum(['sales' => fn ($q) => $q->withoutGlobalScope('of_store')], 'grand_total')
            ->withSum(['purchases' => fn ($q) => $q->withoutGlobalScope('of_store')], 'grand_total')
            ->withSum(['payments as cash_payments' => fn ($q) => $q->withoutGlobalScope('of_store')->where('method', 'Cash')], 'amount')
            ->withSum(['payments as other_payments' => fn ($q) => $q->withoutGlobalScope('of_store')->where('method', 'Others')], 'amount')
            ->withSum(['payments as cc_payments' => fn ($q) => $q->withoutGlobalScope('of_store')->where('method', 'Card Terminal')], 'amount')
            ->withSum(['payments as gift_card_payments' => fn ($q) => $q->withoutGlobalScope('of_store')->where('method', 'Gift Card')], 'amount')
            ->withSum(['payments as stripe_payments' => fn ($q) => $q->withoutGlobalScope('of_store')->where('method', 'Stripe Terminal')], 'amount')
            ->find($id);
    }
}
