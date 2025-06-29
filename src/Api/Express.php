<?php
/**
 * Express
 *
 * Handles Mpesa Express (STK Push) API operations.
 *
 * @package Rndwiga\Mpesa\Api
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Api;

use InvalidArgumentException;
use Rndwiga\Mpesa\Client\MpesaClient;
use Rndwiga\Mpesa\Utils\MpesaUtils;

class Express extends BaseApi
{
    /**
     * Transaction types
     */
    public const TRANSACTION_TYPE_CUSTOMER_PAYBILL_ONLINE = 'CustomerPayBillOnline';
    public const TRANSACTION_TYPE_CUSTOMER_BUY_GOODS_ONLINE = 'CustomerBuyGoodsOnline';

    /**
     * Business shortcode
     *
     * @var string
     */
    protected $businessShortCode;

    /**
     * Password
     *
     * @var string
     */
    protected $password;

    /**
     * Timestamp
     *
     * @var string
     */
    protected $timestamp;

    /**
     * Transaction type
     *
     * @var string
     */
    protected $transactionType;

    /**
     * Amount
     *
     * @var int
     */
    protected $amount;

    /**
     * Party A (phone number)
     *
     * @var string
     */
    protected $partyA;

    /**
     * Party B (shortcode)
     *
     * @var string
     */
    protected $partyB;

    /**
     * Phone number
     *
     * @var string
     */
    protected $phoneNumber;

    /**
     * Callback URL
     *
     * @var string
     */
    protected $callbackUrl;

    /**
     * Account reference
     *
     * @var string
     */
    protected $accountReference;

    /**
     * Transaction description
     *
     * @var string
     */
    protected $transactionDesc;

    /**
     * Passkey
     *
     * @var string
     */
    protected $passkey;

    /**
     * Get the API endpoint for Express operations
     *
     * @return string The API endpoint
     */
    protected function getEndpoint(): string
    {
        return self::EXPRESS_ENDPOINT;
    }

    /**
     * Get the API endpoint for Express query operations
     *
     * @return string The API endpoint
     */
    protected function getQueryEndpoint(): string
    {
        return self::EXPRESS_QUERY_ENDPOINT;
    }

    /**
     * Set the business shortcode
     *
     * @param string $businessShortCode The business shortcode
     * @return $this
     */
    public function setBusinessShortCode(string $businessShortCode): self
    {
        $this->businessShortCode = $businessShortCode;
        return $this;
    }

    /**
     * Set the passkey
     *
     * @param string $passkey The passkey
     * @return $this
     */
    public function setPasskey(string $passkey): self
    {
        $this->passkey = $passkey;
        return $this;
    }

    /**
     * Set the timestamp
     *
     * @param string|null $timestamp The timestamp (optional, will use current time if not provided)
     * @return $this
     */
    public function setTimestamp(string $timestamp = null): self
    {
        $this->timestamp = $timestamp ?? date('YmdHis');
        return $this;
    }

    /**
     * Set the password
     *
     * @return $this
     */
    public function setPassword(): self
    {
        if (!isset($this->businessShortCode) || !isset($this->passkey) || !isset($this->timestamp)) {
            throw new InvalidArgumentException("Business shortcode, passkey, and timestamp must be set before setting password");
        }

        $this->password = base64_encode($this->businessShortCode . $this->passkey . $this->timestamp);
        return $this;
    }

    /**
     * Set the transaction type
     *
     * @param string $transactionType The transaction type
     * @return $this
     */
    public function setTransactionType(string $transactionType = self::TRANSACTION_TYPE_CUSTOMER_PAYBILL_ONLINE): self
    {
        $validTransactionTypes = [
            self::TRANSACTION_TYPE_CUSTOMER_PAYBILL_ONLINE,
            self::TRANSACTION_TYPE_CUSTOMER_BUY_GOODS_ONLINE
        ];

        if (!in_array($transactionType, $validTransactionTypes)) {
            throw new InvalidArgumentException("Invalid transaction type: {$transactionType}");
        }

        $this->transactionType = $transactionType;
        return $this;
    }

    /**
     * Set the amount
     *
     * @param int $amount The amount
     * @return $this
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $this->validateAmount($amount);
        return $this;
    }

    /**
     * Set the party A (phone number)
     *
     * @param string|int $partyA The party A
     * @return $this
     */
    public function setPartyA($partyA): self
    {
        $this->partyA = $this->validatePhoneNumber($partyA);
        return $this;
    }

    /**
     * Set the party B (shortcode)
     *
     * @param string $partyB The party B
     * @return $this
     */
    public function setPartyB(string $partyB): self
    {
        $this->partyB = $partyB;
        return $this;
    }

    /**
     * Set the phone number
     *
     * @param string|int $phoneNumber The phone number
     * @return $this
     */
    public function setPhoneNumber($phoneNumber): self
    {
        $this->phoneNumber = $this->validatePhoneNumber($phoneNumber);
        return $this;
    }

    /**
     * Set the callback URL
     *
     * @param string $callbackUrl The callback URL
     * @return $this
     */
    public function setCallbackUrl(string $callbackUrl): self
    {
        $this->callbackUrl = $callbackUrl;
        return $this;
    }

    /**
     * Set the account reference
     *
     * @param string $accountReference The account reference
     * @return $this
     */
    public function setAccountReference(string $accountReference): self
    {
        $this->accountReference = $accountReference;
        return $this;
    }

    /**
     * Set the transaction description
     *
     * @param string $transactionDesc The transaction description
     * @return $this
     */
    public function setTransactionDesc(string $transactionDesc): self
    {
        $this->transactionDesc = $transactionDesc;
        return $this;
    }

    /**
     * Initiate an STK push
     *
     * @param int|null $amount The amount (optional if already set)
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return mixed The API response
     */
    public function push($amount = null, bool $verifySSL = true)
    {
        // Set the amount if it's provided as a parameter
        if ($amount !== null) {
            $this->setAmount($amount);
        }

        // Set timestamp if not already set
        if (!isset($this->timestamp)) {
            $this->setTimestamp();
        }

        // Set password if not already set
        if (!isset($this->password)) {
            $this->setPassword();
        }

        // Validate required fields
        if (!isset($this->businessShortCode) || !isset($this->password) || 
            !isset($this->timestamp) || !isset($this->transactionType) || 
            !isset($this->amount) || !isset($this->partyA) || 
            !isset($this->partyB) || !isset($this->phoneNumber) || 
            !isset($this->callbackUrl) || !isset($this->accountReference) || 
            !isset($this->transactionDesc)) {
            throw new InvalidArgumentException("Missing required parameters for STK push");
        }

        // Prepare request data
        $requestData = [
            'BusinessShortCode' => $this->businessShortCode,
            'Password' => $this->password,
            'Timestamp' => $this->timestamp,
            'TransactionType' => $this->transactionType,
            'Amount' => $this->amount,
            'PartyA' => $this->partyA,
            'PartyB' => $this->partyB,
            'PhoneNumber' => $this->phoneNumber,
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => $this->accountReference,
            'TransactionDesc' => $this->transactionDesc
        ];

        return $this->sendRequest($requestData, $verifySSL);
    }

    /**
     * Query the status of an STK push transaction
     *
     * @param string $checkoutRequestId The checkout request ID
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return mixed The API response
     */
    public function query(string $checkoutRequestId, bool $verifySSL = true)
    {
        // Set timestamp if not already set
        if (!isset($this->timestamp)) {
            $this->setTimestamp();
        }

        // Set password if not already set
        if (!isset($this->password)) {
            $this->setPassword();
        }

        // Validate required fields
        if (!isset($this->businessShortCode) || !isset($this->password) || !isset($this->timestamp)) {
            throw new InvalidArgumentException("Missing required parameters for STK query");
        }

        // Prepare request data
        $requestData = [
            'BusinessShortCode' => $this->businessShortCode,
            'Password' => $this->password,
            'Timestamp' => $this->timestamp,
            'CheckoutRequestID' => $checkoutRequestId
        ];

        return $this->client->post($this->getQueryEndpoint(), $requestData, $verifySSL);
    }

    /**
     * Process an STK push callback
     *
     * @param array $callbackData The callback data from Mpesa
     * @return array The processed callback data
     */
    public function processCallback(array $callbackData): array
    {
        // Extract the relevant data from the callback
        $body = $callbackData['Body'] ?? [];
        $stkCallback = $body['stkCallback'] ?? [];
        
        $merchantRequestId = $stkCallback['MerchantRequestID'] ?? null;
        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;
        $resultDesc = $stkCallback['ResultDesc'] ?? null;
        
        $callbackMetadata = $stkCallback['CallbackMetadata'] ?? [];
        $items = $callbackMetadata['Item'] ?? [];
        
        $amount = null;
        $mpesaReceiptNumber = null;
        $transactionDate = null;
        $phoneNumber = null;
        
        foreach ($items as $item) {
            $name = $item['Name'] ?? '';
            $value = $item['Value'] ?? null;
            
            switch ($name) {
                case 'Amount':
                    $amount = $value;
                    break;
                case 'MpesaReceiptNumber':
                    $mpesaReceiptNumber = $value;
                    break;
                case 'TransactionDate':
                    $transactionDate = $value;
                    break;
                case 'PhoneNumber':
                    $phoneNumber = $value;
                    break;
            }
        }
        
        return [
            'success' => $resultCode === 0,
            'data' => [
                'merchantRequestId' => $merchantRequestId,
                'checkoutRequestId' => $checkoutRequestId,
                'resultCode' => $resultCode,
                'resultDesc' => $resultDesc,
                'amount' => $amount,
                'mpesaReceiptNumber' => $mpesaReceiptNumber,
                'transactionDate' => $transactionDate,
                'phoneNumber' => $phoneNumber
            ]
        ];
    }
}