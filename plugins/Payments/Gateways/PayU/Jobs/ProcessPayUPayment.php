<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\PayU\Jobs;

use Throwable;
use App\Models\Sma\Order\Sale;
use App\Models\Sma\Order\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

final class ProcessPayUPayment implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $saleReference,
        public string $txnid,
        public string $payuId,
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
                Log::error('PayU payment processing failed: Sale not found', [
                    'sale_reference' => $this->saleReference,
                    'txnid'          => $this->txnid,
                    'payu_id'        => $this->payuId,
                ]);

                return;
            }

            // Check if payment already exists to avoid duplicates
            $existingPayment = Payment::where('sale_id', $sale->id)
                ->whereJsonContains('method_data->payu_id', $this->payuId)
                ->first();

            if ($existingPayment) {
                Log::info('PayU payment already processed', [
                    'sale_id'             => $sale->id,
                    'payu_id'             => $this->payuId,
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
                'method'      => 'PayU',
                'method_data' => [
                    'gateway'        => 'payu',
                    'payu_id'        => $this->payuId,
                    'transaction_id' => $this->txnid,
                    'currency'       => $this->currency,
                    'mode'           => $this->paymentData['mode'] ?? null,
                    'bank_ref_num'   => $this->paymentData['bank_ref_num'] ?? null,
                    'bankcode'       => $this->paymentData['bankcode'] ?? null,
                    'cardnum'        => $this->paymentData['cardnum'] ?? null,
                    'name_on_card'   => $this->paymentData['name_on_card'] ?? null,
                    'card_type'      => $this->paymentData['card_type'] ?? null,
                    'pg_type'        => $this->paymentData['PG_TYPE'] ?? null,
                    'payment_data'   => $this->paymentData,
                ],
            ]);

            // PaymentObserver handles attaching payment to sale and updating the paid amount

            Log::info('PayU payment processed successfully', [
                'sale_id'    => $sale->id,
                'payment_id' => $payment->id,
                'payu_id'    => $this->payuId,
                'amount'     => $this->amount,
            ]);
        } catch (Throwable $exception) {
            Log::error('PayU payment processing failed: ' . $exception->getMessage(), [
                'sale_reference' => $this->saleReference,
                'txnid'          => $this->txnid,
                'payu_id'        => $this->payuId,
                'trace'          => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }
}
