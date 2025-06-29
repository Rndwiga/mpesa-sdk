<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 10/30/18
 * Time: 6:28 PM
 */

namespace Rndwiga\Mpesa\Libraries;


class BaseRequest extends MpesaApiConnection
{
    // API endpoints
    const LIVE_BASE_URL = 'https://api.safaricom.co.ke';
    const SANDBOX_BASE_URL = 'https://sandbox.safaricom.co.ke';

    // B2C endpoints
    const B2C_ENDPOINT = '/mpesa/b2c/v1/paymentrequest';
    const B2C_VALIDATE_ENDPOINT = '/mpesa/b2c-validate-id/v1.0.1/paymentrequest';

    // B2B endpoints
    const B2B_ENDPOINT = '/mpesa/b2b/v1/paymentrequest';

    //B2C Payment Request
    protected $InitiatorName; //string initiator_1
    private $InitiatorPassword;
    protected $SecurityCredential; //alpha-num
    protected $CommandID; //alpha-num [SalaryPayment(both),BusinessPayment(registered),PromotionPayment(registered)]
    protected $Amount; //number 30671
    protected $PartyA; //number -Shortcode (5-6 digits) e.g. 123454
    protected $PartyB; //number  - Customer mobile number: 254722000000
    protected $Remarks; //string
    protected $QueueTimeOutURL; //url
    protected $ResultURL; //url
    protected $Occasion; //string
    protected $ConsumerKey;
    protected $ConsumerSecret;
    protected $SenderIdentifierType;
    protected $IdentifierType;
    protected $TransactionID; //alpha-num
    protected $AccountReference;
    protected $ReceiverParty; //numeric [Shortcode]
    protected $ReceiverIdentifierType; //numeric [11 - Organization Identifier on M-Pesa]
    protected $ApplicationStatus; // true for live, false for sandbox


    public function setApplicationStatus(bool $applicationIsLive){
        $this->ApplicationStatus = $applicationIsLive;
        return $this;
    }
    public function setInitiatorName(string $initiatorName){
        $this->InitiatorName = $initiatorName;
        return $this;
    }
    public function setInitiatorPassword(string $initiatorPassword){
        $this->InitiatorPassword = $initiatorPassword;
        return $this;
    }

    /**For B2B Calls
     * @param mixed $SenderIdentifierType
     */
    public function setSenderIdentifierType($SenderIdentifierType)
    {
        $this->SenderIdentifierType = $SenderIdentifierType;
        return $this;
    }

    /**For B2B calls the options are [BusinessPaybill]
     * @param mixed $AccountReference
     */
    public function setAccountReference($AccountReference)
    {
        $this->AccountReference = $AccountReference;
        return $this;
    }

    /**For Reversal Request the options are [Shortcode]
     * @param mixed $ReceiverParty
     */
    public function setReceiverParty($ReceiverParty)
    {
        $this->ReceiverParty = $ReceiverParty;
        return $this;
    }

    /**For Reversal Request the options are [11 - Organization Identifier on M-Pesa]
     * For B2B the options are [
                1 => "MSISDN",
                2 => "Till Number",
                4 => "Organization short code",
                ]
     * @param mixed $ReceiverIdentifierType
     */
    public function setReceiverIdentifierType(int $ReceiverIdentifierType = 11)
    {
        $this->ReceiverIdentifierType = $ReceiverIdentifierType;
        return $this;
    }

    /** For Reversal Request
     *
     * @param mixed $TransactionID
     */
    public function setTransactionID($TransactionID)
    {
        $this->TransactionID = $TransactionID;
        return $this;
    }

    /**For Account balance & Transaction Status, the options are [1-MSISDN,2-Till Number,4-Organization short code]
     * @param mixed $IdentifierType
     */
    public function setIdentifierType(int $IdentifierType)
    {
        $this->IdentifierType = $IdentifierType;
        return $this;
    }

    public function setSecurityCredential(string $initiatorPassword){
        $this->SecurityCredential = self::generateSecurityCredentials($initiatorPassword,$this->ApplicationStatus);
        return $this;
    }

    public function setConsumerKey(string $consumerKey){
        $this->ConsumerKey = $consumerKey;
        return $this;
    }
    public function setConsumerSecret(string $consumerSecret){
        $this->ConsumerSecret = $consumerSecret;
        return $this;

    }

