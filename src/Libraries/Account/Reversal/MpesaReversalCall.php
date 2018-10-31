<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 4/2/18
 * Time: 6:47 PM
 */

namespace Rndwiga\Mpesa\Libraries\Account\Reversal;

use Exception;

use Rndwiga\Mmt\Helpers\MmtUtility;
use Rndwiga\Mpesa\Libraries\BaseRequest;
use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaReversalCall extends BaseRequest
{
    private function sameRequestCall(){
        $response = (new \Rndwiga\Mpesa\Libraries\Account\Reversal\MpesaReversalCall())
            ->setApplicationStatus(false)
            ->setInitiatorName("apitest314")
            ->setSecurityCredential("314reset")
            ->setConsumerKey("mhRpe708DblJNb9P3qM6M93fWmnhXhLd")
            ->setConsumerSecret("KeoYJkSLxqKM1vMP")
            ->setCommandId("TransactionReversal")
            ->setReceiverParty(601314)
            ->setReceiverIdentifierType(11)
            ->setTransactionID("MJV61H78BM")
            ->setAmount(10)
            ->setRemarks("Reversing Business Payment To Client")
            ->setOccasion("Erroneous transaction reversal")
            ->setQueueTimeOutUrl("https://webhook.site/352510be-7b2e-45cd-b360-51bf1257c8bd")
            ->setResultUrl("https://webhook.site/352510be-7b2e-45cd-b360-51bf1257c8bd")
            ->makeReversalRequestCall("MJV61H78BM");
        return $response;
    }

    public function makeReversalRequestCall(string $transactionId){

        if(!isset($this->ApplicationStatus)){
            die("please declare the application status as defined in the documentation");
        }

        if( $this->ApplicationStatus == true){
            $url = 'https://api.safaricom.co.ke/mpesa/reversal/v1/request';
        }elseif ($this->ApplicationStatus== false){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request';
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }
        $token = $this->generateAccessToken($this->ApplicationStatus,$this->ConsumerKey,$this->ConsumerSecret);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token));


        $curl_post_data = array(
            'Initiator' => $this->InitiatorName,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID,
            'TransactionID' => $this->TransactionID,
            'Amount' => "$this->Amount",
            'ReceiverParty' => "$this->ReceiverParty",
            'RecieverIdentifierType' => "$this->ReceiverIdentifierType",
            'ResultURL' => $this->ResultURL,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'Remarks' => $this->Remarks,
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
}