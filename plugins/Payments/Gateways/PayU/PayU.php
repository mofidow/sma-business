<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\PayU;

use RuntimeException;

/**
 * PayU India SDK
 *
 * Based on the official PayU SDK: https://github.com/payu-intrepos/web-sdk-php
 */
class PayU
{
    public const VERIFY_PAYMENT_API = 'verify_payment';

    public const VERIFY_PAYMENT_BY_PAYU_ID_API = 'check_payment';

    public const GET_TRANSACTION_DETAILS_API = 'get_Transaction_Details';

    public const GET_TRANSACTION_INFO_API = 'get_transaction_info';

    public const CANCEL_REFUND_API = 'cancel_refund_transaction';

    public const CHECK_ACTION_STATUS = 'check_action_status';

    public const GET_ALL_TRANSACTION_ID_REFUND_DETAILS_API = 'getAllRefundsFromTxnIds';

    private string $key;

    private string $salt;

    private bool $isProduction;

    private string $paymentUrl;

    private string $apiUrl;

    /** @var array<string, mixed> */
    private array $params = [];

    public function __construct(string $key, string $salt, bool $isProduction = false)
    {
        $this->key = $key;
        $this->salt = $salt;
        $this->isProduction = $isProduction;
        $this->initGateway();
    }

    /**
     * Initialize gateway URLs based on environment
     */
    private function initGateway(): void
    {
        if ($this->isProduction) {
            $this->paymentUrl = 'https://secure.payu.in/_payment';
            $this->apiUrl = 'https://info.payu.in/merchant/postservice.php?form=2';
        } else {
            $this->paymentUrl = 'https://test.payu.in/_payment';
            $this->apiUrl = 'https://test.payu.in/merchant/postservice.php?form=2';
        }
    }

    /**
     * Get the payment URL for form submission
     */
    public function getPaymentUrl(): string
    {
        return $this->paymentUrl;
    }

    /**
     * Get the merchant key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Generate hash for payment initiation
     *
     * @param  array<string, mixed>  $params
     */
    public function generateHash(array $params): string
    {
        $hashString = $this->key . '|'
            . ($params['txnid'] ?? '') . '|'
            . ($params['amount'] ?? '') . '|'
            . ($params['productinfo'] ?? '') . '|'
            . ($params['firstname'] ?? '') . '|'
            . ($params['email'] ?? '') . '|'
            . ($params['udf1'] ?? '') . '|'
            . ($params['udf2'] ?? '') . '|'
            . ($params['udf3'] ?? '') . '|'
            . ($params['udf4'] ?? '') . '|'
            . ($params['udf5'] ?? '') . '||||||'
            . $this->salt;

        return strtolower(hash('sha512', $hashString));
    }

    /**
     * Verify the response hash from PayU
     *
     * @param  array<string, mixed>  $params
     */
    public function verifyHash(array $params): bool
    {
        $status = $params['status'] ?? '';
        $responseHash = $params['hash'] ?? '';

        $hashString = $this->key . '|'
            . ($params['txnid'] ?? '') . '|'
            . ($params['amount'] ?? '') . '|'
            . ($params['productinfo'] ?? '') . '|'
            . ($params['firstname'] ?? '') . '|'
            . ($params['email'] ?? '') . '|'
            . ($params['udf1'] ?? '') . '|'
            . ($params['udf2'] ?? '') . '|'
            . ($params['udf3'] ?? '') . '|'
            . ($params['udf4'] ?? '') . '|'
            . ($params['udf5'] ?? '') . '|||||';

        $hashArray = explode('|', $hashString);
        $reverseHashArray = array_reverse($hashArray);
        $reverseHashString = implode('|', $reverseHashArray);

        $calculatedHash = strtolower(hash('sha512', $this->salt . '|' . $status . '|' . $reverseHashString));

        // Check for presence of additionalCharges parameter in response
        if (isset($params['additionalCharges']) && $params['additionalCharges'] !== '') {
            $calculatedHash = strtolower(hash('sha512', $params['additionalCharges'] . '|' . $this->salt . '|' . $status . '|' . $reverseHashString));
        }

        return hash_equals($calculatedHash, $responseHash);
    }

