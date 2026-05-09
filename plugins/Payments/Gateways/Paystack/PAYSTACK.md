# Paystack Payment Gateway

This documentation covers the Paystack payment gateway integration for the application.

## Overview

Paystack is a modern payments platform that provides payment processing services for businesses in Africa. This integration uses the Paystack API to accept payments.

## Configuration

Add the following environment variables to your `.env` file:

```env
PAYSTACK_PUBLIC_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxx
PAYSTACK_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Configuration Options

| Key          | Description                                  | Required |
| ------------ | -------------------------------------------- | -------- |
| `public_key` | Your Paystack public key (starts with `pk_`) | Yes      |
| `secret_key` | Your Paystack secret key (starts with `sk_`) | Yes      |

## Supported Features

- **Card Payments**: Visa, Mastercard, Verve
- **Bank Transfers**: Direct bank transfers
- **USSD**: Bank USSD payments
- **Mobile Money**: Mobile money payments (Ghana, Kenya)
- **Apple Pay**: Apple Pay integration
- **QR Payments**: QR code payments
- **Partial Refunds**: Full and partial refund support
- **Recurring Payments**: Authorization codes for recurring charges

## Supported Currencies

Paystack supports:

- NGN (Nigerian Naira)
- GHS (Ghanaian Cedi)
- ZAR (South African Rand)
- USD (US Dollar)
- KES (Kenyan Shilling)

## Customer Information

When processing payments, the following customer information is collected:

| Field   | Type  | Required | Description              |
| ------- | ----- | -------- | ------------------------ |
| `email` | email | Yes      | Customer's email address |
| `name`  | text  | No       | Customer's full name     |
| `phone` | text  | No       | Customer's phone number  |

## API Endpoints

### Callback URL

The callback URL is used by Paystack to redirect the customer after payment:

```
GET /payment-gateways/paystack/callback
```

Query Parameters:

- `reference` - Paystack transaction reference
- `trxref` - Alternative transaction reference
- `sale_reference` - Your sale reference
- `return_url` - URL to redirect after processing

### Webhook URL

Configure this URL in your Paystack dashboard for receiving payment notifications:

```
POST /payment-gateways/paystack/webhook
```

Supported webhook events:

- `charge.success` - When a charge is successful
- `transfer.success` - When a transfer is successful
- `transfer.failed` - When a transfer fails
- `refund.processed` - When a refund is processed
- `refund.failed` - When a refund fails

## Security

### Webhook Verification

Paystack signs all webhooks with your secret key using HMAC SHA512. The webhook handler automatically verifies the `x-paystack-signature` header against your secret key.

### Transaction Verification

All successful transactions are verified server-side using the Paystack API before processing to prevent fraud.

## Payment Flow

1. Customer submits payment form with email
2. Application initializes transaction via Paystack API
3. Customer is redirected to Paystack's checkout page (authorization_url)
4. Customer completes payment using their preferred method
5. Paystack redirects to callback URL with transaction reference
6. Application verifies transaction and processes the order
7. Webhook provides backup confirmation

## Refunds

To process a refund:

```php
use Plugins\Payments\Gateways\Paystack\PaystackPaymentMethod;

$method = app(PaystackPaymentMethod::class);
$result = $method->refund($context, $transactionReference, $amountInSubunit);

if ($result->isSuccessful()) {
    // Refund processed
    $refundId = $result->providerReference();
}
```

Note: Amount must be in subunit (kobo for NGN, pesewas for GHS, etc.)

## Recurring Payments

Paystack supports recurring payments through authorization codes. When a customer pays successfully, you receive an `authorization_code` that can be used for future charges:

```php
// The authorization data is available in the payment response
$authorization = $paymentData['authorization'];
$authorizationCode = $authorization['authorization_code'];
$reusable = $authorization['reusable']; // Check if code is reusable

// Store the authorization code for future charges
if ($reusable) {
    // Save $authorizationCode for this customer
}
```

## Testing

### Test Cards

Use these test cards in test mode:

| Card Type      | Card Number             | Expiry          | CVV          | PIN  | OTP    |
| -------------- | ----------------------- | --------------- | ------------ | ---- | ------ |
| Visa (Success) | 4084 0841 0834 0843     | Any future date | Any 3 digits | 0000 | 123456 |
| Visa (Failure) | 4084 0841 0834 0842     | Any future date | Any 3 digits | 0000 | 123456 |
| Mastercard     | 5060 6666 6666 6666 666 | Any future date | Any 3 digits | 0000 | 123456 |

### Test Bank Account

For bank transfers, use:

- Bank: Zenith Bank
- Account Number: 0000000000

## Error Handling

Common error responses:

| Status | Message               | Description             |
| ------ | --------------------- | ----------------------- |
| false  | Invalid email         | Email validation failed |
| false  | Invalid amount        | Amount must be positive |
| false  | Invalid reference     | Reference already used  |
| false  | Transaction not found | Reference doesn't exist |

## Logging

All Paystack transactions are logged for debugging:

```
ProcessPaystackPayment: Processing payment
ProcessPaystackPayment: Payment verified successfully
Paystack webhook received
Paystack callback: Transaction verification failed
```

## Dashboard Setup

1. Log in to your [Paystack Dashboard](https://dashboard.paystack.com)
2. Navigate to Settings > API Keys & Webhooks
3. Copy your Public Key and Secret Key
4. Add your webhook URL: `https://yourdomain.com/payment-gateways/paystack/webhook`
5. Select the events you want to receive

## Transaction ID Data Type

If you plan to store the transaction ID, represent it as an unsigned 64-bit integer.

## Support

For API documentation and support:

- [Paystack API Reference](https://paystack.com/docs/api/)
- [Paystack Payments Guide](https://paystack.com/docs/payments/)
- [Paystack Support](https://support.paystack.com)