    /** For B2C Calls the options are [SalaryPayment-(both),BusinessPayment-(registered),PromotionPayment-(registered)]
     * For Account Balance the options are [AccountBalance]
     * For transaction status the options are  [TransactionStatusQuery]
     * For transaction reversal the options are [TransactionReversal]
     * For B2B the options are [BusinessPayBill, MerchantToMerchantTransfer,
     *                              MerchantTransferFromMerchantToWorking, MerchantServicesMMFAccountTransfer,
     *                          AgencyFloatAdvance]
     * @param string $commandID
     * @return $this
     */
    public function setCommandId(string $commandID = "BusinessPayment"){
        $this->CommandID = $commandID;
        return $this;
    }
    public function setAmount(int $amount){
        $this->Amount = $amount;
        return $this;
    }
    public function setPartyA(int $partyA){
        $this->PartyA = $partyA;
        return $this;
    }

    /**For B2C the option is customer mobile number 254722000000
     * @param int $partyB
     * @return $this
     */
    public function setPartyB(int $partyB){
        $this->PartyB = $partyB;
        return $this;
    }
    public function setRemarks(string $remarks ="Business Payment To Client"){
        $this->Remarks = $remarks;
        return $this;
    }
    public function setQueueTimeOutUrl(string $queueTimeOutURL){
        $this->QueueTimeOutURL = $queueTimeOutURL;
        return $this;
    }
    public function setResultUrl(string $resultURL){
        $this->ResultURL = $resultURL;
        return $this;
    }
    public function setOccasion(string $occasion = "Business Payment To Client"){
        $this->Occasion = $occasion;
        return $this;
    }

    /**
     * Make an HTTP request to the Mpesa API
     *
     * @param string $endpoint The API endpoint to call
     * @param array $data The data to send in the request
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return string The response from the API
     */
    protected function makeApiRequest($endpoint, $data, $verifySSL = true)
    {
        if (!isset($this->ApplicationStatus)) {
            throw new \InvalidArgumentException("Please declare the application status as defined in the documentation");
        }

        $baseUrl = $this->ApplicationStatus === true ? self::LIVE_BASE_URL : self::SANDBOX_BASE_URL;
        $url = $baseUrl . $endpoint;

        if (!isset($this->ConsumerKey) || !isset($this->ConsumerSecret)) {
            throw new \InvalidArgumentException("Consumer key and secret must be set");
        }

        $token = $this->generateAccessToken($this->ApplicationStatus, $this->ConsumerKey, $this->ConsumerSecret);

        return $this->makePostRequest($url, $data, $token, $verifySSL);
    }

    /**
     * Get the common request data for API calls
     *
     * @return array The common request data
     */
    protected function getCommonRequestData()
    {
        return [
            'InitiatorName' => $this->InitiatorName,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID,
            'Amount' => $this->Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => $this->PartyB,
            'Remarks' => $this->Remarks,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'ResultURL' => $this->ResultURL,
            'Occasion' => $this->Occasion
        ];
    }

    /**
     * Process the response from the Mpesa API
     *
     * @param string $callResponse The response from the API
     * @return mixed The processed response
     */
    public function processRequestResponse($callResponse)
    {
        $response = json_decode($callResponse);

        if (isset($response->errorCode)) {
            $errorCode = $response->errorCode;
            $errorDescriptions = [
                "400.002.01" => "Invalid Access Token",
                "400.002.02" => "Bad Request",
                "400.002.05" => "Invalid Request Payload",
                "401.002.01" => "Error Occurred - Invalid Access Token",
                "404.001.01" => "Resource not found",
                "404.002.01" => "Resource not found",
                "404.001.03" => "Invalid Access Token",
                "404.001.04" => "Invalid Authentication Header",
                "500.001.1001" => "Server Error",
                "500.002.1001" => "Server Error",
                "500.002.02" => "Error Occurred: Spike Arrest Violation",
                "500.002.03" => "Error Occurred: Quota Violation"
            ];

            $description = isset($errorDescriptions[$errorCode]) ? $errorDescriptions[$errorCode] : "Unknown Error";

            return json_encode([
                'errorCode' => $errorCode,
                'errorRequestId' => $response->requestId ?? null,
                'errorDescription' => $description,
                'errorMessage' => $response->errorMessage ?? null
            ]);
        } elseif (isset($response->ConversationID)) {
            return true;
        }

        return $callResponse;
    }
}
