<?php

namespace Modules\Shop\Http\Livewire\Components\Payments;

use Exception;
use Livewire\Component;
use App\Models\Sma\Order\Payment;
use Illuminate\Support\Facades\URL;
use Modules\Shop\Tec\Services\PaymentService;
use App\Tec\Notifications\Order\PaymentNotification;

class Stripe extends Component
{
    public $token;

    public Payment $payment;

    public $payment_settings = [];

    public function mount($payment, $payment_settings)
    {
        $this->payment = $payment;
        $this->payment_settings = $payment_settings;
    }

    public function render()
    {
        return view('shop::components.payments.stripe');
    }

    public function pay()
    {
        // logger()->info('Stripe payment initiated', ['token' => $this->token, 'payment_id' => $this->payment->id, 'amount' => $this->payment->amount]);

        if ($this->payment->received) {
            $this->dispatch('notify',
                type: 'success',
                content: __('Payment is already marked as received.'),
            );

            return true;
        }

        $currency_code = $this->payment_settings['default_currency'] ?? 'USD';
        $omni = new PaymentService($this->payment_settings, demo());

        try {
            if (! $this->token) {
                $this->dispatch('notify',
                    type: 'error',
                    content: __('We are unable to process your request, token is missing.'),
                );

                return false;
            }
            $response = $omni->purchase([
                'token'    => $this->token,
                'currency' => $currency_code,
                'amount'   => $this->payment->amount,
            ]);

            if ($response->isSuccessful()) {
                // logger()->info('Stripe payment successful', ['response' => $response->getData()]);

                $this->payment->update([
                    'received'    => true,
                    'method'      => 'Credit Card',
                    'method_data' => [
                        'gateway'        => 'Stripe',
                        'transaction_id' => $response->getTransactionReference(),
                        'response'       => $response->getData(),
                    ],
                ]);

                $this->payment->customer->notify(new PaymentNotification($this->payment));

                $this->dispatch('notify',
                    type: 'success',
                    content: __('Payment successful, your order has been updated.'),
                );
                session()->flash('success', __('Payment successful, your order has been updated.'));

                if ($this->payment->sale_id) {
                    if (auth()->guest()) {
                        $url = URL::signedRoute('shop.order.guest', [
                            'id'   => $this->payment->sale->id,
                            'hash' => $this->payment->sale->hash,
                        ]);

                        return redirect()->away($url);
                    }

                    return to_route('shop.order', ['id' => $this->payment->sale_id,
                    ]);
                }

                if (auth()->guest()) {
                    $url = URL::signedRoute('shop.payment.guest', [
                        'id'   => $this->payment->id,
                        'hash' => $this->payment->hash,
                    ]);

                    return redirect()->away($url);
                }

                return to_route('shop.payment', ['id' => $this->payment->id]);
            }
            throw new Exception($response->getMessage());
        } catch (Exception $e) {
            logger(__('Stripe payment failed, :error', ['error' => $e->getMessage()]), ['trace' => $e->getTraceAsString()]);
            $this->dispatch('notify',
                type: 'error',
                content: __('We are unable to process your request, :error', ['error' => $e->getMessage()]),
            );
        }
    }
}
