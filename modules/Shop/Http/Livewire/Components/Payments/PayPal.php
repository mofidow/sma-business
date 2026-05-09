<?php

namespace Modules\Shop\Http\Livewire\Components\Payments;

use Livewire\Component;
use App\Models\Sma\Order\Payment;

class PayPal extends Component
{
    public Payment $payment;

    public $payment_settings = [];

    public function mount($payment, $payment_settings)
    {
        $this->payment = $payment;
        $this->payment_settings = $payment_settings;
    }

    public function render()
    {
        return view('shop::components.payments.paypal');
    }
}
