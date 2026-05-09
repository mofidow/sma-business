<?php

namespace App\Http\Controllers\Sma\Setting;

use Inertia\Inertia;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Plugins\Payments\PaymentMethods;

class PaymentController extends Controller
{
    public function index()
    {
        $payment_methods = PaymentMethods::settingsFields();

        return Inertia::render('Sma/Setting/Payment', [
            'payment_methods' => $payment_methods,
            'current'         => get_settings('payment'),
        ]);
    }

    public function store(Request $request)
    {
        $payment_methods = PaymentMethods::settingsFields();
        $rules = [];
        foreach ($payment_methods as $method => $data) {
            foreach ($data['fields'] as $field => $info) {
                $rules[$info['config']] = $info['rules'];
            }
        }

        $form = $request->validate($rules + [
            'default_currency' => 'required',
            'gateway'          => 'nullable',
            'stripe_terminal'  => 'nullable|boolean',

            'static_payment_methods'   => 'nullable|array',
            'static_payment_methods.*' => 'nullable|string|max:50',

            'services' => 'nullable|array',

            'services.paypal'                 => 'nullable|array',
            'services.paypal.enabled'         => 'nullable|boolean',
            'services.paypal.client_id'       => 'nullable|string|required_if:gateway,PayPal Rest',
            'services.paypal.secret'          => 'nullable|string|required_if:gateway,PayPal Rest',
            'services.paypal.fixed'           => 'nullable|numeric',
            'services.paypal.same_country'    => 'nullable|numeric',
            'services.paypal.other_countries' => 'nullable|numeric',

            'services.stripe'                 => 'nullable|array',
            'services.stripe.enabled'         => 'nullable|boolean',
            'services.stripe.key'             => 'nullable|string|required_if:gateway,Stripe',
            'services.stripe.secret'          => 'nullable|string|required_if:gateway,Stripe',
            'services.stripe.fixed'           => 'nullable|numeric',
            'services.stripe.same_country'    => 'nullable|numeric',
            'services.stripe.other_countries' => 'nullable|numeric',

            'services.*' => 'nullable',
        ]);

        if (demo()) {
            return back()->with('error', __('Changes are not allowed in demo mode.'));
        }

        Setting::updateOrCreate(['tec_key' => 'currency_code'], ['tec_value' => $form['default_currency']]);
        Setting::updateOrCreate(['tec_key' => 'payment'], ['tec_value' => json_encode($form)]);

        return back()->with('message', __('Payment settings successfully saved.'));
    }
}
