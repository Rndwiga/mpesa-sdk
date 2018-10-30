<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:14 PM
 */

namespace Rndwiga\Mpesa\Libraries\Account\Account;


use Rndwiga\Mpesa\Libraries\MpesaBaseClass;

class AccountBalanceTransactionCallbacks extends MpesaBaseClass
{

    /**
     * Use this function to process the Account Balance request callback
     * @param array $transactionResult
     * @return array
     */
    public function processAccountBalanceRequestCallback(array $transactionResult){
        //$callbackJSONData=file_get_contents('php://input');
        $callbackJSONData=json_encode($transactionResult);
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
            "resultType"=>$resultType,
            "resultCode"=>$resultCode,
            "resultDesc"=>$resultDesc,
            "originatorConversationID"=>$originatorConversationID,
            "conversationID"=>$conversationID,
            "transactionID"=>$transactionID,
            "accountBalance"=>$this->processAccountBalanceString($accountBalance),
            "BOCompletedTime"=> $this->processCompletedTime($BOCompletedTime),
        ];

        return $result;
    }

    private function processAccountBalanceString(string $accountBalanceDetails){
        $accountDetails = explode('&',$accountBalanceDetails);
        $accountInfo = [];
        array_walk($accountDetails,function ($account) use (&$accountInfo){
            $info = explode('|',$account);
            $accountInfo[] =[
              'accountName' => $info[0],
              'accountCurrency' => $info[1],
              'accountBalance1' => $info[2],
              'accountBalance2' => $info[3],
              'accountBalance3' => $info[4],
              'accountBalance4' => $info[5],
            ];
        });
        return $accountInfo;
    }
}