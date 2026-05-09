<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Razorpay\Jobs;

use Throwable;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Order\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

final class ProcessRazorpayPayment implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $saleReference,
        public string $paymentId,
        public string $orderId,
        public array $paymentData,
        public float $amount,
        public string $currency,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $sale = Sale::where('reference', $this->saleReference)->first();

            if (! $sale) {
                Log::error('Razorpay payment processing failed: Sale not found', [
                    'sale_reference' => $this->saleReference,
                    'payment_id'     => $this->paymentId,
                ]);

                return;
            }

            $existingPayment = Payment::where('sale_id', $sale->id)
                ->whereJsonContains('method_data->transaction_id', $this->orderId)
                ->first();

            if ($existingPayment) {
                Log::info('Razorpay payment already processed', [
                    'sale_id'             => $sale->id,
                    'payment_id'          => $this->paymentId,
                    'existing_payment_id' => $existingPayment->id,
                ]);

                return;
            }

            $payment = Payment::create([
                'sale_id'     => $sale->id,
                'customer_id' => $sale->customer_id,
                'store_id'    => $sale->store_id,
                'user_id'     => $sale->user_id,
                'date'        => now(),
                'amount'      => $this->amount,
                'received'    => true,
                'payment_for' => 'Customer',
                'method'      => 'Razorpay',
                'method_data' => [
                    'gateway'        => 'razorpay',
                    'payment_id'     => $this->paymentId,
                    'transaction_id' => $this->orderId,
                    'currency'       => $this->currency,
                    'payment_data'   => $this->paymentData,
                ],
            ]);

            // PaymentObserver handles attaching payment to sale and updating the paid amount

            Log::info('Razorpay payment processed successfully', [
                'sale_id'    => $sale->id,
                'payment_id' => $payment->id,
                'amount'     => $this->amount,
            ]);
        } catch (Throwable $exception) {
            Log::error('Razorpay payment processing failed: ' . $exception->getMessage(), [
                'sale_reference' => $this->saleReference,
                'payment_id'     => $this->paymentId,
                'trace'          => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['razorpay', 'payment', 'sale:' . $this->saleReference];
    }
}
