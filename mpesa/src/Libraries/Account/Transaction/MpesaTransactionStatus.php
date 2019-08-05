<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:07 PM
 */

namespace Rndwiga\Mpesa\Libraries\Account;


use Rndwiga\Mpesa\Libraries\BaseRequest;

class MpesaTransactionStatus extends BaseRequest
{
    private function sampleRequest(){
        $response = (new \Rndwiga\Mpesa\Libraries\Account\MpesaTransactionStatus())
            ->setApplicationStatus(false)
            ->setInitiatorName("apitest314")
            ->setSecurityCredential("314reset")
            ->setConsumerKey("mhRpe708DblJNb9P3qM6M93fWmnhXhLd")
            ->setConsumerSecret("KeoYJkSLxqKM1vMP")
            ->setCommandId("TransactionStatusQuery")
            ->setPartyA(601314)
            ->setIdentifierType(4)
            ->setRemarks("Understanding Account Balance")
            ->setTransactionID("MJV51H78BL")
            ->setQueueTimeOutUrl("https://webhook.site/352510be-7b2e-45cd-b360-51bf1257c8bd")
            ->setResultUrl("https://webhook.site/352510be-7b2e-45cd-b360-51bf1257c8bd")
            ->makeTransactionStatusCall();

        return $response;
    }

    public function makeTransactionStatusCall(){

        if( $this->ApplicationStatus == true){
            $url = 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query';
        }elseif ($this->ApplicationStatus== false){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query';
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }
        $token = $this->generateAccessToken($this->ApplicationStatus,$this->ConsumerKey,$this->ConsumerSecret);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token)); //setting custom header


        $curl_post_data = array(
            'Initiator' => $this->InitiatorName,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID,
            'TransactionID' => $this->TransactionID,
            'PartyA' => $this->PartyA,
            'IdentifierType' => $this->IdentifierType,
            'ResultURL' => $this->ResultURL,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'Remarks' => $this->Remarks,
            'Occasion' => $this->Occasion
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $curl_response = curl_exec($curl);

        return $curl_response;
    }
}