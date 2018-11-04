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
    public function processTransactionRequestResponse(array $transactionRequest, bool $isRequest = true){

        if ($isRequest === true){
            if (isset($transactionRequest['ResponseCode'])){
                if ($transactionRequest['ResponseCode'] == 0){
                    $transactionRequest['transactionRequestStatus'] = 'success';
                    return $transactionRequest;
                }else{
                    $transactionRequest['transactionRequestStatus'] = 'fail';
                    return $transactionRequest;
                }
            }elseif(isset($transactionRequest['fault'])){
                $transactionRequest['transactionRequestStatus'] = 'fault';
                return $transactionRequest;
            }

        }else{
            if ($transactionRequest['resultCode'] == 0){
                $transactionRequest['transactionResultStatus'] = 'success';
                return $transactionRequest;
            }else{
                $transactionRequest['transactionResultStatus'] = 'fail';
                return $transactionRequest;
            }
        }
    }

    /**
     * Use this function to process the Transaction status request callback
     * @param array $resultArray
     * @param bool $isLive
     * @return array
     */
    public function processTransactionStatusRequestCallback(array $resultArray, bool $isLive = true){
       if ($isLive === true){
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
               "resultType" =>$resultType,
               "resultCode"=>$resultCode,
               "resultDesc"=>$resultDesc,
               "StatusOriginatorConversationID"=>$originatorConversationID,
               "StatusConversationID"=>$conversationID,
               "TransactionID"=>$transactionID,
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
       }else{
           $callbackData=json_encode($resultArray);
           $callbackData=json_decode($callbackData);
           $resultType=$callbackData->Result->ResultType;
           $resultCode=$callbackData->Result->ResultCode;
           $resultDesc=$callbackData->Result->ResultDesc;

           $originatorConversationID=$callbackData->Result->OriginatorConversationID;
           $conversationID=$callbackData->Result->ConversationID;

           $transactionID=$callbackData->Result->TransactionID;
           $DebitPartyName=$callbackData->Result->ResultParameters->ResultParameter[0]->Value;
           $CreditPartyName=$callbackData->Result->ResultParameters->ResultParameter[1]->Value;
           $OriginatorConversationID=$callbackData->Result->ResultParameters->ResultParameter[2]->Value;
           $InitiatedTime=$callbackData->Result->ResultParameters->ResultParameter[3]->Value;
           $DebitAccountType=$callbackData->Result->ResultParameters->ResultParameter[4]->Value;
           $DebitPartyCharges=$callbackData->Result->ResultParameters->ResultParameter[5]->Value;
           $TransactionReason=isset($callbackData->Result->ResultParameters->ResultParameter[6]->Value)?$callbackData->Result->ResultParameters->ResultParameter[6]->Value:'';
           $ReasonType=$callbackData->Result->ResultParameters->ResultParameter[7]->Value;
           $TransactionStatus=$callbackData->Result->ResultParameters->ResultParameter[8]->Value;
           $FinalisedTime=$callbackData->Result->ResultParameters->ResultParameter[9]->Value;
           $Amount=$callbackData->Result->ResultParameters->ResultParameter[10]->Value;
           $ConversationID=$callbackData->Result->ResultParameters->ResultParameter[11]->Value;
           $ReceiptNo=$callbackData->Result->ResultParameters->ResultParameter[12]->Value;
           $result=[
               "resultType" =>$resultType,
               "resultCode"=>$resultCode,
               "resultDesc"=>$resultDesc,
               "StatusOriginatorConversationID"=>$originatorConversationID,
               "StatusConversationID"=>$conversationID,
               "TransactionID"=>$transactionID,
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
}