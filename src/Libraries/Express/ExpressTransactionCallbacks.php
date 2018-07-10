<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:18 PM
 */

namespace Rndwiga\Mpesa\Libraries\Express;


class ExpressTransactionCallbacks
{
    /**
     * Use this function to process the STK push request callback
     * @return string
     */
    public static function processSTKPushRequestCallback(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);
        $resultCode=$callbackData->stkCallback->ResultCode;
        $resultDesc=$callbackData->stkCallback->ResultDesc;
        $merchantRequestID=$callbackData->stkCallback->MerchantRequestID;
        $checkoutRequestID=$callbackData->stkCallback->CheckoutRequestID;
        $amount=$callbackData->stkCallback->CallbackMetadata->Item[0]->Value;
        $mpesaReceiptNumber=$callbackData->stkCallback->CallbackMetadata->Item[1]->Value;
        $balance=$callbackData->stkCallback->CallbackMetadata->Item[2]->Value;
        $b2CUtilityAccountAvailableFunds=$callbackData->stkCallback->CallbackMetadata->Item[3]->Value;
        $transactionDate=$callbackData->stkCallback->CallbackMetadata->Item[4]->Value;
        $phoneNumber=$callbackData->stkCallback->CallbackMetadata->Item[5]->Value;

        $result=[
            "resultDesc"=>$resultDesc,
            "resultCode"=>$resultCode,
            "merchantRequestID"=>$merchantRequestID,
            "checkoutRequestID"=>$checkoutRequestID,
            "amount"=>$amount,
            "mpesaReceiptNumber"=>$mpesaReceiptNumber,
            "balance"=>$balance,
            "b2CUtilityAccountAvailableFunds"=>$b2CUtilityAccountAvailableFunds,
            "transactionDate"=>$transactionDate,
            "phoneNumber"=>$phoneNumber
        ];

        return json_encode($result);
    }

    /**
     * Use this function to process the STK Push  request callback
     * @return string
     */
    public static function processSTKPushQueryRequestCallback(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);
        $responseCode=$callbackData->ResponseCode;
        $responseDescription=$callbackData->ResponseDescription;
        $merchantRequestID=$callbackData->MerchantRequestID;
        $checkoutRequestID=$callbackData->CheckoutRequestID;
        $resultCode=$callbackData->ResultCode;
        $resultDesc=$callbackData->ResultDesc;

        $result=[
            "resultCode"=>$resultCode,
            "responseDescription"=>$responseDescription,
            "responseCode"=>$responseCode,
            "merchantRequestID"=>$merchantRequestID,
            "checkoutRequestID"=>$checkoutRequestID,
            "resultDesc"=>$resultDesc
        ];

        return json_encode($result);
    }

}