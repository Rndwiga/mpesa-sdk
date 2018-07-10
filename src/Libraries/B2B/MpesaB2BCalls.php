<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 4/2/18
 * Time: 6:44 PM
 */

namespace Rndwiga\Mpesa\Libraries\B2B;


use Exception;
use Rndwiga\Payment\Gateway\Libraries\Mpesa\MpesaApiConnection;

class MpesaB2BCalls extends MpesaApiConnection
{

    private $Initiator;
    private $InitiatorPassword;
    private $SecurityCredential;
    private $CommandID;
    private $ConsumerKey;
    private $ConsumerSecret;
    private $SenderIdentifierType;
    private $RecieverIdentifierType;
    private $Amount;
    private $PartyA;
    private $PartyB;
    private $AccountReference;
    private $Remarks;
    private $QueueTimeOutURL;
    private $ResultURL;

    private $ApplicationStatus;

    /**
     * MpesaB2BCalls constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->setApplicationStatus();

        $this->setInitiator($this->getInitiator());
        $this->setSecurityCredential($this->getSecurityCredential());
        $this->setConsumerKey($this->getConsumerKey());
        $this->setConsumerSecret($this->getConsumerSecret());

        $this->setCommandID($this->getCommandID());
        $this->setAccountReference($this->getAccountReference());

        $this->setPartyA($this->getPartyA());
        $this->setSenderIdentifierType($this->getSenderIdentifierType());

        $this->setPartyB($this->getPartyB());
        $this->setRecieverIdentifierType($this->getRecieverIdentifierType());

        $this->setQueueTimeoutUrl($this->getQueueTimeoutUrl());
        $this->setResultUrl($this->getResultUrl());

    }

    public function setApplicationStatus($applicationStatus = null){

        if (!is_null($applicationStatus)){
            $this->ApplicationStatus = $applicationStatus;
        }else{
            $status = env('MMT_MPESA_B2B_INTEGRATION_STATUS') ? env('MMT_MPESA_B2B_INTEGRATION_STATUS') : 'sandbox';
            $this->ApplicationStatus = $status;
        }

    }

    public function setConsumerKey($consumerKey){
        $this->ConsumerKey = $consumerKey;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getConsumerKey(){
        if (env('MMT_MPESA_B2B_CONSUMER_KEY')){
            return env('MMT_MPESA_B2B_CONSUMER_KEY');
        }else{
            throw new Exception("b2B consumer key not set");
        }
        //return config('gateway.module.gateway.mpesa.b2b.B2cConsumerKey');
    }

    public function setConsumerSecret($consumerSecret){
        $this->ConsumerSecret = $consumerSecret;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getConsumerSecret(){
        if (env('MMT_MPESA_B2B_CONSUMER_SECRETE')){
            return env('MMT_MPESA_B2B_CONSUMER_SECRETE');
        }else{
            throw new Exception("b2B consumer secret not set");
        }
        //return config('gateway.module.gateway.mpesa.b2c.B2cConsumerSecret');
    }

    /**
     * @param $initiator | This is the credential/username used to authenticate the transaction request.
     * @return $this
     */
    public function setInitiator($initiator){
        $this->Initiator = $initiator;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getInitiator(){
        if (env('MMT_MPESA_B2B_INITIATOR_NAME')){
            return env('MMT_MPESA_B2B_INITIATOR_NAME');
        }else{
            throw new Exception("initiator name not set");
        }

        //return config('gateway.module.gateway.mpesa.b2b.InitiatorName');
    }

    public function setInitiatorPassword($initiatorPassword){
        $this->InitiatorPassword = $initiatorPassword;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getInitiatorPassword(){
        if (env('MMT_MPESA_B2B_INITIATOR_PASSWORD')){
            return env('MMT_MPESA_B2B_INITIATOR_PASSWORD');
        }else{
            throw new Exception("initiator password not set");
        }

        //return config('gateway.module.gateway.mpesa.b2b.InitiatorPassword');
    }

    /**
     * @param $securityCredential | Base64 encoded string of the B2B short code and password, which is encrypted using M-Pesa public key and validates the transaction on M-Pesa Core system.
     * @return $this
     */
    public function setSecurityCredential($securityCredential){
        $this->SecurityCredential = $securityCredential;
        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getSecurityCredential(){
        if (env('MMT_MPESA_B2B_INITIATOR_PASSWORD')){

            $liveStatus= $this->ApplicationStatus == 'live' ? true : false;

            return self::generateSecurityCredentials(env('MMT_MPESA_B2B_INITIATOR_PASSWORD'),$liveStatus);
            //return self::generateSecurityCredentials(config('gateway.module.gateway.mpesa.b2c.InitiatorPassword'));
        }else{
            throw new Exception("initiator password not set for credential generation");
        }

    }

    /**
     * @param $commandID | Unique command for each transaction type, possible values are: BusinessPayBill, MerchantToMerchantTransfer, MerchantTransferFromMerchantToWorking, MerchantServicesMMFAccountTransfer, AgencyFloatAdvance
     * @return $this
     */
    public function setCommandID($commandID){
        $this->CommandID = $commandID;
        return $this;
    }
    public function getCommandID(){
        [
            "BusinessPayBill","BusinessBuyGoods",
            "DisburseFundsToBusiness","BusinessToBusinessTransfer",
            "MerchantToMerchantTransfer"
        ];
        return "";
    }

    /**
     * @param $senderIdentifierType | Type of organization sending the transaction.
     * @return $this
     */
    public function setSenderIdentifierType($senderIdentifierType){
        $this->SenderIdentifierType = $senderIdentifierType;
        return $this;
    }
    public function getSenderIdentifierType(){
        [
            1 => "MSISDN",
            2 => "Till Number",
            4 => "Organization short code",
        ];
        return "";
    }

    /**
     * @param $recieverIdentifierType | Type of organization receiving the funds being transacted.
     * @return $this
     */
    public function setRecieverIdentifierType($recieverIdentifierType){
        $this->RecieverIdentifierType = $recieverIdentifierType;
        return $this;
    }
    public function getRecieverIdentifierType(){
        [
            1 => "MSISDN",
            2 => "Till Number",
            4 => "Organization short code",
        ];
        return "";
    }

    public function setAmount($amount){
        $this->Amount = $amount;
        return $this;
    }
    public function getAmount(){
        return "";
    }

    /**
     * @param $partyA | Organization’s short code initiating the transaction.
     * @return $this
     */
    public function setPartyA($partyA){
        $this->PartyA = $partyA;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getPartyA(){

        if (env('MMT_MPESA_B2B_SHORT_CODE')){
            return env('MMT_MPESA_B2B_SHORT_CODE');
        }else{
            throw new Exception("b2B shortcode not set");
        }
        //return config('gateway.module.gateway.mpesa.b2c.B2cShortCode');
    }

    /**
     * @param $partyB | Organization’s short code receiving the funds being transacted.
     * @return $this
     */
    public function setPartyB($partyB){
        $this->PartyB = $partyB;
        return $this;
    }
    public function getPartyB(){
        return "";
    }

    /**
     * @param $accountReference | Account Reference mandatory for “BusinessPaybill” CommandID.
     * @return $this
     */
    public function setAccountReference($accountReference){
        $this->AccountReference = $accountReference;
        return $this;
    }
    public function getAccountReference(){
        return "Musoni";
    }

    /**
     * @param $remarks | Comments that are sent along with the transaction.
     * @return $this
     */
    public function setRemarks($remarks){
        $this->Remarks = $remarks;
        return $this;
    }
    public function getRemarks(){
        return "Business Payment To Business";
    }

    /**
     * @param $queueTimeOutURL | The path that stores information of time out transactions.it should be properly validated to make sure that it contains the port, URI and domain name or publicly available IP.
     * @return $this
     */
    public function setQueueTimeoutUrl($queueTimeOutURL){
        $this->QueueTimeOutURL = $queueTimeOutURL;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getQueueTimeoutUrl(){
        if (env('MMT_MPESA_B2B_QUEUE_TIMEOUT_URL')){
            return env('MMT_MPESA_B2B_QUEUE_TIMEOUT_URL');
        }else{
            throw new Exception("b2B queue time-out url not set");
        }

        //return config('gateway.module.gateway.mpesa.b2c.QueueTimeOutURL');
    }

    /**
     * @param $resultURL | The path that receives results from M-Pesa it should be properly validated to make sure that it contains the port, URI and domain name or publicly available IP.
     * @return $this
     */
    public function setResultUrl($resultURL){
        $this->ResultURL = $resultURL;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getResultUrl(){
        if (env('MMT_MPESA_B2B_RESULT_URL')){
            return env('MMT_MPESA_B2B_RESULT_URL');
        }else{
            throw new Exception("b2B result-url not set");
        }

        //return config('gateway.module.gateway.mpesa.b2c.ResultURL');
    }

    public function processSendFundsRequest($callResponse){
        $response = json_decode($callResponse);
        if (isset($response->errorCode)){
            $errorCode = $response->errorCode;
            switch ($errorCode){
                case "404.001.04":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Invalid Authentication Header",
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
                case "500.001.1001":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Server Error",
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
                case "404.001.03":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Invalid Access Token",
                        'errorMessage' => $response->errorMessage
                    ]);

                    break;
                default:
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Unknown error",
                        'errorMessage' => $response->errorMessage
                    ]);
                    break;
            }
        }else{
            //handling successful service request
            if (isset($response->ConversationID)){
                return true;
            }
        }

    }

    /**
     * Use this function to initiate a B2B request
     * @param $SecurityCredential | Base64 encoded string of the B2B short code and password, which is encrypted using M-Pesa public key and validates the transaction on M-Pesa Core system.
     * @param $Amount | Base64 encoded string of the B2B short code and password, which is encrypted using M-Pesa public key and validates the transaction on M-Pesa Core system.
     * @param $PartyA | Organization’s short code initiating the transaction.
     * @param $PartyB | Organization’s short code receiving the funds being transacted.
     * @param $Remarks | Comments that are sent along with the transaction.
     * @param $QueueTimeOutURL | The path that stores information of time out transactions.it should be properly validated to make sure that it contains the port, URI and domain name or publicly available IP.
     * @param $ResultURL | The path that receives results from M-Pesa it should be properly validated to make sure that it contains the port, URI and domain name or publicly available IP.
     * @param $AccountReference | Account Reference mandatory for “BusinessPaybill” CommandID.
     * @param $commandID | Unique command for each transaction type, possible values are: BusinessPayBill, MerchantToMerchantTransfer, MerchantTransferFromMerchantToWorking, MerchantServicesMMFAccountTransfer, AgencyFloatAdvance
     * @param $SenderIdentifierType | Type of organization sending the transaction.
     * @param $RecieverIdentifierType | Type of organization receiving the funds being transacted.

     * @return mixed|string
     */
    public function b2b( $Amount){
        //$live=config('gateway.module.gateway.mpesa.b2b.is_live');

        if ($this->ApplicationStatus == 'live'){
            $live = true;
        }elseif ($this->ApplicationStatus == 'sandbox'){
            $live = false;
        }
        else{
            $live = null;
        }

        if(is_null($live)){
            die("please declare the application status as defined in the documentation");
        }

        if( $live == true){
            $url = 'https://api.safaricom.co.ke/mpesa/b2b/v1/paymentrequest';
        }elseif ($live== false){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest';
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }

        $token = $this->generateAccessToken($live,$this->ConsumerKey,$this->ConsumerSecret);


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token)); //setting custom header
        $curl_post_data = array(
            'Initiator' => $this->Initiator,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID,
            'SenderIdentifierType' => $this->SenderIdentifierType,
            'RecieverIdentifierType' => $this->RecieverIdentifierType,
            'Amount' => $Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => $this->PartyB,
            'AccountReference' => $this->AccountReference,
            'Remarks' => $this->Remarks,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'ResultURL' => $this->ResultURL
        );
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $curl_response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            $curl_response = "cURL Error #:" . $err;
            return $curl_response;
        } else {
            return $curl_response;
        }
    }


    public function getSetProperties($Amount){
        return [
            'ConsumerKey' => $this->ConsumerKey,
            'ConsumerSecret' => $this->ConsumerSecret,
            'Initiator' => $this->Initiator,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID,
            'SenderIdentifierType' => $this->SenderIdentifierType,
            'RecieverIdentifierType' => $this->RecieverIdentifierType,
            'Amount' => $Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => $this->PartyB,
            'AccountReference' => $this->AccountReference,
            'Remarks' => $this->Remarks,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'ResultURL' => $this->ResultURL,
            'applicationStatus' => $this->ApplicationStatus
        ];
    }

}