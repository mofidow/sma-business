<?php

namespace Modules\Shop\Http\Livewire\Components\Payments;

use Livewire\Component;
use App\Models\Sma\Order\Payment;

class Others extends Component
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
        return view('shop::components.payments.others');
    }

    public function pay()
    {
        logger()->info('Stripe payment initiated', ['token' => $this->token, 'payment_id' => $this->payment->id, 'amount' => $this->payment->amount]);
    }
}
