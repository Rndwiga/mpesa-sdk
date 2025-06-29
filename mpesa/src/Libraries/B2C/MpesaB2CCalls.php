<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 4/2/18
 * Time: 6:47 PM
 */

namespace Rndwiga\Mpesa\Libraries\B2C;

use Exception;

use Rndwiga\Mpesa\Libraries\BaseRequest;
use Rndwiga\Toolbox\Infrastructure\Services\AppLogger;

class MpesaB2CCalls extends BaseRequest
{

    public function sampleRequest(){
        $response = (new MpesaB2CCalls())
            ->setApplicationStatus(false)
            ->setInitiatorName(env('INITIATOR_NAME'))
            ->setSecurityCredential(env('SECURITY_CREDENTIAL'))
            ->setConsumerKey(env('CONSUMER_KEY'))
            ->setConsumerSecret(env('CONSUMER_SECRET'))
            ->setCommandId(env('COMMAND_ID'))
            ->setAmount(10)
            ->setPartyA(env('PARTY_A'))
            ->setPartyB(254708374149)
            ->setRemarks("loan disbursement")
            ->setOccasion("funds management")
            ->setQueueTimeOutUrl(env('QUEUE_TIMEOUT_URL'))
            ->setResultUrl(env('RESULT_URL'))
            ->makeB2cCall();
        return $response;
    }

    public function processCallRequest($response){
        $processedRequest = $this->processRequestResponse($response);

        if ($processedRequest === true){
            (new AppLogger('mpesaSDKApp_B2C','b2c_success_request'))->logInfo([$response]);
            return $response;
        }else{
            $mpesaRequestdata = json_decode($response,true);
            $mpesaRequestdata['originalRequest'] = json_decode($processedRequest,true);

            (new AppLogger('mpesaSDKApp_B2C','b2c_failed_request'))->logInfo([
                'mpesaRequestdata' => $mpesaRequestdata,
            ]);

            return json_encode($mpesaRequestdata);
        }
    }

    /**
     * Make a B2C API call
     * 
     * @param int|null $amount The amount to transfer (optional if already set)
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return string The API response
     */
    public function makeB2cCall($amount = null, $verifySSL = true)
    {
        // Set the amount if it's provided as a parameter
        if ($amount !== null) {
            $this->setAmount($amount);
        }

        // Validate required fields
        if (!isset($this->InitiatorName) || !isset($this->SecurityCredential) || 
            !isset($this->CommandID) || !isset($this->Amount) || 
            !isset($this->PartyA) || !isset($this->PartyB) || 
            !isset($this->QueueTimeOutURL) || !isset($this->ResultURL)) {
            throw new \InvalidArgumentException("Missing required parameters for B2C call");
        }

        // Prepare request data
        $requestData = [
            'InitiatorName' => $this->InitiatorName,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID,
            'Amount' => $this->Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => (string)$this->PartyB, // Cast to string as required by the API
            'Remarks' => $this->Remarks,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'ResultURL' => $this->ResultURL,
            'Occasion' => $this->Occasion
        ];

        try {
            // Make the API request
            $response = $this->makeApiRequest(self::B2C_ENDPOINT, $requestData, $verifySSL);
            return $response;
        } catch (\Exception $e) {
            $errorMessage = "cURL Error: " . $e->getMessage();
            (new AppLogger('mpesaSDKApp_B2C', 'b2c_failed_request'))->logInfo([$errorMessage]);
            return json_encode(['error' => $errorMessage]);
        }
    }

    /**
     * Make a B2C API call with an originator conversation ID
     * 
     * @param string $originatorConversationID The originator conversation ID
     * @param int|null $amount The amount to transfer (optional if already set)
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return string The API response
     */
    public function makeB2cCallV2(string $originatorConversationID, $amount = null, $verifySSL = true)
    {
        // Set the amount if it's provided as a parameter
        if ($amount !== null) {
            $this->setAmount($amount);
        }

        // Validate required fields
        if (!isset($this->InitiatorName) || !isset($this->SecurityCredential) || 
            !isset($this->CommandID) || !isset($this->Amount) || 
            !isset($this->PartyA) || !isset($this->PartyB) || 
            !isset($this->QueueTimeOutURL) || !isset($this->ResultURL)) {
            throw new \InvalidArgumentException("Missing required parameters for B2C call");
        }

        // Prepare request data
        $requestData = [
            'OriginatorConversationID' => $originatorConversationID,
            'InitiatorName' => $this->InitiatorName,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID,
            'Amount' => $this->Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => (string)$this->PartyB, // Cast to string as required by the API
            'Remarks' => $this->Remarks,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'ResultURL' => $this->ResultURL,
            'Occasion' => $this->Occasion
        ];

        try {
            // Make the API request
            $endpoint = $this->ApplicationStatus === true ? self::B2C_ENDPOINT : self::B2C_VALIDATE_ENDPOINT;
            $response = $this->makeApiRequest($endpoint, $requestData, $verifySSL);
            return $response;
        } catch (\Exception $e) {
            $errorMessage = "cURL Error: " . $e->getMessage();
            (new AppLogger('mpesaSDKApp_B2C', 'b2c_failed_request'))->logInfo([$errorMessage]);
            return json_encode(['error' => $errorMessage]);
        }
    }

    public function getSetProperties(){
        return array(
            'InitiatorName' => $this->InitiatorName,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID ,
            'Amount' => $this->Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => "$this->PartyB",
            'Remarks' => $this->Remarks,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'ResultURL' => $this->ResultURL,
            'Occasion' => $this->Occasion
        );
    }
}
