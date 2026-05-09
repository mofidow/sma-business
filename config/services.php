<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'waafi' => [
        'merchant_uid' => env('WAAFI_MERCHANT_UID'),
        'api_user_id'  => env('WAAFI_API_USER_ID'),
        'api_key'      => env('WAAFI_API_KEY'),
        'enabled'      => env('WAAFI_ENABLED', 'true'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'mailersend' => [
        'api_key' => env('MAILERSEND_API_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'model'   => App\Models\User::class,
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
        'webhook' => [
            'secret'    => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'paymes' => [
        'public_key' => env('PAYMES_PUBLIC_KEY'),
        'secret_key' => env('PAYMES_SECRET_KEY'),
    ],

    'paypal' => [
        'enabled'   => env('PAYPAL_ENABLED'),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret'    => env('PAYPAL_SECRET'),
    ],

    'authorize' => [
        'login'           => env('AUTHORIZE_LOGIN'),
        'transaction_key' => env('AUTHORIZE_TRANSACTION_KEY'),
        'test_mode'       => env('AUTHORIZE_TEST_MODE', false),
    ],

    'razorpay' => [
        'key_id'         => env('RAZORPAY_KEY_ID'),
        'key_secret'     => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        'theme_color'    => env('RAZORPAY_THEME_COLOR', '#3399cc'),
    ],

    'payu' => [
        'key'       => env('PAYU_MERCHANT_KEY'),
        'salt'      => env('PAYU_MERCHANT_SALT'),
        'test_mode' => env('PAYU_TEST_MODE', true),
    ],

    'flutterwave' => [
        'client_id'      => env('FLUTTERWAVE_CLIENT_ID'),
        'client_secret'  => env('FLUTTERWAVE_CLIENT_SECRET'),
        'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
        'webhook_secret' => env('FLUTTERWAVE_WEBHOOK_SECRET'),
        'test_mode'      => env('FLUTTERWAVE_TEST_MODE', true),
    ],

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
    ],

    'mpesa' => [
        'base_url'        => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
        'consumer_key'    => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'short_code'      => env('MPESA_SHORT_CODE'),
        'passkey'         => env('MPESA_PASSKEY'),
        'command_id'      => env('MPESA_COMMAND_ID', 'CustomerPayBillOnline'),
        'callback_url'    => env('MPESA_CALLBACK_URL'),
    ],

    'telegram-bot-api' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
    ],
];
