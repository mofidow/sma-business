# Razorpay Payment Gateway Integration

This document explains how to set up and use the Razorpay payment gateway for India.

## Installation

1. Install the Razorpay PHP SDK:

```bash
composer require razorpay/razorpay:2.*
```

2. Add your Razorpay credentials to your `.env` file:

```env
RAZORPAY_KEY_ID=your_key_id_here
RAZORPAY_KEY_SECRET=your_key_secret_here
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret_here
RAZORPAY_THEME_COLOR=#3399cc
```

## Configuration

### Getting API Keys

1. Sign up for a Razorpay account at [https://razorpay.com](https://razorpay.com)
2. Navigate to Settings → API Keys in your dashboard
3. Generate your Test/Live API keys
4. Copy the Key ID and Key Secret to your `.env` file

### Webhook Configuration

1. Go to Settings → Webhooks in your Razorpay dashboard
2. Create a new webhook with the URL: `https://yourdomain.com/payment_gateways/razorpay/webhook`
3. Select the following events:
   - `payment.captured`
   - `payment.failed`
   - `refund.created`
4. Copy the webhook secret and add it to your `.env` file

## Features

### Supported Features

- ✅ Order creation with Razorpay Checkout
- ✅ Customer prefill (name, email, phone)
- ✅ Automatic payment capture
- ✅ Full and partial refunds
- ✅ Webhook support for real-time notifications
- ✅ Signature verification for security
- ✅ Custom theme color

### Payment Flow

1. **Order Creation**: When a customer initiates payment, an order is created in Razorpay
2. **Checkout**: The customer is shown the Razorpay checkout modal
3. **Payment**: Customer completes payment using their preferred method
4. **Callback**: After successful payment, customer is redirected to the callback URL
5. **Verification**: Payment signature is verified for security
6. **Job Dispatch**: A `ProcessRazorpayPayment` job is dispatched to queue
7. **Payment Processing**: The job creates a payment record and updates the sale
8. **Confirmation**: Payment is marked as received and customer sees success message

### Background Job Processing

The payment processing happens asynchronously via a queued job:

**Job**: `ProcessRazorpayPayment`

- Creates payment record linked to the sale
- Updates sale's paid amount and payment status
- Prevents duplicate payment processing
- Comprehensive logging for debugging
- Automatic retry on failure

**Benefits**:

- Non-blocking payment confirmation
- Resilient to temporary failures
- Better user experience with faster redirects
- Detailed audit trail via logs

Make sure your queue worker is running:

```bash
php artisan queue:work
```

## Routes

The following routes are automatically registered:

- **POST** `/payment_gateways/razorpay/callback` - Handle payment completion
- **POST** `/payment_gateways/razorpay/webhook` - Handle webhook notifications

### Webhook Events

The webhook handler processes the following events:

**payment.captured**: Dispatches the payment processing job (redundant with callback but ensures reliability)
**payment.failed**: Logs failed payment attempts for debugging
**refund.created**: Logs refund information for tracking

## Usage

### Frontend Integration

When the purchase method returns a successful result, you'll receive checkout data. Use the Razorpay Checkout library on your frontend:

```html
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
  const options = {
    key: checkoutData.razorpay_key,
    amount: checkoutData.amount,
    currency: checkoutData.currency,
    name: checkoutData.name,
    description: checkoutData.description,
    order_id: checkoutData.order_id,
    callback_url: checkoutData.callback_url,
    prefill: checkoutData.prefill,
    theme: checkoutData.theme,
    modal: checkoutData.modal,
  };

  const razorpay = new Razorpay(options);
  razorpay.open();
</script>
```

### Customer Information

You can prefill customer information by passing the following fields in the gateway form:

- `name` - Customer's full name
- `email` - Customer's email address
- `phone` - Customer's phone number (format: +91 9876543210)

### Refunds

The gateway supports both full and partial refunds. Refunds are processed through the Razorpay API and typically take 5-7 business days to reflect in the customer's account.

## Testing

### Test Cards

Use the following test card details in test mode:

**Successful Payment:**

- Card Number: 4111 1111 1111 1111
- Expiry: Any future date
- CVV: Any 3 digits

**Failed Payment:**

- Card Number: 4000 0000 0000 0002
- Expiry: Any future date
- CVV: Any 3 digits

### Test UPI IDs

- Success: `success@razorpay`
- Failure: `failure@razorpay`

## Currency Support

Razorpay supports multiple currencies:

- INR (Indian Rupee) - Primary
- USD, EUR, GBP, AUD, and 100+ more currencies

**Note:** For non-INR currencies, ensure your Razorpay account is enabled for international payments.

## Security

- All payment signatures are verified using HMAC SHA256
- Webhook signatures are verified before processing events
- API credentials should never be exposed to the frontend
- Use environment variables for all sensitive configuration

## Troubleshooting

### Payment Verification Failed

- Ensure your webhook secret is correctly configured
- Check that the callback URL is accessible
- Verify API credentials are correct

### Webhook Not Receiving Events

- Confirm webhook URL is publicly accessible (not localhost)
- Check that webhook secret matches your configuration
- Verify selected events in Razorpay dashboard

### Amount Mismatch

- Amounts are stored in minor units (paise for INR)
- 1 INR = 100 paise
- Example: ₹100.00 = 10000 paise

## Support

For Razorpay-specific issues:

- Documentation: [https://razorpay.com/docs](https://razorpay.com/docs)
- Support: [https://razorpay.com/support](https://razorpay.com/support)

## License

This integration uses the official Razorpay PHP SDK.
