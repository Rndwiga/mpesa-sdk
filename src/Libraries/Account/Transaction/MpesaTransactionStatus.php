<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:07 PM
 */
/**
 * Use this function to make a transaction status request
 * @param $Initiator | The name of Initiator to initiating the request.
 * @param $SecurityCredential | 	Base64 encoded string of the M-Pesa short code and password, which is encrypted using M-Pesa public key and validates the transaction on M-Pesa Core system.
 * @param $CommandID | Unique command for each transaction type, possible values are: TransactionStatusQuery.
 * @param $TransactionID | Organization Receiving the funds.
 * @param $PartyA | Organization/MSISDN sending the transaction
 * @param $IdentifierType | Type of organization receiving the transaction
 * @param $ResultURL | The path that stores information of transaction
 * @param $QueueTimeOutURL | The path that stores information of time out transaction
 * @param $Remarks | 	Comments that are sent along with the transaction
 * @param $Occasion | 	Optional Parameter
 * @return mixed|string
 */

namespace Rndwiga\Mpesa\Libraries\Account;


use Rndwiga\Mpesa\Libraries\BaseRequest;

class MpesaTransactionStatus extends BaseRequest
{
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