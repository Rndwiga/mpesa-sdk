<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:11 PM
 */

namespace Rndwiga\Mpesa\Libraries\B2B;


class B2BTransactionCallbacks
{

    /**
     * Use this function to process the B2B request callback
     * @return string
     */
    public static function processB2BRequestCallback(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);
        $resultCode=$callbackData->Result->ResultCode;
        $resultDesc=$callbackData->Result->ResultDesc;
        $originatorConversationID=$callbackData->Result->OriginatorConversationID;
        $conversationID=$callbackData->Result->ConversationID;
        $transactionID=$callbackData->Result->TransactionID;
        $transactionReceipt=$callbackData->Result->ResultParameters->ResultParameter[0]->Value;
        $transactionAmount=$callbackData->Result->ResultParameters->ResultParameter[1]->Value;
        $b2CWorkingAccountAvailableFunds=$callbackData->Result->ResultParameters->ResultParameter[2]->Value;
        $b2CUtilityAccountAvailableFunds=$callbackData->Result->ResultParameters->ResultParameter[3]->Value;
        $transactionCompletedDateTime=$callbackData->Result->ResultParameters->ResultParameter[4]->Value;
        $receiverPartyPublicName=$callbackData->Result->ResultParameters->ResultParameter[5]->Value;
        $B2CChargesPaidAccountAvailableFunds=$callbackData->Result->ResultParameters->ResultParameter[6]->Value;
        $B2CRecipientIsRegisteredCustomer=$callbackData->Result->ResultParameters->ResultParameter[7]->Value;


        $result=[
            "resultCode"=>$resultCode,
            "resultDesc"=>$resultDesc,
            "originatorConversationID"=>$originatorConversationID,
            "conversationID"=>$conversationID,
            "transactionID"=>$transactionID,
            "transactionReceipt"=>$transactionReceipt,
            "transactionAmount"=>$transactionAmount,
            "b2CWorkingAccountAvailableFunds"=>$b2CWorkingAccountAvailableFunds,
            "b2CUtilityAccountAvailableFunds"=>$b2CUtilityAccountAvailableFunds,
            "transactionCompletedDateTime"=>$transactionCompletedDateTime,
            "receiverPartyPublicName"=>$receiverPartyPublicName,
            "B2CChargesPaidAccountAvailableFunds"=>$B2CChargesPaidAccountAvailableFunds,
            "B2CRecipientIsRegisteredCustomer"=>$B2CRecipientIsRegisteredCustomer
        ];

        return json_encode($result);
    }

}