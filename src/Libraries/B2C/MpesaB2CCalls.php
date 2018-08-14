<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 4/2/18
 * Time: 6:47 PM
 */

namespace Rndwiga\Mpesa\Libraries\B2C;

use Exception;

use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaB2CCalls extends MpesaApiConnection
{
    private $InitiatorName;
    private $InitiatorPassword;
    private $SecurityCredential;
    private $ConsumerKey;
    private $ConsumerSecret;
    private $CommandID;
    private $Amount;
    private $PartyA;
    private $PartyB;
    private $Remarks;
    private $QueueTimeOutURL;
    private $ResultURL;
    private $Occasion;

    private $ApplicationStatus;

    /**
     * B2CHelper constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->setApplicationStatus();

        $this->setInitiatorName($this->getInitiatorName());
        $this->setSecurityCredential($this->getSecurityCredential());
        $this->setPartyA($this->getPartyA());

        $this->setConsumerSecret($this->getConsumerSecret());
        $this->setConsumerKey($this->getConsumerKey());

        $this->setCommandId($this->getCommandId());
        $this->setQueueTimeOutUrl($this->getQueueTimeOutUrl());
        $this->setResultUrl($this->getResultUrl());
        $this->setRemarks($this->getRemarks());
        $this->setOccasion($this->getOccasion());

    }

    public function setApplicationStatus($applicationStatus = null){

        if (!is_null($applicationStatus)){
            $this->ApplicationStatus = $applicationStatus;
            return $this;
        }else{
            $status = env('B2C_INTEGRATION_IS_LIVE') ? env('B2C_INTEGRATION_IS_LIVE') : false;
            $this->ApplicationStatus = $status;
            return $this;
        }

    }

    public function setInitiatorName($initiatorName){
        $this->InitiatorName = $initiatorName;
        return $this;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     * @throws Exception
     */
    public function getInitiatorName(){

        if (env('MMT_MPESA_B2C_INITIATOR_NAME')){
            return env('MMT_MPESA_B2C_INITIATOR_NAME');
        }else{
            throw new Exception("initiator name not set");
        }

        // return config('gateway.module.gateway.mpesa.b2c.InitiatorName');
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

        if (env('MMT_MPESA_B2C_INITIATOR_PASSWORD')){
            return env('MMT_MPESA_B2C_INITIATOR_PASSWORD');
        }else{
            throw new Exception("initiator password not set");
        }

        //return config('gateway.module.gateway.mpesa.b2c.InitiatorPassword');
    }

    public function setSecurityCredential($securityCredential){
        $this->SecurityCredential = $securityCredential;
        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getSecurityCredential(){

        if (env('MMT_MPESA_B2C_INITIATOR_PASSWORD')){

            return self::generateSecurityCredentials(env('MMT_MPESA_B2C_INITIATOR_PASSWORD'),$this->ApplicationStatus);
        }else{
            throw new Exception("initiator password not set for credential generation");
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

        if (env('MMT_MPESA_B2C_CONSUMER_KEY')){
            return env('MMT_MPESA_B2C_CONSUMER_KEY');
        }else{
            throw new Exception("b2c consumer key not set");
        }
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
        if (env('MMT_MPESA_B2C_CONSUMER_SECRETE')){
            return env('MMT_MPESA_B2C_CONSUMER_SECRETE');
        }else{
            throw new Exception("b2c consumer secret not set");
        }
       // return config('gateway.module.gateway.mpesa.b2c.B2cConsumerSecret');
    }

    public function setCommandId($commandID){
        $this->CommandID = $commandID;
        return $this;
    }
    public function getCommandId(){
        return "BusinessPayment";
    }

    public function setAmount($amount){
        $this->Amount = $amount;
        return $this;
    }

    public function setPartyA($partyA){
        $this->PartyA = $partyA;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getPartyA(){
        if (env('MMT_MPESA_B2C_SHORT_CODE')){
            return env('MMT_MPESA_B2C_SHORT_CODE');
        }else{
            throw new Exception("b2c shortcode not set");
        }
        //return config('gateway.module.gateway.mpesa.b2c.B2cShortCode');
    }

    public function setPartyB($partyB){
        $this->PartyB = $partyB;
        return $this;
    }

    public function setRemarks($remarks){
        $this->Remarks = $remarks;
        return $this;
    }
    public function getRemarks(){
        return "Business Payment To Client";
    }

    public function setQueueTimeOutUrl($queueTimeOutURL){
        $this->QueueTimeOutURL = $queueTimeOutURL;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getQueueTimeOutUrl(){
        if (env('MMT_MPESA_B2C_QUEUE_TIMEOUT_URL')){
            return env('MMT_MPESA_B2C_QUEUE_TIMEOUT_URL');
        }else{
            throw new Exception("b2c queue time-out url not set");
        }
        //return config('gateway.module.gateway.mpesa.b2c.QueueTimeOutURL');
    }

    public function setResultUrl($resultURL){
        $this->ResultURL = $resultURL;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getResultUrl(){
        if (env('MMT_MPESA_B2C_RESULT_URL')){
            return env('MMT_MPESA_B2C_RESULT_URL');
        }else{
            throw new Exception("b2c result-url not set");
        }
        //return config('gateway.module.gateway.mpesa.b2c.ResultURL');
    }

    public function setOccasion($occasion){
        $this->Occasion = $occasion;
        return $this;
    }
    public function getOccasion(){
        return "Business Payment To Client";
    }

    public function sendFunds($Amount,$PartyB){

        $response = $this->b2c($Amount,$PartyB);
        $processedRequest = $this->processSendFundsRequest($response);

        if ($processedRequest === true){
            return $response;
        }else{
            return $processedRequest;
        }
    }

    private function processSendFundsRequest($callResponse){
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
                case "500.002.03":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Error Occured: Quota Violation",
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
                case "404.002.01":
                    return json_encode([
                        'errorCode' => $errorCode,
                        'errorRequestId' => $response->requestId,
                        'errorDescription' => "Resource not found",
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
            }
        }else{
            //handling successful service request
            if (isset($response->ConversationID)){
                return true;
            }
        }

        /*
         {
            "requestId": "4475-211337-1",
            "errorCode": "500.002.1001",
            "errorMessage": "Service is currently under maintenance. Please try again later"
        }
         ....
         {
            "ConversationID": "AG_20180421_000076bac40c57557c64",
            "OriginatorConversationID": "4474-211081-1",
            "ResponseCode": "0",
            "ResponseDescription": "Accept the service request successfully."
        }
         ............
         {
              "Result": {
                "ResultType": 0,
                "ResultCode": 2001,
                "ResultDesc": "The initiator information is invalid.",
                "OriginatorConversationID": "4472-208948-1",
                "ConversationID": "AG_20180421_00007131ca1a8e24a6dc",
                "TransactionID": "MDL51H4DMX",
                "ReferenceData": {
                  "ReferenceItem": {
                    "Key": "QueueTimeoutURL",
                    "Value": "https://internalsandbox.safaricom.co.ke/mpesa/b2cresults/v1/submit"
                  }
                }
              }
            }
         */
    }


    /**
     * @param $Amount | The amount being transacted
     * @param $PartyB | Phone number receiving the transaction
     * @return string
     */
    public function b2c($Amount, $PartyB){

        if(!isset($this->ApplicationStatus)){
            die("please declare the application status as defined in the documentation");
        }

        if( $this->ApplicationStatus == true){
            $url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
        }elseif ($this->ApplicationStatus== false){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }

        $token = $this->generateAccessToken($this->ApplicationStatus,$this->ConsumerKey,$this->ConsumerSecret);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token));


        $curl_post_data = array(
            'InitiatorName' => $this->InitiatorName,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID ,
            'Amount' => $Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => "$PartyB",
            'Remarks' => $this->Remarks,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'ResultURL' => $this->ResultURL,
            'Occasion' => $this->Occasion
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

    public function getSetProperties($Amount,$PartyB){
        return [
            'InitiatorName' => $this->InitiatorName,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID ,
            'Amount' => $Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => "$PartyB",
            'Remarks' => $this->Remarks,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'ResultURL' => $this->ResultURL,
            'Occasion' => $this->Occasion
        ];
    }
}