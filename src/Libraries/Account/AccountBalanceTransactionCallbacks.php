<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:14 PM
 */

namespace Rndwiga\Mpesa\Libraries\Account;


class AccountBalanceTransactionCallbacks
{

    /**
     * Use this function to process the Account Balance request callback
     * @return string
     */
    public static function processAccountBalanceRequestCallback(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);
        $resultType=$callbackData->Result->ResultType;
        $resultCode=$callbackData->Result->ResultCode;
        $resultDesc=$callbackData->Result->ResultDesc;
        $originatorConversationID=$callbackData->Result->OriginatorConversationID;
        $conversationID=$callbackData->Result->ConversationID;
        $transactionID=$callbackData->Result->TransactionID;
        $accountBalance=$callbackData->Result->ResultParameters->ResultParameter[0]->Value;
        $BOCompletedTime=$callbackData->Result->ResultParameters->ResultParameter[1]->Value;

        $result=[
            "resultDesc"=>$resultDesc,
            "resultCode"=>$resultCode,
            "originatorConversationID"=>$originatorConversationID,
            "conversationID"=>$conversationID,
            "transactionID"=>$transactionID,
            "accountBalance"=>$accountBalance,
            "BOCompletedTime"=>$BOCompletedTime,
            "resultType"=>$resultType
        ];

        return json_encode($result);


    }
}