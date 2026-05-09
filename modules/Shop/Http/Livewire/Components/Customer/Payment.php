<?php

namespace Modules\Shop\Http\Livewire\Components\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sma\Order\Payment as OrderPayment;

class Payment extends Component
{
    use WithPagination;

    public $payment_settings = [];

    public OrderPayment $payment;

    public function mount($id, $hash = null)
    {
        $this->payment_settings = get_settings('payment');
        $this->payment = OrderPayment::withoutGlobalScopes()
            ->with(['store', 'customer', 'user:id,name'])->find($id);
        if (auth()->user() && $this->payment->customer_id != auth()->user()->customer_id) {
            abort(404);
        } elseif (auth()->guest() && (! $this->payment || $this->payment->hash != $hash)) {
            abort(404);
        }
        $request = request();
        if ($request->type == 'pay' && $request->gateway == 'PayPal') {
            return redirect()->route('shop.paypal.pay', ['payment' => $this->payment->id]);
        }
    }

    public function render()
    {
        return view('shop::pages.customer.payment', [
            'settings' => get_public_settings(),
        ]);
    }
}
