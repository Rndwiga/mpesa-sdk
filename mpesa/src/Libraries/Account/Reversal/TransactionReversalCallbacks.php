<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:15 PM
 */

namespace Rndwiga\Mpesa\Libraries\Account\Reversal;

class TransactionReversalCallbacks
{
    /**
     * Use this function to process the Reversal request callback
     * @return array|string
     */
    public function processReversalRequestCallBack(array $reversalDetailsArray){
       // $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_encode($reversalDetailsArray);
        $callbackData=json_decode($callbackData);
        $resultType=$callbackData->Result->ResultType;
        $resultCode=$callbackData->Result->ResultCode;
        $resultDesc=$callbackData->Result->ResultDesc;
        $originatorConversationID=$callbackData->Result->OriginatorConversationID;
        $conversationID=$callbackData->Result->ConversationID;
        $transactionID=$callbackData->Result->TransactionID;

        $result=[
            "resultType"=>$resultType,
            "resultCode"=>$resultCode,
            "resultDesc"=>$resultDesc,
            "conversationID"=>$conversationID,
            "transactionID"=>$transactionID,
            "originatorConversationID"=>$originatorConversationID
        ];

        return $result;

    }
}