<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\PayU;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Plugins\Payments\Gateways\PayU\Jobs\ProcessPayUPayment;

class PayUController extends Controller
{
    /**
     * Handle successful payment callback from PayU
     */
    public function success(Request $request): RedirectResponse
    {
        try {
            $params = $request->all();

            Log::info('PayU success callback received', [
                'txnid'  => $params['txnid'] ?? null,
                'status' => $params['status'] ?? null,
            ]);

            $sdk = $this->getSdk();

            // Verify the hash
            if (! $sdk->verifyHash($params)) {
                Log::warning('PayU hash verification failed', ['txnid' => $params['txnid'] ?? null]);

                return $this->redirectWithError($request, 'Payment verification failed. Invalid hash.');
            }

            $status = $params['status'] ?? '';

            if (! PayU::isPaymentSuccessful($status)) {
                Log::warning('PayU payment not successful', [
                    'txnid'  => $params['txnid'] ?? null,
                    'status' => $status,
                ]);

                return $this->redirectWithError($request, 'Payment was not successful. Status: ' . $status);
            }

            // Extract payment details
            $txnid = $params['txnid'] ?? '';
            $payuId = $params['mihpayid'] ?? '';
            $amount = (float) ($params['amount'] ?? 0);
            $saleId = $params['udf1'] ?? '';
            $saleReference = $params['udf2'] ?? '';

            if ($saleReference === '' || $payuId === '') {
                Log::warning('PayU callback missing required fields', [
                    'txnid'          => $txnid,
                    'payu_id'        => $payuId,
                    'sale_reference' => $saleReference,
                ]);

                return $this->redirectWithError($request, 'Payment verification failed. Missing required fields.');
            }

            // Dispatch job to process the payment
            ProcessPayUPayment::dispatch(
                saleReference: $saleReference,
                txnid: $txnid,
                payuId: $payuId,
                paymentData: $params,
                amount: $amount,
                currency: 'INR'
            );

            return $this->redirectWithSuccess($request, 'Payment completed successfully.');
        } catch (Throwable $exception) {
            Log::error('PayU success callback error: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
            ]);

            return $this->redirectWithError($request, 'Payment verification failed: ' . $exception->getMessage());
        }
    }

    /**
     * Handle failed payment callback from PayU
     */
    public function failure(Request $request): RedirectResponse
    {
        $params = $request->all();

        Log::warning('PayU payment failure callback', [
            'txnid'          => $params['txnid'] ?? null,
            'status'         => $params['status'] ?? null,
            'error_message'  => $params['error_Message'] ?? $params['error'] ?? null,
            'unmappedstatus' => $params['unmappedstatus'] ?? null,
        ]);

        $errorMessage = $params['error_Message'] ?? $params['error'] ?? 'Payment failed or was cancelled.';

        return $this->redirectWithError($request, $errorMessage);
    }

    /**
     * Handle webhook notifications from PayU
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $params = $request->all();

            Log::info('PayU webhook received', [
                'txnid'  => $params['txnid'] ?? null,
                'status' => $params['status'] ?? null,
            ]);

            $sdk = $this->getSdk();

            // Verify the hash
            if (! $sdk->verifyHash($params)) {
                Log::warning('PayU webhook hash verification failed', ['txnid' => $params['txnid'] ?? null]);

                return response()->json(['status' => 'error', 'message' => 'Invalid hash'], 400);
            }

            $status = $params['status'] ?? '';
            $txnid = $params['txnid'] ?? '';
            $payuId = $params['mihpayid'] ?? '';
            $saleReference = $params['udf2'] ?? '';

            if (PayU::isPaymentSuccessful($status)) {
                if ($saleReference !== '' && $payuId !== '') {
                    ProcessPayUPayment::dispatch(
                        saleReference: $saleReference,
                        txnid: $txnid,
                        payuId: $payuId,
                        paymentData: $params,
                        amount: (float) ($params['amount'] ?? 0),
                        currency: 'INR'
                    );
                }

                Log::info('PayU webhook processed - payment successful', [
                    'txnid'          => $txnid,
                    'sale_reference' => $saleReference,
                ]);
            } else {
                Log::info('PayU webhook processed - payment not successful', [
                    'txnid'  => $txnid,
                    'status' => $status,
                ]);
            }

            return response()->json(['status' => 'success']);
        } catch (Throwable $exception) {
            Log::error('PayU webhook error: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    /**
     * Get the PayU SDK instance
     */
    private function getSdk(): PayU
    {
        return new PayU(
            (string) config('services.payu.key'),
            (string) config('services.payu.salt'),
            ! (bool) config('services.payu.test_mode', true)
        );
    }

    /**
     * Redirect with success message
     */
    private function redirectWithSuccess(Request $request, string $message): RedirectResponse
    {
        $returnUrl = $request->input('return_url');

        if ($returnUrl) {
            return redirect($returnUrl)->with('success', $message);
        }

        return redirect()->route('home')->with('success', $message);
    }

    /**
     * Redirect with error message
     */
    private function redirectWithError(Request $request, string $message): RedirectResponse
    {
        $returnUrl = $request->input('return_url');

        if ($returnUrl) {
            return redirect($returnUrl)->with('error', $message);
        }

        return redirect()->back()->with('error', $message);
    }
}
