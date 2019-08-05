<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 4/2/18
 * Time: 6:44 PM
 */

namespace Rndwiga\Mpesa\Libraries\B2B;


use Exception;
use Rndwiga\Mpesa\Libraries\BaseRequest;
use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaB2BCalls extends BaseRequest
{

    public function sendFundsToBusiness(){

        $response = $this->setPartyB("600000")
            ->setCommandID("BusinessToBusinessTransfer")
            ->setSenderIdentifierType("4")
            ->setReiver("4")
            ->setAccountReference("BusinessPaybill")
            ->setRemarks("Business Payment To Business")
            ->b2b(100);
        return $response;
    }

    public function makeB2bCall( $Amount){

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
            'Initiator' => $this->InitiatorName,
            'SecurityCredential' => $this->SecurityCredential,
            'CommandID' => $this->CommandID,
            'SenderIdentifierType' => $this->SenderIdentifierType,
            'RecieverIdentifierType' => $this->ReceiverIdentifierType,
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
}