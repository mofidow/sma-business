<?php

namespace Modules\Shop\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Sma\Order\Payment;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;
use Modules\Shop\Tec\Services\PaymentService;
use App\Tec\Notifications\Order\PaymentNotification;

class PayPalController extends Controller
{
    public function pay(Payment $payment)
    {
        // logger()->info('Stripe payment initiated', ['payment_id' => $payment->id, 'amount' => $payment->amount]);

        if ($payment->received) {
            $this->dispatch('notify',
                type: 'success',
                content: __('Payment is already marked as received.'),
            );

            return true;
        }

        try {
            $payment_settings = get_settings('payment');
            $payment_settings['gateway'] = 'PayPal_Rest';
            $currency_code = $payment_settings['default_currency'] ?? 'USD';

            $omni = new PaymentService($payment_settings, demo());

            $response = $omni->purchase([
                'currency'             => $currency_code,
                'transactionReference' => $payment->id,
                'amount'               => $payment->amount,
                'returnUrl'            => route('shop.paypal.completed', ['payment' => $payment->id]),
                'cancelUrl'            => URL::signedRoute('shop.payment', [
                    'gateway' => 'PayPal',
                    'type'    => 'cancel',
                    'id'      => $payment->id,
                    'hash'    => $payment->hash,
                ]),
            ]);

            if ($response->isRedirect()) {
                $response->redirect();
            } elseif (! $response->isSuccessful()) {
                logger()->error(print_r($response, true));
                throw new Exception($response->getMessage());
            }
        } catch (Exception $e) {
            logger(__('PayPal payment failed, :error', ['error' => $e->getMessage()]), ['trace' => $e->getTraceAsString()]);

            if (auth()->guest()) {
                $url = URL::signedRoute('shop.payment.guest', [
                    'gateway' => 'PayPal',
                    'type'    => 'error',
                    'id'      => $payment->id,
                    'hash'    => $payment->hash,
                ]);

                return redirect()->away($url);
            }

            return to_route('shop.payment', ['id' => $payment->id])
                ->with('error', __('We are unable to process your request, :error', ['error' => $e->getMessage()]));
        }
    }

    public function completed(Request $request, Payment $payment)
    {
        $error = null;
        if ($request->input('paymentId') && $request->input('PayerID')) {
            $payment_settings = get_settings('payment');
            $payment_settings['gateway'] = 'PayPal_Rest';

            $omni = new PaymentService($payment_settings, demo());

            $response = $omni->complete([
                'payer_id'             => $request->input('PayerID'),
                'transactionReference' => $request->input('paymentId'),
            ]);

            if ($response->isSuccessful()) {
                // logger()->info('PayPal payment successful', ['response' => $response->getData()]);

                $payment->update([
                    'received'    => true,
                    'method'      => 'PayPal',
                    'method_data' => [
                        'gateway'        => 'PayPal_Rest',
                        'transaction_id' => $response->getTransactionReference(),
                        'response'       => $response->getData(),
                    ],
                ]);

                $payment->customer->notify(new PaymentNotification($payment));

                session()->flash('success', __('Payment successful, your order has been updated.'));

                if ($payment->sale_id) {
                    if (auth()->guest()) {
                        $url = URL::signedRoute('shop.order.guest', [
                            'id'   => $payment->sale->id,
                            'hash' => $payment->sale->hash,
                        ]);

                        return redirect()->away($url);
                    }

                    return to_route('shop.order', ['id' => $payment->sale_id,
                    ]);
                }

                if (auth()->guest()) {
                    $url = URL::signedRoute('shop.payment.guest', [
                        'id'   => $payment->id,
                        'hash' => $payment->hash,
                    ]);

                    return redirect()->away($url);
                }

                return to_route('shop.payment', ['id' => $payment->id]);
            }
            $error = $response->getMessage();
        }
        if (auth()->guest()) {
            $url = URL::signedRoute('shop.payment.guest', [
                'gateway' => 'PayPal',
                'type'    => 'error',
                'id'      => $payment->id,
                'hash'    => $payment->hash,
            ]);

            return redirect()->away($url)->with('error', $error);
        }

        return to_route('shop.payment', ['id' => $payment->id])->with('error', $error);
    }
}
