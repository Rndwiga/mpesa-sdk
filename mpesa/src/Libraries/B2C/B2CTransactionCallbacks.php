<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/3/18
 * Time: 2:25 PM
 */

namespace Rndwiga\Mpesa\Libraries\B2C;


class B2CTransactionCallbacks
{
    public function b2CRequestCallback($requestCallback){
        $callbackJson = $requestCallback;
        $callbackData = json_decode($callbackJson);
        /*TODO:: b2c:: 1. Get ConversationID and OriginatorConversationID from DB
        TODO:: b2c:: 2. confirm that the response matches given request
        TODO:: b2c:: 3. If the response is negative mark the transaction for a re-trial
        TODO:: b2c:: 4. If the response is success mark transaction as complete
         *
         */
        if ($callbackData->Result->ResultType == 0 && $callbackData->Result->ResultCode == 0){
            //return 'cannot give you pretty response';
           return $this->processB2CRequestCallback($requestCallback);
        }else{
            /*
             * Log the transaction
             */

            return $this->processB2CFailedRequest($requestCallback);
        }
    }

    /**
    {
    "Result": {
    "ResultType": 0,
    "ResultCode": 0,
    "ResultDesc": "The service request is processed successfully.",
    "OriginatorConversationID": "25773-1299115-1",
    "ConversationID": "AG_20180903_00005efc187c4b350a69",
    "TransactionID": "MI31FXT9ZP",
    "ResultParameters": {
    "ResultParameter": [
    {
    "Key": "TransactionAmount",
    "Value": 19000
    },
    {
    "Key": "TransactionReceipt",
    "Value": "MI31FXT9ZP"
    },
    {
    "Key": "ReceiverPartyPublicName",
    "Value": "254728309492 - MOGOKO ROBERT AMEMBA"
    },
    {
    "Key": "TransactionCompletedDateTime",
    "Value": "03.09.2018 12:40:07"
    },
    {
    "Key": "B2CUtilityAccountAvailableFunds",
    "Value": 4986599
    },
    {
    "Key": "B2CWorkingAccountAvailableFunds",
    "Value": 0
    },
    {
    "Key": "B2CRecipientIsRegisteredCustomer",
    "Value": "Y"
    },
    {
    "Key": "B2CChargesPaidAccountAvailableFunds",
    "Value": 0
    }
    ]
    },
    "ReferenceData": {
    "ReferenceItem": {
    "Key": "QueueTimeoutURL",
    "Value": "http://internalapi.safaricom.co.ke/mpesa/b2cresults/v1/submit"
    }
    }
    }
    }
     * @param $callbackJSONData
     * @return false|string
     */
    public function processB2CRequestCallback($callbackJSONData){

        $callbackData=json_decode($callbackJSONData);
        return json_encode([
            "status" => 'success',
            "data" => [
                "resultType"=>$callbackData->Result->ResultType,
                "resultCode"=>$callbackData->Result->ResultCode,
                "resultDesc"=>$callbackData->Result->ResultDesc,
                "originatorConversationID"=>$callbackData->Result->OriginatorConversationID,
                "conversationID"=>$callbackData->Result->ConversationID,
                "transactionID"=>$callbackData->Result->TransactionID,
                "transactionAmount"=>$callbackData->Result->ResultParameters->ResultParameter[0]->Value,
                "transactionReceipt"=>$callbackData->Result->ResultParameters->ResultParameter[1]->Value,
                "receiverPartyPublicName"=>$callbackData->Result->ResultParameters->ResultParameter[2]->Value,
                "transactionCompletedDateTime"=>$callbackData->Result->ResultParameters->ResultParameter[3]->Value,
                "b2CUtilityAccountAvailableFunds"=>$callbackData->Result->ResultParameters->ResultParameter[4]->Value,
                "b2CWorkingAccountAvailableFunds"=>$callbackData->Result->ResultParameters->ResultParameter[5]->Value,
                "b2CRecipientIsRegisteredCustomer"=>$callbackData->Result->ResultParameters->ResultParameter[6]->Value,
                "b2CChargesPaidAccountAvailableFunds"=>$callbackData->Result->ResultParameters->ResultParameter[7]->Value,
            ]
        ]);
    }


