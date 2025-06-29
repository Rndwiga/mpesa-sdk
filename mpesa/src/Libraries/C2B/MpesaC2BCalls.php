<?php
/**
 * MpesaC2BCalls
 *
 * Handles Customer to Business (C2B) API calls to the Mpesa API.
 *
 * @package Rndwiga\Mpesa\Libraries\C2B
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Libraries\C2B;

use InvalidArgumentException;
use RuntimeException;
use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaC2BCalls extends MpesaApiConnection
{
    /**
     * @var string The shortcode for the business
     */
    private $ShortCode;

    /**
     * @var string The response type for C2B transactions
     */
    private $ResponseType;

    /**
     * @var string The URL for confirmation notifications
     */
    private $ConfirmationURL;

    /**
     * @var string The URL for validation notifications
     */
    private $ValidationURL;

    /**
     * @var string The command ID for C2B transactions
     */
    private $CommandID;

    /**
     * @var float The amount for the transaction
     */
    private $Amount;

    /**
     * @var string The MSISDN (phone number) sending the transaction
     */
    private $MSISDN;

    /**
     * @var string The bill reference number
     */
    private $BillRefNumber;

    /**
     * @var string The consumer key for API authentication
     */
    private $ConsumerKey;

    /**
     * @var string The consumer secret for API authentication
     */
    private $ConsumerSecret;

    /**
     * @var bool|string The application status (true/false or 'live'/'sandbox')
     */
    private $ApplicationStatus;

    /**
     * @return string
     */
    public function getShortCode(): string
    {
        return $this->ShortCode;
    }

    /**
     * @param string $ShortCode
     * @return MpesaC2BCalls
     */
    public function setShortCode(string $ShortCode): MpesaC2BCalls
    {
        $this->ShortCode = $ShortCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseType(): string
    {
        return $this->ResponseType ?? 'Completed';
    }

    /**
     * @param string $ResponseType
     * @return MpesaC2BCalls
     */
    public function setResponseType(string $ResponseType): MpesaC2BCalls
    {
        $this->ResponseType = $ResponseType;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfirmationURL(): string
    {
        return $this->ConfirmationURL;
    }

    /**
     * @param string $ConfirmationURL
     * @return MpesaC2BCalls
     */
    public function setConfirmationURL(string $ConfirmationURL): MpesaC2BCalls
    {
        $this->ConfirmationURL = $ConfirmationURL;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidationURL(): string
    {
        return $this->ValidationURL;
    }

    /**
     * @param string $ValidationURL
     * @return MpesaC2BCalls
     */
    public function setValidationURL(string $ValidationURL): MpesaC2BCalls
    {
        $this->ValidationURL = $ValidationURL;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommandID(): string
    {
        return $this->CommandID;
    }

    /**
     * @param string $CommandID
     * @return MpesaC2BCalls
     */
    public function setCommandID(string $CommandID): MpesaC2BCalls
    {
        $this->CommandID = $CommandID;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->Amount;
    }

    /**
     * @param float $Amount
     * @return MpesaC2BCalls
     */
    public function setAmount(float $Amount): MpesaC2BCalls
    {
        $this->Amount = $Amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getMSISDN(): string
    {
        return $this->MSISDN;
    }

    /**
     * @param string $MSISDN
     * @return MpesaC2BCalls
     */
    public function setMSISDN(string $MSISDN): MpesaC2BCalls
    {
        $this->MSISDN = $MSISDN;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillRefNumber(): string
    {
        return $this->BillRefNumber;
    }

    /**
     * @param string $BillRefNumber
     * @return MpesaC2BCalls
     */
    public function setBillRefNumber(string $BillRefNumber): MpesaC2BCalls
    {
        $this->BillRefNumber = $BillRefNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getConsumerKey(): string
    {
        return $this->ConsumerKey;
    }

    /**
     * @param string $ConsumerKey
     * @return MpesaC2BCalls
     */
    public function setConsumerKey(string $ConsumerKey): MpesaC2BCalls
    {
        $this->ConsumerKey = $ConsumerKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getConsumerSecret(): string
    {
        return $this->ConsumerSecret;
    }

    /**
     * @param string $ConsumerSecret
     * @return MpesaC2BCalls
     */
    public function setConsumerSecret(string $ConsumerSecret): MpesaC2BCalls
    {
        $this->ConsumerSecret = $ConsumerSecret;
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getApplicationStatus()
    {
        return $this->ApplicationStatus;
    }

    /**
     * @param bool|string $ApplicationStatus
     * @return MpesaC2BCalls
     */
    public function setApplicationStatus($ApplicationStatus): MpesaC2BCalls
    {
        $this->ApplicationStatus = $ApplicationStatus;
        return $this;
    }

    /**
     * Register URLs for C2B transactions with the Mpesa API
     *
     * This method registers the confirmation and validation URLs that will receive
     * notifications for C2B transactions.
     * 
     * Example successful response:
     * ```json
     * {
     *   "ConversationID": "",
     *   "OriginatorCoversationID": "",
     *   "ResponseDescription": "success"
     * }
     * ```
     *
     * Required properties that must be set before calling this method:
     * - ApplicationStatus: true/false or 'live'/'sandbox'
     * - ConsumerKey: Your API consumer key
     * - ConsumerSecret: Your API consumer secret
     * - ShortCode: Your business shortcode
     * - ResponseType: Usually 'Completed'
     * - ConfirmationURL: The URL to receive confirmation notifications
     * - ValidationURL: The URL to receive validation notifications
     *
     * @return string JSON response from the API
     * @throws InvalidArgumentException If required properties are not set
     * @throws RuntimeException If there's an error in the API request
     */
    public function registerURLs(): string
    {
        // Validate required properties
        $requiredProperties = [
            'ConsumerKey', 'ConsumerSecret', 'ShortCode',
            'ConfirmationURL', 'ValidationURL'
        ];

        foreach ($requiredProperties as $property) {
            if (empty($this->$property)) {
                throw new InvalidArgumentException("Required property '{$property}' is not set");
            }
        }

        // Determine if we're using live or sandbox environment
        $isLive = $this->ApplicationStatus === true || $this->ApplicationStatus === 'live';

        // Get the appropriate URL based on environment
        $url = $isLive 
            ? 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl'
            : 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';

        try {
            // Generate access token
            $token = $this->generateAccessToken($isLive, $this->ConsumerKey, $this->ConsumerSecret);

            // Prepare request data
            $requestData = [
                'ShortCode' => $this->ShortCode,
                'ResponseType' => $this->getResponseType(),
                'ConfirmationURL' => $this->ConfirmationURL,
                'ValidationURL' => $this->ValidationURL
            ];

            // Initialize cURL session
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestData));

            // Execute cURL request
            $response = curl_exec($curl);
            $error = curl_error($curl);

            curl_close($curl);

            // Handle cURL errors
            if ($error) {
                throw new RuntimeException("cURL Error: " . $error);
            }

            return $response;

        } catch (\Exception $e) {
            // Convert any exceptions to JSON error response
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Simulate a C2B transaction (for testing in sandbox environment)
     *
     * This method simulates a customer paying to your business shortcode.
     * Note: This method only works in the sandbox environment.
     * 
     * Example successful response:
     * ```json
     * {
     *   "ConversationID": "AG_20180324_00004636fb3ac56655df",
     *   "OriginatorCoversationID": "12345-67890-1",
     *   "ResponseDescription": "Accept the service request successfully."
     * }
     * ```
     *
     * Required properties that must be set before calling this method:
     * - ApplicationStatus: false or 'sandbox'
     * - ConsumerKey: Your API consumer key
     * - ConsumerSecret: Your API consumer secret
     * - ShortCode: Your business shortcode
     * - CommandID: Usually 'CustomerPayBillOnline' or 'CustomerBuyGoodsOnline'
     * - Amount: The amount to simulate
     * - MSISDN: The phone number making the payment
     * - BillRefNumber: Reference number for the transaction
     *
     * @return string JSON response from the API
     * @throws InvalidArgumentException If required properties are not set or if trying to use in production
     * @throws RuntimeException If there's an error in the API request
     */
    public function simulateC2B(): string
    {
        // Check if we're in sandbox environment
        $isLive = $this->ApplicationStatus === true || $this->ApplicationStatus === 'live';
        if ($isLive) {
            throw new InvalidArgumentException("C2B simulation is only available in the sandbox environment");
        }

        // Validate required properties
        $requiredProperties = [
            'ConsumerKey', 'ConsumerSecret', 'ShortCode',
            'CommandID', 'Amount', 'MSISDN', 'BillRefNumber'
        ];

        foreach ($requiredProperties as $property) {
            if (empty($this->$property)) {
                throw new InvalidArgumentException("Required property '{$property}' is not set");
            }
        }

        // Set the URL for sandbox environment
        $url = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate';

        try {
            // Generate access token
            $token = $this->generateAccessToken(false, $this->ConsumerKey, $this->ConsumerSecret);

            // Prepare request data
            $requestData = [
                'ShortCode' => $this->ShortCode,
                'CommandID' => $this->CommandID,
                'Amount' => $this->Amount,
                'Msisdn' => $this->MSISDN,
                'BillRefNumber' => $this->BillRefNumber
            ];

            // Initialize cURL session
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($curl, CURLOPT_HEADER, false);

            // Execute cURL request
            $response = curl_exec($curl);
            $error = curl_error($curl);

            curl_close($curl);

            // Handle cURL errors
            if ($error) {
                throw new RuntimeException("cURL Error: " . $error);
            }

            return $response;

        } catch (\Exception $e) {
            // Convert any exceptions to JSON error response
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
