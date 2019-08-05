<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 4/2/18
 * Time: 6:37 PM
 */

namespace Rndwiga\Mpesa\Libraries\C2B;


use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaC2BCalls extends MpesaApiConnection
{
    /** This function is used to register url endpoints that will used in confirming and validating client payments
     * @param $BusinessShortCode
     * @param $confirmationUrl
     * @param $validationUrl
     * @param bool $isLiveEnvironment
     * @return mixed|string |>{ "ConversationID": "", "OriginatorCoversationID": "", "ResponseDescription": "success" }
     */

    public static function c2bUrlRegistration($BusinessShortCode,$confirmationUrl,$validationUrl,$isLiveEnvironment=false){

        if ($isLiveEnvironment == true){
            $url = 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl';
            $token=self::generateLiveToken();
        }elseif ($isLiveEnvironment == false){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';
            $token=self::generateSandBoxToken();
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token)); //setting custom header
        $curl_post_data = array(
            'ShortCode' => $BusinessShortCode,
            'ResponseType' => 'Completed',
            'ConfirmationURL' => $confirmationUrl,
            'ValidationURL' => $validationUrl
        );
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);
        return $curl_response;
    }

    /**
     * Use this function to initiate a C2B transaction
     * @param $ShortCode | 6 digit M-Pesa Till Number or PayBill Number
     * @param $CommandID | Unique command for each transaction type.
     * @param $Amount | The amount been transacted.
     * @param $Msisdn | MSISDN (phone number) sending the transaction, start with country code without the plus(+) sign.
     * @param $BillRefNumber | 	Bill Reference Number (Optional).
     * @return mixed|string
     */
    public  static  function  simulateC2B($ShortCode, $CommandID, $Amount, $Msisdn, $BillRefNumber,$isLiveEnvironment=false ){

        if( $isLiveEnvironment == true){
            $url = 'https://api.safaricom.co.ke/mpesa/c2b/v1/simulate';
            $token=self::generateLiveToken();
        }elseif ($isLiveEnvironment == false){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate';
            $token=self::generateSandBoxToken();
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token));

        $curl_post_data = array(
            'ShortCode' => $ShortCode,
            'CommandID' => $CommandID,
            'Amount' => $Amount,
            'Msisdn' => $Msisdn,
            'BillRefNumber' => $BillRefNumber
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