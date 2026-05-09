# Flutterwave Payment Gateway

This documentation covers the Flutterwave payment gateway integration for the application.

## Overview

Flutterwave is a payments technology company that provides payment processing services for banks and businesses across Africa. This integration uses the Flutterwave v4 API.

## Configuration

Add the following environment variables to your `.env` file:

```env
FLUTTERWAVE_CLIENT_ID=FLWPUBK-XXXXXXXXXXXX
FLUTTERWAVE_CLIENT_SECRET=FLWSECK-XXXXXXXXXXXX
FLUTTERWAVE_ENCRYPTION_KEY=FLWENCRYPT-XXXXXXXXXXXX
FLUTTERWAVE_WEBHOOK_SECRET=your_webhook_secret_hash
FLUTTERWAVE_TEST_MODE=true
```

### Configuration Options

| Key              | Description                                 | Required |
| ---------------- | ------------------------------------------- | -------- |
| `client_id`      | Your Flutterwave Client ID (Public Key)     | Yes      |
| `client_secret`  | Your Flutterwave Client Secret (Secret Key) | Yes      |
| `encryption_key` | Your Flutterwave encryption key             | No       |
| `webhook_secret` | Webhook verification hash                   | No       |
| `test_mode`      | Enable sandbox mode (default: true)         | No       |

## Supported Features

- **Card Payments**: Visa, Mastercard, Verve, etc.
- **Bank Transfers**: Direct bank transfers
- **Mobile Money**: M-Pesa, MTN Mobile Money, etc.
- **USSD Payments**: Bank USSD payments
- **Partial Refunds**: Full and partial refund support

## Supported Currencies

Flutterwave supports multiple currencies including:

- NGN (Nigerian Naira)
- GHS (Ghanaian Cedi)
- KES (Kenyan Shilling)
- ZAR (South African Rand)
- USD (US Dollar)
- EUR (Euro)
- GBP (British Pound)

## Customer Information

When processing payments, the following customer information is collected:

| Field   | Type  | Required | Description              |
| ------- | ----- | -------- | ------------------------ |
| `name`  | text  | Yes      | Customer's full name     |
| `email` | email | Yes      | Customer's email address |
| `phone` | text  | No       | Customer's phone number  |

## API Endpoints

### Callback URL

The callback URL is used by Flutterwave to redirect the customer after payment:

```
GET /payment-gateways/flutterwave/callback
```

Query Parameters:

- `status` - Payment status (successful, failed, cancelled)
- `transaction_id` - Flutterwave transaction ID
- `tx_ref` - Your transaction reference
- `sale_reference` - Your sale reference
- `return_url` - URL to redirect after processing

### Webhook URL

Configure this URL in your Flutterwave dashboard for receiving payment notifications:

```
POST /payment-gateways/flutterwave/webhook
```

Supported webhook events:

- `charge.completed` - When a charge is completed
- `transfer.completed` - When a transfer is completed
- `refund.completed` - When a refund is completed

## Security

### Webhook Verification

To secure webhooks, configure a secret hash in your Flutterwave dashboard and set it as `FLUTTERWAVE_WEBHOOK_SECRET`. The webhook handler verifies the `verif-hash` header against this secret.

### Transaction Verification

All successful transactions are verified server-side using the Flutterwave API before processing to prevent fraud.

## Payment Flow

1. Customer submits payment form with name, email, and phone
2. Application creates a charge via Flutterwave's orchestration endpoint
3. Customer is redirected to Flutterwave's checkout page
4. Customer completes payment
5. Flutterwave redirects to callback URL with transaction details
6. Application verifies transaction and processes the order
7. Webhook provides backup confirmation

## Refunds

To process a refund:

```php
use Plugins\Payments\Gateways\Flutterwave\FlutterwavePaymentMethod;

$method = app(FlutterwavePaymentMethod::class);
$result = $method->refund($context, $chargeId, $amountInMinorUnits);

if ($result->isSuccessful()) {
    // Refund processed
    $refundId = $result->providerReference();
}
```

### Refund Reasons

Valid refund reasons:

- `duplicate` - Duplicate charge
- `fraudulent` - Fraudulent transaction
- `requested_by_customer` - Customer requested refund (default)
- `expired_uncaptured_charge` - Expired uncaptured charge

## Testing

### Test Cards

Use these test cards in sandbox mode:

| Card Type      | Card Number             | Expiry | CVV | PIN  | OTP   |
| -------------- | ----------------------- | ------ | --- | ---- | ----- |
| Visa (Success) | 4187 4274 1556 4246     | 09/32  | 828 | 3310 | 12345 |
| Visa (Fail)    | 4242 4242 4242 4242     | 09/32  | 828 | 3310 | 12345 |
| Mastercard     | 5531 8866 5214 2950     | 09/32  | 564 | 3310 | 12345 |
| Verve          | 5061 4604 1012 0223 210 | 09/32  | 780 | 3310 | 12345 |

### Test Bank Account

| Bank        | Account Number |
| ----------- | -------------- |
| Access Bank | 0690000031     |

## Error Handling

Common error responses:

| Status | Message             | Description                     |
| ------ | ------------------- | ------------------------------- |
| error  | Invalid card number | Card validation failed          |
| error  | Insufficient funds  | Customer has insufficient funds |
| error  | Transaction failed  | General transaction failure     |
| error  | Card declined       | Card was declined by issuer     |

## Logging

All Flutterwave transactions are logged for debugging:

```
ProcessFlutterwavePayment: Processing payment
ProcessFlutterwavePayment: Payment verified successfully
Flutterwave webhook received
Flutterwave callback: Transaction verification failed
```

## Dashboard Setup

1. Log in to your [Flutterwave Dashboard](https://dashboard.flutterwave.com)
2. Navigate to Settings > API Keys
3. Copy your Client ID (Public Key), Client Secret (Secret Key), and Encryption Key
4. Navigate to Settings > Webhooks
5. Add your webhook URL: `https://yourdomain.com/payment-gateways/flutterwave/webhook`
6. Set a secret hash for webhook verification
7. Enable the desired webhook events

## Support

For API documentation and support:

- [Flutterwave API Reference](https://developer.flutterwave.com/reference)
- [Flutterwave Support](https://support.flutterwave.com)
