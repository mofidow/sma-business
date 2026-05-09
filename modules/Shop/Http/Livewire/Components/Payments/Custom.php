<?php

namespace Modules\Shop\Http\Livewire\Components\Payments;

use Exception;
use Livewire\Component;
use App\Models\Sma\Order\Payment;
use Illuminate\Support\Facades\URL;
use Plugins\Payments\PaymentMethods;
use Plugins\Payments\Contracts\PaymentContext;
use App\Tec\Notifications\Order\PaymentNotification;

class Custom extends Component
{
    public $token;

    public $form = [];

    public $rules = [];

    public $fields = [];

    public Payment $payment;

    public $payment_settings = [];

    public function mount($payment, $payment_settings)
    {
        $this->payment = $payment;
        $this->payment_settings = $payment_settings;
        $method = PaymentMethods::find($payment_settings['gateway'] ?? '');

        if (! $method) {
            throw new Exception('Payment method not found');
        }

        $this->fields = $method->gatewayFields();
        foreach ($this->fields as $key => $value) {
            $this->rules['form.' . $key] = $value['required'] ? 'required' : 'nullable';
        }
    }

    public function render()
    {
        return view('shop::components.payments.custom');
    }

    public function pay()
    {
        $this->validate();
        // logger()->info('Custom payment initiated', ['payment_id' => $this->payment->id, 'amount' => $this->payment->amount]);

        if ($this->payment->received) {
            $this->dispatch('notify',
                type: 'success',
                content: __('Payment is already marked as received.'),
            );

            return true;
        }

        try {
            $currency_code = $this->payment_settings['default_currency'] ?? 'USD';
            $content = new PaymentContext(
                sale: $this->payment->sale,
                amount: $this->payment->amount * 100,
                currency: $currency_code,
                form: $this->form,
                metadata: [
                    'payment_id' => $this->payment->id,
                ],
                returnUrl: route('shop.payment', ['id' => $this->payment->id]),
            );

            $method = PaymentMethods::find($this->payment_settings['gateway'] ?? '');
            if (! $method) {
                throw new Exception('Payment method not found');
            }

            $response = $method->purchase($content);

            if ($response->isSuccessful()) {
                $this->payment->update([
                    'received'    => true,
                    'method'      => $method->displayName(),
                    'method_data' => [
                        'gateway'        => $method->displayName(),
                        'transaction_id' => $response->providerReference(),
                        'response'       => $response->payload(),
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
            throw new Exception($response->message() ?? 'Payment failed');
        } catch (Exception $e) {
            logger(__(':Method payment failed, :error', ['error' => $e->getMessage(), 'method' => $method->displayName() ?? 'Unknown']), ['trace' => $e->getTraceAsString()]);
            $this->dispatch('notify',
                type: 'error',
                content: __('We are unable to process your request, :error', ['error' => $e->getMessage()]),
            );
        }
    }
}
