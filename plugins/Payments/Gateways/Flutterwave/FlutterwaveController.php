<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Flutterwave;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Queue;
use Plugins\Payments\Gateways\Flutterwave\Jobs\ProcessFlutterwavePayment;

/**
 * Controller for handling Flutterwave callbacks and webhooks
 */
final class FlutterwaveController extends Controller
{
    /**
     * Handle the redirect callback from Flutterwave after payment
     */
    public function callback(Request $request): RedirectResponse
    {
        $status = $request->query('status');
        $transactionId = $request->query('transaction_id');
        $txRef = $request->query('tx_ref');
        $saleReference = $request->query('sale_reference');
        $returnUrl = $request->query('return_url');

        $defaultReturn = config('app.url');

        if ($status === 'successful' || $status === 'completed') {
            try {
                // Verify the transaction
                $sdk = $this->getSdk();
                $response = $sdk->verifyTransaction((int) $transactionId);

                if (($response['status'] ?? '') === 'success') {
                    $data = $response['data'] ?? [];
                    $paymentStatus = $data['status'] ?? '';

                    if ($paymentStatus === 'successful') {
                        // Queue the payment processing job
                        Queue::push(new ProcessFlutterwavePayment(
                            saleReference: $txRef ?? $saleReference,
                            transactionId: (string) $transactionId,
                            paymentData: $data
                        ));

                        return redirect()->to($returnUrl ?: $defaultReturn)
                            ->with('success', 'Payment successful! Your transaction is being processed.');
                    }
                }

                Log::warning('Flutterwave callback: Transaction verification failed', [
                    'transaction_id' => $transactionId,
                    'tx_ref'         => $txRef,
                    'response'       => $response,
                ]);

                return redirect()->to($returnUrl ?: $defaultReturn)
                    ->with('error', 'Payment verification failed. Please contact support.');
            } catch (Throwable $e) {
                Log::error('Flutterwave callback error', [
                    'transaction_id' => $transactionId,
                    'error'          => $e->getMessage(),
                ]);

                return redirect()->to($returnUrl ?: $defaultReturn)
                    ->with('error', 'An error occurred while processing your payment.');
            }
        }

        if ($status === 'cancelled') {
            return redirect()->to($returnUrl ?: $defaultReturn)
                ->with('warning', 'Payment was cancelled.');
        }

        // Payment failed
        return redirect()->to($returnUrl ?: $defaultReturn)
            ->with('error', 'Payment failed. Please try again.');
    }

    /**
     * Handle Flutterwave webhooks
     */
    public function webhook(Request $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('verif-hash');
        $webhookSecret = config('services.flutterwave.webhook_secret');

        // Verify webhook signature if secret is configured
        if ($webhookSecret !== null && $webhookSecret !== '') {
            if ($signature !== $webhookSecret) {
                Log::warning('Flutterwave webhook: Invalid signature', [
                    'received_signature' => $signature,
                ]);

                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            }
        }

        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];

        Log::info('Flutterwave webhook received', [
            'event' => $event,
            'data'  => $data,
        ]);

        try {
            switch ($event) {
                case 'charge.completed':
                    $this->handleChargeCompleted($data);
                    break;
                case 'transfer.completed':
                    $this->handleTransferCompleted($data);
                    break;
                case 'refund.completed':
                    $this->handleRefundCompleted($data);
                    break;
                default:
                    Log::info('Flutterwave webhook: Unhandled event type', ['event' => $event]);
            }

            return response()->json(['status' => 'success']);
        } catch (Throwable $e) {
            Log::error('Flutterwave webhook processing error', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            // Return 200 to acknowledge receipt even on error
            // to prevent Flutterwave from retrying
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Handle charge.completed webhook event
     */
    private function handleChargeCompleted(array $data): void
    {
        $status = $data['status'] ?? '';
        $transactionId = $data['id'] ?? null;
        $txRef = $data['tx_ref'] ?? null;

        if ($status !== 'successful') {
            Log::info('Flutterwave webhook: Charge not successful', [
                'transaction_id' => $transactionId,
                'status'         => $status,
            ]);

            return;
        }

        if ($txRef === null && $transactionId === null) {
            Log::warning('Flutterwave webhook: Missing transaction reference');

            return;
        }

        // Queue the payment processing job
        Queue::push(new ProcessFlutterwavePayment(
            saleReference: (string) $txRef,
            transactionId: (string) $transactionId,
            paymentData: $data
        ));

        Log::info('Flutterwave webhook: Payment job queued', [
            'transaction_id' => $transactionId,
            'tx_ref'         => $txRef,
        ]);
    }

    /**
     * Handle transfer.completed webhook event
     */
    private function handleTransferCompleted(array $data): void
    {
        Log::info('Flutterwave webhook: Transfer completed', [
            'transfer_id' => $data['id'] ?? null,
            'status'      => $data['status'] ?? null,
        ]);

        // Implement transfer completion logic if needed
    }

    /**
     * Handle refund.completed webhook event
     */
    private function handleRefundCompleted(array $data): void
    {
        $refundId = $data['id'] ?? null;
        $status = $data['status'] ?? '';
        $chargeId = $data['flw_ref'] ?? $data['tx_ref'] ?? null;

        Log::info('Flutterwave webhook: Refund completed', [
            'refund_id' => $refundId,
            'charge_id' => $chargeId,
            'status'    => $status,
        ]);

        // Implement refund completion logic if needed
        // This could update the local refund record status
    }

    /**
     * Get the Flutterwave SDK instance
     */
    private function getSdk(): Flutterwave
    {
        $clientSecret = (string) config('services.flutterwave.client_secret');
        $clientId = (string) config('services.flutterwave.client_id');
        $testMode = (bool) config('services.flutterwave.test_mode', true);

        return new Flutterwave($clientSecret, $clientId, ! $testMode);
    }
}
