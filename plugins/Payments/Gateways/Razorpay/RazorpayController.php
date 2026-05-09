<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Razorpay;

use Throwable;
use Razorpay\Api\Api;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Plugins\Payments\Gateways\Razorpay\Jobs\ProcessRazorpayPayment;

class RazorpayController extends Controller
{
    public function callback(Request $request)
    {
        try {
            $api = new Api(
                config('services.razorpay.key_id'),
                config('services.razorpay.key_secret')
            );

            $paymentId = $request->input('razorpay_payment_id');
            $orderId = $request->input('razorpay_order_id');
            $signature = $request->input('razorpay_signature');

            if (! $paymentId || ! $orderId || ! $signature) {
                return redirect()->back()->with('error', 'Invalid payment response from Razorpay.');
            }

            $attributes = [
                'razorpay_order_id'   => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature,
            ];

            $api->utility->verifyPaymentSignature($attributes);

            $payment = $api->payment->fetch($paymentId);

            if ($payment->status === 'captured' || $payment->status === 'authorized') {
                $saleReference = $request->input('sale_reference');
                $returnUrl = $request->input('return_url');

                ProcessRazorpayPayment::dispatch(
                    saleReference: $saleReference,
                    paymentId: $paymentId,
                    orderId: $orderId,
                    paymentData: (array) $payment,
                    amount: $payment->amount / 100,
                    currency: $payment->currency
                );

                if ($returnUrl) {
                    return redirect($returnUrl)->with('success', 'Payment completed successfully.');
                }

                return redirect()->route('home')->with('success', 'Payment completed successfully.');
            }

            return redirect()->back()->with('error', 'Payment verification failed.');
        } catch (Throwable $exception) {
            Log::error('Razorpay callback error: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Payment verification failed: ' . $exception->getMessage());
        }
    }

    public function webhook(Request $request)
    {
        try {
            $webhookSecret = config('services.razorpay.webhook_secret');
            $webhookSignature = $request->header('X-Razorpay-Signature');

            if ($webhookSecret && $webhookSignature) {
                $api = new Api(
                    config('services.razorpay.key_id'),
                    config('services.razorpay.key_secret')
                );

                $api->utility->verifyWebhookSignature(
                    $request->getContent(),
                    $webhookSignature,
                    $webhookSecret
                );
            }

            $payload = $request->all();
            $event = $payload['event'] ?? null;

            Log::info('Razorpay webhook received', [
                'event'   => $event,
                'payload' => $payload,
            ]);

            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($payload);
                    break;
                case 'payment.failed':
                    $this->handlePaymentFailed($payload);
                    break;
                case 'refund.created':
                    $this->handleRefundCreated($payload);
                    break;
                default:
                    break;
            }

            return response()->json(['status' => 'success']);
        } catch (Throwable $exception) {
            Log::error('Razorpay webhook error: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 400);
        }
    }

    /**
     * Handle payment.captured webhook event
     */
    private function handlePaymentCaptured(array $payload): void
    {
        try {
            $paymentData = $payload['payload']['payment']['entity'] ?? null;

            if (! $paymentData) {
                Log::warning('Razorpay payment.captured webhook missing payment data', ['payload' => $payload]);

                return;
            }

            $orderId = $paymentData['order_id'] ?? null;
            $paymentId = $paymentData['id'] ?? null;
            $amount = ($paymentData['amount'] ?? 0) / 100;
            $currency = $paymentData['currency'] ?? 'INR';
            $notes = $paymentData['notes'] ?? [];
            $saleReference = $notes['sale_ref'] ?? null;

            if (! $saleReference || ! $paymentId || ! $orderId) {
                Log::warning('Razorpay payment.captured webhook missing required fields', [
                    'payment_id'     => $paymentId,
                    'order_id'       => $orderId,
                    'sale_reference' => $saleReference,
                ]);

                return;
            }

            ProcessRazorpayPayment::dispatch(
                saleReference: $saleReference,
                paymentId: $paymentId,
                orderId: $orderId,
                paymentData: $paymentData,
                amount: $amount,
                currency: $currency
            );

            Log::info('Razorpay payment.captured webhook processed', [
                'payment_id'     => $paymentId,
                'sale_reference' => $saleReference,
            ]);
        } catch (Throwable $exception) {
            Log::error('Razorpay payment.captured webhook handling failed: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle payment.failed webhook event
     */
    private function handlePaymentFailed(array $payload): void
    {
        try {
            $paymentData = $payload['payload']['payment']['entity'] ?? null;

            if (! $paymentData) {
                return;
            }

            Log::warning('Razorpay payment failed', [
                'payment_id'        => $paymentData['id'] ?? null,
                'order_id'          => $paymentData['order_id'] ?? null,
                'error_code'        => $paymentData['error_code'] ?? null,
                'error_description' => $paymentData['error_description'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Log::error('Razorpay payment.failed webhook handling failed: ' . $exception->getMessage());
        }
    }

    /**
     * Handle refund.created webhook event
     */
    private function handleRefundCreated(array $payload): void
    {
        try {
            $refundData = $payload['payload']['refund']['entity'] ?? null;

            if (! $refundData) {
                return;
            }

            Log::info('Razorpay refund created', [
                'refund_id'  => $refundData['id'] ?? null,
                'payment_id' => $refundData['payment_id'] ?? null,
                'amount'     => ($refundData['amount'] ?? 0) / 100,
                'status'     => $refundData['status'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Log::error('Razorpay refund.created webhook handling failed: ' . $exception->getMessage());
        }
    }
}
