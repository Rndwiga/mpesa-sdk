<?php
/**
 * C2B
 *
 * Handles Customer to Business (C2B) API operations.
 *
 * @package Rndwiga\Mpesa\Api
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Api;

use InvalidArgumentException;
use Rndwiga\Mpesa\Client\MpesaClient;

class C2B extends BaseApi
{
    /**
     * Command ID options for C2B
     */
    public const COMMAND_ID_CUSTOMER_PAYBILL_ONLINE = 'CustomerPayBillOnline';
    public const COMMAND_ID_CUSTOMER_BUY_GOODS_ONLINE = 'CustomerBuyGoodsOnline';

    /**
     * Confirmation and validation response types
     */
    public const RESPONSE_TYPE_COMPLETED = 'Completed';
    public const RESPONSE_TYPE_CANCELLED = 'Cancelled';

    /**
     * Shortcode (the organization's shortcode)
     *
     * @var string
     */
    protected $shortcode;

    /**
     * Command ID (type of transaction)
     *
     * @var string
     */
    protected $commandId;

    /**
     * Amount to be sent
     *
     * @var int
     */
    protected $amount;

    /**
     * MSISDN (phone number) sending the transaction
     *
     * @var string
     */
    protected $msisdn;

    /**
     * Bill reference number
     *
     * @var string
     */
    protected $billRefNumber;

    /**
     * Confirmation URL for the transaction
     *
     * @var string
     */
    protected $confirmationUrl;

    /**
     * Validation URL for the transaction
     *
     * @var string
     */
    protected $validationUrl;

    /**
     * Response type for validation
     *
     * @var string
     */
    protected $responseType;

    /**
     * Get the API endpoint for C2B operations
     *
     * @return string The API endpoint
     */
    protected function getEndpoint(): string
    {
        return self::C2B_SIMULATE_ENDPOINT;
    }

    /**
     * Get the API endpoint for C2B URL registration
     *
     * @return string The API endpoint
     */
    protected function getRegisterUrlEndpoint(): string
    {
        return self::C2B_REGISTER_URL_ENDPOINT;
    }

    /**
     * Set the shortcode
     *
     * @param string $shortcode The shortcode
     * @return $this
     */
    public function setShortcode(string $shortcode): self
    {
        $this->shortcode = $shortcode;
        return $this;
    }

    /**
     * Set the command ID
     *
     * @param string $commandId The command ID
     * @return $this
     */
    public function setCommandId(string $commandId = self::COMMAND_ID_CUSTOMER_PAYBILL_ONLINE): self
    {
        $validCommandIds = [
            self::COMMAND_ID_CUSTOMER_PAYBILL_ONLINE,
            self::COMMAND_ID_CUSTOMER_BUY_GOODS_ONLINE
        ];

        if (!in_array($commandId, $validCommandIds)) {
            throw new InvalidArgumentException("Invalid command ID for C2B: {$commandId}");
        }

        $this->commandId = $commandId;
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
     * Set the MSISDN (phone number)
     *
     * @param string|int $msisdn The MSISDN
     * @return $this
     */
    public function setMsisdn($msisdn): self
    {
        $this->msisdn = $this->validatePhoneNumber($msisdn);
        return $this;
    }

    /**
     * Set the bill reference number
     *
     * @param string $billRefNumber The bill reference number
     * @return $this
     */
    public function setBillRefNumber(string $billRefNumber): self
    {
        $this->billRefNumber = $billRefNumber;
        return $this;
    }

    /**
     * Set the confirmation URL
     *
     * @param string $confirmationUrl The confirmation URL
     * @return $this
     */
    public function setConfirmationUrl(string $confirmationUrl): self
    {
        $this->confirmationUrl = $confirmationUrl;
        return $this;
    }

    /**
     * Set the validation URL
     *
     * @param string $validationUrl The validation URL
     * @return $this
     */
    public function setValidationUrl(string $validationUrl): self
    {
        $this->validationUrl = $validationUrl;
        return $this;
    }

    /**
     * Set the response type
     *
     * @param string $responseType The response type
     * @return $this
     */
    public function setResponseType(string $responseType = self::RESPONSE_TYPE_COMPLETED): self
    {
        $validResponseTypes = [
            self::RESPONSE_TYPE_COMPLETED,
            self::RESPONSE_TYPE_CANCELLED
        ];

        if (!in_array($responseType, $validResponseTypes)) {
            throw new InvalidArgumentException("Invalid response type: {$responseType}");
        }

        $this->responseType = $responseType;
        return $this;
    }

    /**
     * Register URLs for C2B
     *
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return mixed The API response
     */
    public function registerUrls(bool $verifySSL = true)
    {
        // Validate required fields
        if (!isset($this->shortcode) || !isset($this->responseType) || 
            !isset($this->confirmationUrl) || !isset($this->validationUrl)) {
            throw new InvalidArgumentException("Missing required parameters for C2B URL registration");
        }

        // Prepare request data
        $requestData = [
            'ShortCode' => $this->shortcode,
            'ResponseType' => $this->responseType,
            'ConfirmationURL' => $this->confirmationUrl,
            'ValidationURL' => $this->validationUrl
        ];

        return $this->client->post($this->getRegisterUrlEndpoint(), $requestData, $verifySSL);
    }

    /**
     * Simulate a C2B transaction
     *
     * @param int|null $amount The amount to transfer (optional if already set)
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return mixed The API response
     */
    public function simulate($amount = null, bool $verifySSL = true)
    {
        // Set the amount if it's provided as a parameter
        if ($amount !== null) {
            $this->setAmount($amount);
        }

        // Validate required fields
        if (!isset($this->shortcode) || !isset($this->commandId) || 
            !isset($this->amount) || !isset($this->msisdn) || 
            !isset($this->billRefNumber)) {
            throw new InvalidArgumentException("Missing required parameters for C2B simulation");
        }

        // Prepare request data
        $requestData = [
            'ShortCode' => $this->shortcode,
            'CommandID' => $this->commandId,
            'Amount' => $this->amount,
            'Msisdn' => $this->msisdn,
            'BillRefNumber' => $this->billRefNumber
        ];

        return $this->sendRequest($requestData, $verifySSL);
    }

    /**
     * Process a C2B callback
     *
     * @param array $callbackData The callback data from Mpesa
     * @return array The processed callback data
     */
    public function processCallback(array $callbackData): array
    {
        // Extract the relevant data from the callback
        $transactionType = $callbackData['TransactionType'] ?? null;
        $transactionId = $callbackData['TransID'] ?? null;
        $transactionTime = $callbackData['TransTime'] ?? null;
        $amount = $callbackData['TransAmount'] ?? null;
        $businessShortCode = $callbackData['BusinessShortCode'] ?? null;
        $billRefNumber = $callbackData['BillRefNumber'] ?? null;
        $invoiceNumber = $callbackData['InvoiceNumber'] ?? null;
        $phoneNumber = $callbackData['MSISDN'] ?? null;
        $firstName = $callbackData['FirstName'] ?? null;
        $middleName = $callbackData['MiddleName'] ?? null;
        $lastName = $callbackData['LastName'] ?? null;

        return [
            'success' => true,
            'data' => [
                'transactionType' => $transactionType,
                'transactionId' => $transactionId,
                'transactionTime' => $transactionTime,
                'amount' => $amount,
                'businessShortCode' => $businessShortCode,
                'billRefNumber' => $billRefNumber,
                'invoiceNumber' => $invoiceNumber,
                'phoneNumber' => $phoneNumber,
                'firstName' => $firstName,
                'middleName' => $middleName,
                'lastName' => $lastName
            ]
        ];
    }
}