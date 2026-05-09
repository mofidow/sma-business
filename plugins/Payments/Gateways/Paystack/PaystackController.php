<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Paystack;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Queue;
use Plugins\Payments\Gateways\Paystack\Jobs\ProcessPaystackPayment;

/**
 * Controller for handling Paystack callbacks and webhooks
 */
final class PaystackController extends Controller
{
    /**
     * Handle the redirect callback from Paystack after payment
     */
    public function callback(Request $request): RedirectResponse
    {
        $reference = $request->query('reference') ?? $request->query('trxref');
        $saleReference = $request->query('sale_reference');
        $returnUrl = $request->query('return_url');

        $defaultReturn = config('app.url');

        if ($reference === null || $reference === '') {
            Log::warning('Paystack callback: Missing transaction reference');

            return redirect()->to($returnUrl ?: $defaultReturn)
                ->with('error', 'Payment reference not found.');
        }

        try {
            // Verify the transaction
            $sdk = $this->getSdk();
            $response = $sdk->verifyTransaction((string) $reference);

            if (($response['status'] ?? false) !== true) {
                Log::warning('Paystack callback: Transaction verification failed', [
                    'reference' => $reference,
                    'response'  => $response,
                ]);

                return redirect()->to($returnUrl ?: $defaultReturn)
                    ->with('error', 'Payment verification failed. Please contact support.');
            }

            $data = $response['data'] ?? [];
            $transactionStatus = $data['status'] ?? '';

            if (Paystack::isTransactionSuccessful($transactionStatus)) {
                // Queue the payment processing job
                Queue::push(new ProcessPaystackPayment(
                    saleReference: $saleReference ?? $reference,
                    transactionReference: (string) $reference,
                    paymentData: $data
                ));

                return redirect()->to($returnUrl ?: $defaultReturn)
                    ->with('success', 'Payment successful! Your transaction is being processed.');
            }

            Log::info('Paystack callback: Transaction not successful', [
                'reference' => $reference,
                'status'    => $transactionStatus,
            ]);

            return redirect()->to($returnUrl ?: $defaultReturn)
                ->with('error', 'Payment was not successful. Status: ' . $transactionStatus);
        } catch (Throwable $e) {
            Log::error('Paystack callback error', [
                'reference' => $reference,
                'error'     => $e->getMessage(),
            ]);

            return redirect()->to($returnUrl ?: $defaultReturn)
                ->with('error', 'An error occurred while verifying your payment.');
        }
    }

    /**
     * Handle Paystack webhooks
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');
        $secretKey = config('services.paystack.secret_key');

        // Verify webhook signature
        if ($secretKey !== null && $secretKey !== '') {
            $expectedSignature = hash_hmac('sha512', $payload, $secretKey);

            if (! hash_equals($expectedSignature, $signature ?? '')) {
                Log::warning('Paystack webhook: Invalid signature', [
                    'received_signature' => $signature,
                ]);

                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            }
        }

        $data = json_decode($payload, true);

        if (! is_array($data)) {
            Log::warning('Paystack webhook: Invalid JSON payload');

            return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
        }

        $event = $data['event'] ?? '';
        $eventData = $data['data'] ?? [];

        Log::info('Paystack webhook received', [
            'event' => $event,
            'data'  => $eventData,
        ]);

        try {
            switch ($event) {
                case 'charge.success':
                    $this->handleChargeSuccess($eventData);
                    break;
                case 'transfer.success':
                    $this->handleTransferSuccess($eventData);
                    break;
                case 'transfer.failed':
                    $this->handleTransferFailed($eventData);
                    break;
                case 'refund.processed':
                case 'refund.failed':
                    $this->handleRefundEvent($event, $eventData);
                    break;
                default:
                    Log::info('Paystack webhook: Unhandled event type', ['event' => $event]);
            }

            return response()->json(['status' => 'success']);
        } catch (Throwable $e) {
            Log::error('Paystack webhook processing error', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            // Return 200 to acknowledge receipt even on error
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Handle charge.success webhook event
     */
    private function handleChargeSuccess(array $data): void
    {
        $status = $data['status'] ?? '';
        $reference = $data['reference'] ?? null;

        if (! Paystack::isTransactionSuccessful($status)) {
            Log::info('Paystack webhook: Charge not successful', [
                'reference' => $reference,
                'status'    => $status,
            ]);

            return;
        }

        if ($reference === null) {
            Log::warning('Paystack webhook: Missing transaction reference');

            return;
        }

        // Extract sale reference from metadata
        $metadata = $data['metadata'] ?? [];
        $saleReference = $metadata['sale_ref'] ?? $reference;

        // Queue the payment processing job
        Queue::push(new ProcessPaystackPayment(
            saleReference: (string) $saleReference,
            transactionReference: (string) $reference,
            paymentData: $data
        ));

        Log::info('Paystack webhook: Payment job queued', [
            'reference'      => $reference,
            'sale_reference' => $saleReference,
        ]);
    }

    /**
     * Handle transfer.success webhook event
     */
    private function handleTransferSuccess(array $data): void
    {
        Log::info('Paystack webhook: Transfer successful', [
            'transfer_code' => $data['transfer_code'] ?? null,
            'amount'        => $data['amount'] ?? null,
        ]);

        // Implement transfer success logic if needed
    }

    /**
     * Handle transfer.failed webhook event
     */
    private function handleTransferFailed(array $data): void
    {
        Log::warning('Paystack webhook: Transfer failed', [
            'transfer_code' => $data['transfer_code'] ?? null,
            'reason'        => $data['reason'] ?? null,
        ]);

        // Implement transfer failure logic if needed
    }

    /**
     * Handle refund webhook events
     */
    private function handleRefundEvent(string $event, array $data): void
    {
        $refundId = $data['id'] ?? null;
        $status = $data['status'] ?? '';
        $transactionReference = $data['transaction']['reference'] ?? null;

        Log::info('Paystack webhook: Refund event', [
            'event'     => $event,
            'refund_id' => $refundId,
            'status'    => $status,
            'reference' => $transactionReference,
        ]);

        // Implement refund event logic if needed
    }

    /**
     * Get the Paystack SDK instance
     */
    private function getSdk(): Paystack
    {
        $secretKey = (string) config('services.paystack.secret_key');
        $publicKey = (string) config('services.paystack.public_key');

        return new Paystack($secretKey, $publicKey);
    }
}