    /**
     * Use this function to process the B2C request callback
     * {
    "Result":{
    "ResultType":0,
    "ResultCode":0,
    "ResultDesc":"The service request has been accepted successfully.",
    "OriginatorConversationID":"19455-424535-1",
    "ConversationID":"AG_20170717_00006be9c8b5cc46abb6",
    "TransactionID":"LGH3197RIB",
    "ResultParameters":{
    "ResultParameter":[
    {
    "Key":"TransactionReceipt",
    "Value":"LGH3197RIB"
    },
    {
    "Key":"TransactionAmount",
    "Value":8000
    },
    {
    "Key":"B2CWorkingAccountAvailableFunds",
    "Value":150000
    },
    {
    "Key":"B2CUtilityAccountAvailableFunds",
    "Value":133568
    },
    {
    "Key":"TransactionCompletedDateTime",
    "Value":"17.07.2017 10:54:57"
    },
    {
    "Key":"ReceiverPartyPublicName",
    "Value":"254708374149 - John Doe"
    },
    {
    "Key":"B2CChargesPaidAccountAvailableFunds",
    "Value":0
    },
    {
    "Key":"B2CRecipientIsRegisteredCustomer",
    "Value":"Y"
    }
    ]
    },
    "ReferenceData":{
    "ReferenceItem":{
    "Key":"QueueTimeoutURL",
    "Value":"https://internalsandbox.safaricom.co.ke/mpesa/b2cresults/v1/submit"
    }
    }
    }
    }
     * @return string
     */

    public function processB2CRequestCallbackDemo($callbackJSONData){

        $callbackData=json_decode($callbackJSONData);
        return json_encode([
            "status" => 'success',
            "data" => [
                "resultType"=>$callbackData->Result->ResultType,
                "resultCode"=>$callbackData->Result->ResultCode,
                "resultDesc"=>$callbackData->Result->ResultDesc,
                "originatorConversationID"=>$callbackData->Result->OriginatorConversationID,
                "conversationID"=>$callbackData->Result->ConversationID,
                "transactionID"=>$callbackData->Result->TransactionID,
                "transactionAmount"=>$callbackData->Result->ResultParameters->ResultParameter[0]->Value,
                "transactionReceipt"=>$callbackData->Result->ResultParameters->ResultParameter[1]->Value,
                "b2CRecipientIsRegisteredCustomer"=>$callbackData->Result->ResultParameters->ResultParameter[2]->Value,
                "b2CChargesPaidAccountAvailableFunds"=>$callbackData->Result->ResultParameters->ResultParameter[3]->Value,
                "receiverPartyPublicName"=>$callbackData->Result->ResultParameters->ResultParameter[4]->Value,
                "transactionCompletedDateTime"=>$callbackData->Result->ResultParameters->ResultParameter[5]->Value,
                "b2CUtilityAccountAvailableFunds"=>$callbackData->Result->ResultParameters->ResultParameter[6]->Value,
                "b2CWorkingAccountAvailableFunds"=>$callbackData->Result->ResultParameters->ResultParameter[7]->Value,
            ]
        ]);
    }





    /**
     * {
     * "Result": {
     * "ResultType": 0,
     * "ResultCode": 2001,
     * "ResultDesc": "The initiator information is invalid.",
     * "OriginatorConversationID": "26636-768521-1",
     * "ConversationID": "AG_20180503_000068d3116b5b5762ea",
     * "TransactionID": "ME381H4F9W",
     * "ReferenceData": {
     * "ReferenceItem": {
     * "Key": "QueueTimeoutURL",
     * "Value": "https://internalsandbox.safaricom.co.ke/mpesa/b2cresults/v1/submit"
     * }
     * }
     * }
     * }
     * @param $callbackJSONData
     * @return string
     */
    /**
    {"Result":{"ResultType":0,"ResultCode":2028,"ResultDesc":"The request is not permitted according to product assignment.","OriginatorConversationID":"11275-5130496-1","ConversationID":"AG_20180815_0000629f171b236a65ee","TransactionID":"MHF03D1KZ8","ReferenceData":{"ReferenceItem":{"Key":"QueueTimeoutURL","Value":"http:\/\/internalapi.safaricom.co.ke\/mpesa\/b2cresults\/v1\/submit"}}}}

     *
     * @param $callbackJSONData
     * @return string
     */

    public function processB2CFailedRequest($callbackJSONData){
        $callbackData=json_decode($callbackJSONData);

        return json_encode([
            "status" => "fail",
            "data" => [
                "resultType"=>$callbackData->Result->ResultType,
                "resultCode"=>$callbackData->Result->ResultCode,
                "resultDesc"=>$callbackData->Result->ResultDesc,
                "originatorConversationID"=>$callbackData->Result->OriginatorConversationID,
                "conversationID"=>$callbackData->Result->ConversationID,
                "transactionID"=>$callbackData->Result->TransactionID,
            ]
        ]);
    }
}