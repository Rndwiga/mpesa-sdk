<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 4/2/18
 * Time: 6:47 PM
 */

namespace Rndwiga\Mpesa\Libraries\B2C;

use Exception;

use Rndwiga\Mmt\Helpers\MmtUtility;
use Rndwiga\Mpesa\Libraries\BaseRequest;
use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaB2CCalls extends BaseRequest
{


    private function callSampleRequest(){
        $response = (new \Rndwiga\Mpesa\Libraries\B2C\MpesaB2CCalls())
            ->setApplicationStatus(false)
            ->setInitiatorName("apitest314")
            ->setSecurityCredential("314reset")
            ->setConsumerKey("mhRpe708DblJNb9P3qM6M93fWmnhXhLd")
            ->setConsumerSecret("KeoYJkSLxqKM1vMP")
            ->setCommandId("BusinessPayment")
            ->setAmount(10)
            ->setPartyA(601314)
            ->setPartyB(254708374149)
            ->setRemarks("loan disbursement")
            ->setOccasion("funds management")
            ->setQueueTimeOutUrl("https://webhook.site/352510be-7b2e-45cd-b360-51bf1257c8bd")
            ->setResultUrl("https://webhook.site/352510be-7b2e-45cd-b360-51bf1257c8bd")
            ->makeB2cCall();
        return $response;
    }


    public function sendFunds($Amount,$PartyB){
        $response = $this->b2c($Amount,$PartyB);
        $processedRequest = $this->processRequestResponse($response);

        if ($processedRequest === true){
            MmtUtility::logInfo([$response],'b2c_success_request','mpesaSDKApp_B2C');
            return $response;
        }else{
            $mpesaRequestdata = json_decode($response);

            MmtUtility::logInfo([$mpesaRequestdata],'b2c_failed_request_1','mpesaSDKApp_B2C');

            $mpesaRequestdata->originalRequest = $processedRequest;

            MmtUtility::logInfo([$mpesaRequestdata],'b2c_failed_request_2','mpesaSDKApp_B2C');

            return json_encode($mpesaRequestdata);
        }
    }

    /**
     * @return string
     */

    public function makeB2cCall(){
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
            'Amount' => $this->Amount,
            'PartyA' => $this->PartyA,
            'PartyB' => "$this->PartyB",
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