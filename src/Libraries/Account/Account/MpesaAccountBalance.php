<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:07 PM
 */

namespace Rndwiga\Mpesa\Libraries\Account\Account;

use Exception;
use Rndwiga\Mpesa\Libraries\BaseRequest;
use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaAccountBalance extends BaseRequest
{
    public function makeAccountBalanceCall(){

        if(!isset($this->ApplicationStatus)){
            die("please declare the application status as defined in the documentation");
        }
        if( $this->ApplicationStatus == true){
            $url = 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query';
        }elseif ($this->ApplicationStatus== false){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query';
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }

        $token = $this->generateAccessToken($this->ApplicationStatus,$this->ConsumerKey,$this->ConsumerSecret);


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token)); //setting custom header

        $curl_post_data = array(
            'CommandID' => $this->CommandID,
            'Initiator' => $this->InitiatorName,
            'SecurityCredential' => $this->SecurityCredential,
            'PartyA' => $this->PartyA,
            'IdentifierType' => $this->IdentifierType, //4
            'Remarks' => $this->Remarks,
            'QueueTimeOutURL' => $this->QueueTimeOutURL,
            'ResultURL' => $this->ResultURL
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);

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