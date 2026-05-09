<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Paystack\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Job to process Paystack payment confirmations asynchronously
 */
class ProcessPaystackPayment implements ShouldQueue
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
        public readonly string $transactionReference,
        public readonly array $paymentData,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('ProcessPaystackPayment: Processing payment', [
            'sale_reference'        => $this->saleReference,
            'transaction_reference' => $this->transactionReference,
        ]);

        try {
            // Extract payment information
            $status = $this->paymentData['status'] ?? '';
            $amount = $this->paymentData['amount'] ?? 0;
            $currency = $this->paymentData['currency'] ?? 'NGN';
            $channel = $this->paymentData['channel'] ?? '';
            $gatewayResponse = $this->paymentData['gateway_response'] ?? '';
            $paidAt = $this->paymentData['paid_at'] ?? null;
            $createdAt = $this->paymentData['created_at'] ?? null;
            $ipAddress = $this->paymentData['ip_address'] ?? '';
            $fees = $this->paymentData['fees'] ?? 0;

            // Customer information
            $customer = $this->paymentData['customer'] ?? [];
            $customerEmail = $customer['email'] ?? '';
            $customerId = $customer['id'] ?? null;
            $customerCode = $customer['customer_code'] ?? '';

            // Authorization information (for recurring payments)
            $authorization = $this->paymentData['authorization'] ?? [];
            $authorizationCode = $authorization['authorization_code'] ?? null;
            $cardType = $authorization['card_type'] ?? null;
            $cardLast4 = $authorization['last4'] ?? null;
            $expMonth = $authorization['exp_month'] ?? null;
            $expYear = $authorization['exp_year'] ?? null;
            $bank = $authorization['bank'] ?? null;
            $countryCode = $authorization['country_code'] ?? null;
            $reusable = $authorization['reusable'] ?? false;

            // Metadata
            $metadata = $this->paymentData['metadata'] ?? [];

            if ($status !== 'success') {
                Log::warning('ProcessPaystackPayment: Payment not successful', [
                    'sale_reference'        => $this->saleReference,
                    'transaction_reference' => $this->transactionReference,
                    'status'                => $status,
                    'gateway_response'      => $gatewayResponse,
                ]);

                return;
            }

            Log::info('ProcessPaystackPayment: Payment verified successfully', [
                'sale_reference'        => $this->saleReference,
                'transaction_reference' => $this->transactionReference,
                'amount'                => $amount,
                'currency'              => $currency,
                'channel'               => $channel,
                'fees'                  => $fees,
                'customer_email'        => $customerEmail,
                'card_last4'            => $cardLast4,
            ]);

            // TODO: Implement your business logic here:
            // 1. Find the sale/order by $this->saleReference
            // 2. Verify the amount matches
            // 3. Update the sale/order payment status
            // 4. Create payment record
            // 5. Store authorization code if reusable (for recurring payments)
            // 6. Send confirmation email
            // 7. Update inventory if needed

            // Example implementation:
            // $sale = Sale::where('reference', $this->saleReference)->first();
            // if ($sale) {
            //     $expectedAmount = $sale->grand_total * 100; // Convert to subunit
            //     if ($amount >= $expectedAmount) {
            //         $sale->update([
            //             'payment_status' => 'paid',
            //             'payment_method' => 'paystack',
            //             'gateway_transaction_id' => $this->transactionReference,
            //         ]);
            //
            //         // Create payment record
            //         $sale->payments()->create([
            //             'amount' => $amount / 100, // Convert from subunit
            //             'gateway' => 'paystack',
            //             'gateway_transaction_id' => $this->transactionReference,
            //             'status' => 'completed',
            //             'channel' => $channel,
            //             'gateway_fee' => $fees / 100,
            //             'customer_email' => $customerEmail,
            //             'card_last4' => $cardLast4,
            //             'card_type' => $cardType,
            //             'authorization_code' => $reusable ? $authorizationCode : null,
            //             'meta' => $this->paymentData,
            //         ]);
            //     }
            // }
        } catch (Throwable $e) {
            Log::error('ProcessPaystackPayment: Processing failed', [
                'sale_reference'        => $this->saleReference,
                'transaction_reference' => $this->transactionReference,
                'error'                 => $e->getMessage(),
                'trace'                 => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('ProcessPaystackPayment: Job failed permanently', [
            'sale_reference'        => $this->saleReference,
            'transaction_reference' => $this->transactionReference,
            'error'                 => $exception?->getMessage(),
        ]);

        // TODO: Notify admin about the failed payment processing
        // Notification::route('mail', config('app.admin_email'))
        //     ->notify(new PaymentProcessingFailed($this->saleReference, $this->transactionReference, $exception));
    }
}
