<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:17 PM
 */

namespace Rndwiga\Mpesa\Libraries\Account;

class TransactionStatusCallback
{
    /**
     * Use this function to process the Transaction status request callback
     * @return string
     */
    public static function processTransactionStatusRequestCallback(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);
        $resultCode=$callbackData->Result->ResultCode;
        $resultDesc=$callbackData->Result->ResultDesc;
        $originatorConversationID=$callbackData->Result->OriginatorConversationID;
        $conversationID=$callbackData->Result->ConversationID;
        $transactionID=$callbackData->Result->TransactionID;
        $ReceiptNo=$callbackData->Result->ResultParameters->ResultParameter[0]->Value;
        $ConversationID=$callbackData->Result->ResultParameters->ResultParameter[1]->Value;
        $FinalisedTime=$callbackData->Result->ResultParameters->ResultParameter[2]->Value;
        $Amount=$callbackData->Result->ResultParameters->ResultParameter[3]->Value;
        $TransactionStatus=$callbackData->Result->ResultParameters->ResultParameter[4]->Value;
        $ReasonType=$callbackData->Result->ResultParameters->ResultParameter[5]->Value;
        $TransactionReason=$callbackData->Result->ResultParameters->ResultParameter[6]->Value;
        $DebitPartyCharges=$callbackData->Result->ResultParameters->ResultParameter[7]->Value;
        $DebitAccountType=$callbackData->Result->ResultParameters->ResultParameter[8]->Value;
        $InitiatedTime=$callbackData->Result->ResultParameters->ResultParameter[9]->Value;
        $OriginatorConversationID=$callbackData->Result->ResultParameters->ResultParameter[10]->Value;
        $CreditPartyName=$callbackData->Result->ResultParameters->ResultParameter[11]->Value;
        $DebitPartyName=$callbackData->Result->ResultParameters->ResultParameter[12]->Value;

        $result=[
            "resultCode"=>$resultCode,
            "resultDesc"=>$resultDesc,
            "originatorConversationID"=>$originatorConversationID,
            "conversationID"=>$conversationID,
            "transactionID"=>$transactionID,
            "ReceiptNo"=>$ReceiptNo,
            "ConversationID"=>$ConversationID,
            "FinalisedTime"=>$FinalisedTime,
            "Amount"=>$Amount,
            "TransactionStatus"=>$TransactionStatus,
            "ReasonType"=>$ReasonType,
            "TransactionReason"=>$TransactionReason,
            "DebitPartyCharges"=>$DebitPartyCharges,
            "DebitAccountType"=>$DebitAccountType,
            "InitiatedTime"=>$InitiatedTime,
            "OriginatorConversationID"=>$OriginatorConversationID,
            "CreditPartyName"=>$CreditPartyName,
            "DebitPartyName"=>$DebitPartyName
        ];

        return json_encode($result);
    }
}