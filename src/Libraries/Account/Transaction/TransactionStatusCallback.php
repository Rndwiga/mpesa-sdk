<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:17 PM
 */

namespace Rndwiga\Mpesa\Libraries\Account;

use Rndwiga\Mpesa\Libraries\MpesaBaseClass;

class TransactionStatusCallback extends MpesaBaseClass
{
    /**
     * Use this function to process the Transaction status request callback
     * @return array
     */
    public function processTransactionStatusRequestCallback(array $resultArray){
       // $callbackJSONData=file_get_contents('php://input');

       // return $resultArray;
        $callbackData=json_encode($resultArray);
        $callbackData=json_decode($callbackData);

        $resultType=$callbackData->Result->ResultType;
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
        $TransactionReason=isset($callbackData->Result->ResultParameters->ResultParameter[6]->Value)?$callbackData->Result->ResultParameters->ResultParameter[6]->Value:''; //
        $DebitPartyCharges=$callbackData->Result->ResultParameters->ResultParameter[7]->Value;
        $DebitAccountType=$callbackData->Result->ResultParameters->ResultParameter[8]->Value;
        $InitiatedTime=$callbackData->Result->ResultParameters->ResultParameter[9]->Value;
        $OriginatorConversationID=$callbackData->Result->ResultParameters->ResultParameter[10]->Value;
        $CreditPartyName=$callbackData->Result->ResultParameters->ResultParameter[11]->Value;
        $DebitPartyName=$callbackData->Result->ResultParameters->ResultParameter[12]->Value;

        $result=[
            "ResultType" =>$resultType,
            "resultCode"=>$resultCode,
            "resultDesc"=>$resultDesc,
            "originatorConversationID"=>$originatorConversationID,
            "conversationID"=>$conversationID,
            "transactionID"=>$transactionID,
            "ReceiptNo"=>$ReceiptNo,
            "ConversationID"=>$ConversationID,
            "FinalisedTime"=>$this->processCompletedTime($FinalisedTime),
            "Amount"=>$Amount,
            "TransactionStatus"=>$TransactionStatus,
            "ReasonType"=>$ReasonType,
            "TransactionReason"=>$TransactionReason,
            "DebitPartyCharges"=>$DebitPartyCharges,
            "DebitAccountType"=>$DebitAccountType,
            "InitiatedTime"=>$this->processCompletedTime($InitiatedTime),
            "OriginatorConversationID"=>$OriginatorConversationID,
            "CreditPartyName"=>$CreditPartyName,
            "DebitPartyName"=>$DebitPartyName
        ];

        return $result;
    }
}