    /**
     * Verify payment by transaction ID
     *
     * @return array<string, mixed>|false
     */
    public function verifyPaymentByTxnId(string $txnid): array|false
    {
        $this->params['data'] = [
            'var1'    => $txnid,
            'command' => self::VERIFY_PAYMENT_API,
        ];

        $response = $this->execute();

        if (is_array($response) && ($response['status'] ?? false)) {
            $transactions = $response['transaction_details'] ?? [];

            return $transactions[$txnid] ?? false;
        }

        return false;
    }

    /**
     * Verify payment by PayU ID
     *
     * @return array<string, mixed>|false
     */
    public function verifyPaymentByPayuId(string $payuId): array|false
    {
        $this->params['data'] = [
            'var1'    => $payuId,
            'command' => self::VERIFY_PAYMENT_BY_PAYU_ID_API,
        ];

        $response = $this->execute();

        if (is_array($response) && ($response['status'] ?? false)) {
            return $response['transaction_details'] ?? false;
        }

        return false;
    }

    /**
     * Initiate refund for a transaction
     *
     * @return array<string, mixed>|false
     */
    public function initiateRefund(string $payuId, string $txnid, float $amount): array|false
    {
        $this->params['data'] = [
            'var1'    => $payuId,
            'var2'    => $txnid,
            'var3'    => (string) $amount,
            'command' => self::CANCEL_REFUND_API,
        ];

        return $this->execute();
    }

    /**
     * Check refund status by request ID
     *
     * @return array<string, mixed>|false
     */
    public function checkRefundStatus(string $requestId): array|false
    {
        $this->params['data'] = [
            'var1'    => $requestId,
            'command' => self::CHECK_ACTION_STATUS,
        ];

        return $this->execute();
    }

    /**
     * Check refund status by PayU ID
     *
     * @return array<string, mixed>|false
     */
    public function checkRefundStatusByPayuId(string $payuId): array|false
    {
        $this->params['data'] = [
            'var1'    => $payuId,
            'var2'    => 'payuid',
            'command' => self::CHECK_ACTION_STATUS,
        ];

        return $this->execute();
    }

    /**
     * Get all refunds for a transaction ID
     *
     * @return array<string, mixed>|false
     */
    public function getAllRefundsForTransaction(string $txnid): array|false
    {
        $this->params['data'] = [
            'var1'    => $txnid,
            'command' => self::GET_ALL_TRANSACTION_ID_REFUND_DETAILS_API,
        ];

        return $this->execute();
    }

    /**
     * Get transaction details by date range
     *
     * @return array<string, mixed>|false
     */
    public function getTransactionDetails(string $from, string $to, string $type = 'date'): array|false
    {
        $command = $type === 'time'
            ? self::GET_TRANSACTION_INFO_API
            : self::GET_TRANSACTION_DETAILS_API;

        $this->params['data'] = [
            'var1'    => $from,
            'var2'    => $to,
            'command' => $command,
        ];

        return $this->execute();
    }

    /**
     * Execute API call
     *
     * @return array<string, mixed>|false
     */
    private function execute(): array|false
    {
        $data = $this->params['data'] ?? [];

        $hashString = $this->key . '|' . $data['command'] . '|' . ($data['var1'] ?? '') . '|' . $this->salt;

        $data['key'] = $this->key;
        $data['hash'] = strtolower(hash('sha512', $hashString));

        return $this->makeRequest($data);
    }

    /**
     * Make HTTP request to PayU API
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|false
     */
    private function makeRequest(array $data): array|false
    {
        $postData = http_build_query($data);

        $ch = curl_init();

        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL');
        }

        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error !== '') {
            throw new RuntimeException('PayU API request failed: ' . $error);
        }

        if (! is_string($response) || $response === '') {
            return false;
        }

        $decoded = json_decode($response, true);

        return is_array($decoded) ? $decoded : false;
    }

    /**
     * Check if payment is successful based on status
     */
    public static function isPaymentSuccessful(string $status): bool
    {
        return in_array(strtolower($status), ['success', 'captured'], true);
    }
}
