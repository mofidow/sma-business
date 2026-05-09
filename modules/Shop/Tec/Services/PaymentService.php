<?php

namespace Modules\Shop\Tec\Services;

use Omnipay\Omnipay;

class PaymentService
{
    public $gateway;

    public function __construct(public $payment_settings = [], $testMode = false)
    {
        $this->gateway = Omnipay::create($payment_settings['gateway'] ?? '');
        if ($payment_settings['gateway'] == 'Stripe') {
            $this->gateway->setApiKey(config('services.stripe.secret', true));
        } elseif ($payment_settings['gateway'] == 'TillPayments') {
            $this->gateway->setApiKey(config('services.tillpayments.api_key', true));
            $this->gateway->setSharedSecret(config('services.tillpayments.shared_secret', true));
            $this->gateway->setPublicKey(config('services.tillpayments.public_key', true));
            $this->gateway->setUsername(config('services.tillpayments.username', true));
            $this->gateway->setPassword(config('services.tillpayments.password', true));
        } elseif ($payment_settings['gateway'] == 'PayPal_Pro') {
            $this->gateway->setUsername(config('services.paypal.username', true));
            $this->gateway->setPassword(config('services.paypal.password', true));
            $this->gateway->setSignature(config('services.paypal.signature', true));
        } elseif ($payment_settings['gateway'] == 'AuthorizeNetApi_Api') {
            $this->gateway->setAuthName(config('services.authorize.login', true));
            $this->gateway->setTransactionKey(config('services.authorize.transaction_key', true));
        } elseif ($payment_settings['gateway'] == 'PayPal_Rest') {
            $this->gateway->setSecret(config('services.paypal.secret', true));
            $this->gateway->setClientId(config('services.paypal.client_id', true));
        } elseif ($payment_settings['gateway'] == 'Paymes') {
            $this->gateway->setSecretKey(config('services.paymes.secret_key', true));
            $this->gateway->setPublicKey(config('services.paymes.public_key', true));
        } else {
            abort(500, 'Unknown payment gateway!');
        }

        $this->gateway->setTestMode($testMode);
    }

    public function processStoreRequest($request, $data, $payer)
    {
        $card_gateway = $this->payment_settings['gateway'];
        $currency_code = $this->payment_settings['default_currency'] ?? 'USD';
        try {
            $data['hash'] = sha1($data['sale_id'] . str()->random());
            if ($card_gateway == 'Stripe') {
                $response = $this->purchase([
                    'currency' => $currency_code,
                    'token'    => $request->token_id,
                    'amount'   => ($data['amount'] * 100),
                ]);
            } elseif ($card_gateway == 'TillPayments') {
                $response = $this->purchase([
                    'transactionId' => $data['hash'],
                    'currency'      => $currency_code,
                    'token'         => $request->token_id,
                    'amount'        => number_format($data['amount'], 2, '.', ''),
                ]);
            } else {
                $card = $request->only(['firstName', 'lastName', 'cvv', 'billingAddress1', 'billingCity', 'billingPostcode', 'billingState', 'billingCountry']);
                $expiry = explode('-', $request->expiry_date);
                if ($expiry[0] && $expiry[1]) {
                    $card['expiryYear'] = $expiry[0];
                    $card['expiryMonth'] = $expiry[1];
                } else {
                    return ['success' => false, 'message' => __('Expiry date has wrong format.')];
                }
                $card['number'] = $request->card_number;
                $response = $this->purchase([
                    'card'        => $card,
                    'currency'    => $currency_code,
                    'amount'      => $data['amount'],
                    'description' => __('Processing payment ' . $data['amount'] . ' for ' . $payer->name . ($payer->company ? ' (' . $payer->company . ')' : '')),
                ]);
            }

            if ($response->isSuccessful()) {
                $data['received'] = 1;
                $data['gateway_transaction_id'] = $response->getTransactionReference();

                return $data;
            }
            logger()->error(print_r($response, true));

            return ['success' => false, 'message' => $response->getMessage()];
        } catch (\Exception $e) {
            logger()->error($e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function purchase($data)
    {
        return $this->gateway->purchase($data)->send();
    }

    public function complete($data)
    {
        return $this->gateway->completePurchase($data)->send();
    }
}
