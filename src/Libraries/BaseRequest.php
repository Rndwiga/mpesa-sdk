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
    protected $ReceiverIdentifierType; //numeric [11 - Organization Identifier on M-Pesa] $RecieverIdentifierType
    protected $ApplicationStatus;


    public function setApplicationStatus(bool $applicationIsLive = false){
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
    }

    /**For B2B calls the options are [BusinessPaybill]
     * @param mixed $AccountReference
     */
    public function setAccountReference($AccountReference)
    {
        $this->AccountReference = $AccountReference;
    }

    /**For Reversal Request the options are [Shortcode]
     * @param mixed $ReceiverParty
     */
    public function setReceiverParty($ReceiverParty)
    {
        $this->ReceiverParty = $ReceiverParty;
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
    }

    /** For Reversal Request
     *
     * @param mixed $TransactionID
     */
    public function setTransactionID($TransactionID)
    {
        $this->TransactionID = $TransactionID;
    }

    /**For Account balance & Transaction Status, the options are [1-MSISDN,2-Till Number,4-Organization short code]
     * @param mixed $IdentifierType
     */
    public function setIdentifierType(int $IdentifierType)
    {
        $this->IdentifierType = $IdentifierType;
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

    public function processRequestResponse($callResponse){

        $response = json_decode($callResponse);
        if (isset($response->errorCode)){
            $errorCode = $response->errorCode;
            switch ($errorCode){
                case "400.002.01":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Invalid Access Token",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
                case "400.002.02":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Bad Request",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
                case "400.002.05":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Invalid Request Payload",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
                case "401.002.01":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Error Occurred - Invalid Access Token",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
                case "404.001.01":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Resource not found",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
                case "404.002.01":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Resource not found",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
                case "404.001.03":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Invalid Access Token",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
                case "404.001.04":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Invalid Authentication Header",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
                case "500.001.1001":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Server Error",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
                case "500.002.1001":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Server Error",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
                case "500.002.02":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Error Occured: Spike Arrest Violation",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
                case "500.002.03":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Error Occured: Quota Violation",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
            }
        }elseif (isset($response->ConversationID)){
            return true;
        }
    }
}