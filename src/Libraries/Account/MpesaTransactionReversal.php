<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:07 PM
 */

namespace Rndwiga\Mpesa\Libraries\Account;


class MpesaTransactionReversal
{

    /**
     * Use this function to initiate a reversal request
     * @param $CommandID | Takes only 'TransactionReversal' Command id
     * @param $Initiator | The name of Initiator to initiating  the request
     * @param $SecurityCredential | 	Encrypted Credential of user getting transaction amount
     * @param $TransactionID | Unique Id received with every transaction response.
     * @param $Amount | Amount
     * @param $ReceiverParty | Organization /MSISDN sending the transaction
     * @param $RecieverIdentifierType | Type of organization receiving the transaction
     * @param $ResultURL | The path that stores information of transaction
     * @param $QueueTimeOutURL | The path that stores information of time out transaction
     * @param $Remarks | Comments that are sent along with the transaction.
     * @param $Occasion | 	Optional Parameter
     * @return mixed|string
     */
    public static function reversal($CommandID, $Initiator, $SecurityCredential, $TransactionID, $Amount, $ReceiverParty, $RecieverIdentifierType, $ResultURL, $QueueTimeOutURL, $Remarks, $Occasion){
        $live=config('gateway.module.payment_gateway.mpesa.application_status');

        if( $live =="true"){
            $url = 'https://api.safaricom.co.ke/mpesa/reversal/v1/request';
            $token=self::generateLiveToken();
        }elseif ($live=="false"){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request';
            $token=self::generateSandBoxToken();
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }



        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token));


        $curl_post_data = array(
            'CommandID' => $CommandID,
            'Initiator' => $Initiator,
            'SecurityCredential' => $SecurityCredential,
            'TransactionID' => $TransactionID,
            'Amount' => $Amount,
            'ReceiverParty' => $ReceiverParty,
            'RecieverIdentifierType' => $RecieverIdentifierType,
            'ResultURL' => $ResultURL,
            'QueueTimeOutURL' => $QueueTimeOutURL,
            'Remarks' => $Remarks,
            'Occasion' => $Occasion
        );

        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $curl_response = curl_exec($curl);
        return json_decode($curl_response);

    }
}