# PayU India Payment Gateway

This plugin integrates PayU India payment gateway into the application.

## Configuration

Add the following configuration to your `config/services.php`:

```php
'payu' => [
    'key' => env('PAYU_MERCHANT_KEY'),
    'salt' => env('PAYU_MERCHANT_SALT'),
    'test_mode' => env('PAYU_TEST_MODE', true),
],
```

Add the following environment variables to your `.env` file:

```env
PAYU_MERCHANT_KEY=your_merchant_key
PAYU_MERCHANT_SALT=your_merchant_salt
PAYU_TEST_MODE=true
```

## Test Credentials

For testing, you can use the PayU sandbox environment:

- **Test Mode**: Set `PAYU_TEST_MODE=true`
- **Test Merchant Key**: Contact PayU for test credentials
- **Test Merchant Salt**: Contact PayU for test credentials

## Test Cards

PayU provides test cards for sandbox testing:

| Card Type  | Card Number      | Expiry          | CVV          |
| ---------- | ---------------- | --------------- | ------------ |
| Visa       | 4012001037141112 | Any future date | Any 3 digits |
| MasterCard | 5123456789012346 | Any future date | Any 3 digits |

## Webhook Setup

Configure the webhook URL in your PayU merchant dashboard:

```
https://yourdomain.com/payment-gateways/payu/webhook
```

## Features

- **Payment Initiation**: Redirect users to PayU checkout page
- **Payment Verification**: Verify payment status using transaction ID or PayU ID
- **Refunds**: Full and partial refund support
- **Webhook Support**: Real-time payment status updates

## Usage

The payment gateway is automatically registered. Use it through the payment interface:

```php
use Plugins\Payments\Gateways\PayU\PayUPaymentMethod;

$gateway = new PayUPaymentMethod();

// Create payment
$result = $gateway->purchase($context);

// Refund payment
$result = $gateway->refund($context, $providerReference, $amount);
```

## API Reference

### PayU SDK Methods

- `generateHash($params)` - Generate hash for payment initiation
- `verifyHash($params)` - Verify response hash from PayU
- `verifyPaymentByTxnId($txnid)` - Verify payment by transaction ID
- `verifyPaymentByPayuId($payuId)` - Verify payment by PayU ID
- `initiateRefund($payuId, $txnid, $amount)` - Initiate refund
- `checkRefundStatus($requestId)` - Check refund status

## Documentation

- [PayU Developer Guide](https://devguide.payu.in/)
- [PayU PHP SDK](https://github.com/payu-intrepos/web-sdk-php)
