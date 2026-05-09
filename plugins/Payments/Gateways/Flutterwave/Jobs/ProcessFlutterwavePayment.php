<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Flutterwave\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Job to process Flutterwave payment confirmations asynchronously
 */
class ProcessFlutterwavePayment implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $saleReference,
        public readonly string $transactionId,
        public readonly array $paymentData,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('ProcessFlutterwavePayment: Processing payment', [
            'sale_reference' => $this->saleReference,
            'transaction_id' => $this->transactionId,
        ]);

        try {
            // Extract payment information
            $status = $this->paymentData['status'] ?? '';
            $amount = $this->paymentData['amount'] ?? 0;
            $currency = $this->paymentData['currency'] ?? 'NGN';
            $chargedAmount = $this->paymentData['charged_amount'] ?? $amount;
            $appFee = $this->paymentData['app_fee'] ?? 0;
            $merchantFee = $this->paymentData['merchant_fee'] ?? 0;
            $processorResponse = $this->paymentData['processor_response'] ?? '';
            $authModel = $this->paymentData['auth_model'] ?? '';
            $ip = $this->paymentData['ip'] ?? '';
            $narration = $this->paymentData['narration'] ?? '';
            $paymentType = $this->paymentData['payment_type'] ?? '';

            // Customer information
            $customer = $this->paymentData['customer'] ?? [];
            $customerEmail = $customer['email'] ?? '';
            $customerName = $customer['name'] ?? '';
            $customerPhone = $customer['phone_number'] ?? '';

            // Card information (if available)
            $card = $this->paymentData['card'] ?? [];
            $cardType = $card['type'] ?? null;
            $cardLast4 = $card['last_4digits'] ?? null;
            $cardExpiry = $card['expiry'] ?? null;
            $cardCountry = $card['country'] ?? null;

            // Meta information
            $meta = $this->paymentData['meta'] ?? [];

            if ($status !== 'successful') {
                Log::warning('ProcessFlutterwavePayment: Payment not successful', [
                    'sale_reference' => $this->saleReference,
                    'transaction_id' => $this->transactionId,
                    'status'         => $status,
                    'response'       => $processorResponse,
                ]);

                return;
            }

            Log::info('ProcessFlutterwavePayment: Payment verified successfully', [
                'sale_reference' => $this->saleReference,
                'transaction_id' => $this->transactionId,
                'amount'         => $amount,
                'currency'       => $currency,
                'charged_amount' => $chargedAmount,
                'app_fee'        => $appFee,
                'payment_type'   => $paymentType,
                'customer_email' => $customerEmail,
            ]);

            // TODO: Implement your business logic here:
            // 1. Find the sale/order by $this->saleReference
            // 2. Verify the amount matches
            // 3. Update the sale/order payment status
            // 4. Create payment record
            // 5. Send confirmation email
            // 6. Update inventory if needed

            // Example implementation:
            // $sale = Sale::where('reference', $this->saleReference)->first();
            // if ($sale) {
            //     $expectedAmount = $sale->grand_total;
            //     if ($amount >= $expectedAmount) {
            //         $sale->update([
            //             'payment_status' => 'paid',
            //             'payment_method' => 'flutterwave',
            //             'gateway_transaction_id' => $this->transactionId,
            //         ]);
            //
            //         // Create payment record
            //         $sale->payments()->create([
            //             'amount' => $amount,
            //             'gateway' => 'flutterwave',
            //             'gateway_transaction_id' => $this->transactionId,
            //             'status' => 'completed',
            //             'payment_type' => $paymentType,
            //             'gateway_fee' => $appFee,
            //             'customer_email' => $customerEmail,
            //             'card_last4' => $cardLast4,
            //             'card_type' => $cardType,
            //             'meta' => $this->paymentData,
            //         ]);
            //     }
            // }
        } catch (Throwable $e) {
            Log::error('ProcessFlutterwavePayment: Processing failed', [
                'sale_reference' => $this->saleReference,
                'transaction_id' => $this->transactionId,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('ProcessFlutterwavePayment: Job failed permanently', [
            'sale_reference' => $this->saleReference,
            'transaction_id' => $this->transactionId,
            'error'          => $exception?->getMessage(),
        ]);

        // TODO: Notify admin about the failed payment processing
        // Notification::route('mail', config('app.admin_email'))
        //     ->notify(new PaymentProcessingFailed($this->saleReference, $this->transactionId, $exception));
    }
}
