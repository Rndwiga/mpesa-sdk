<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:13 PM
 */

namespace Rndwiga\Mpesa\Libraries\C2B;


class C2BTransactionCallbacks
{

    /**
     * Use this function to process the C2B Validation request callback
     * @return string
     */
    public static function processC2BRequestValidation(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);
        $transactionType=$callbackData->TransactionType;
        $transID=$callbackData->TransID;
        $transTime=$callbackData->TransTime;
        $transAmount=$callbackData->TransAmount;
        $businessShortCode=$callbackData->BusinessShortCode;
        $billRefNumber=$callbackData->BillRefNumber;
        $invoiceNumber=$callbackData->InvoiceNumber;
        $orgAccountBalance=$callbackData->OrgAccountBalance;
        $thirdPartyTransID=$callbackData->ThirdPartyTransID;
        $MSISDN=$callbackData->MSISDN;
        $firstName=$callbackData->FirstName;
        $middleName=$callbackData->MiddleName;
        $lastName=$callbackData->LastName;

        $result=[
            $transTime=>$transTime,
            $transAmount=>$transAmount,
            $businessShortCode=>$businessShortCode,
            $billRefNumber=>$billRefNumber,
            $invoiceNumber=>$invoiceNumber,
            $orgAccountBalance=>$orgAccountBalance,
            $thirdPartyTransID=>$thirdPartyTransID,
            $MSISDN=>$MSISDN,
            $firstName=>$firstName,
            $lastName=>$lastName,
            $middleName=>$middleName,
            $transID=>$transID,
            $transactionType=>$transactionType

        ];

        return json_encode($result);

    }

    /**
     * Use this function to process the C2B Confirmation result callback
     * @return string
     */
    public static function processC2BRequestConfirmation(){
        $callbackJSONData=file_get_contents('php://input');
        $callbackData=json_decode($callbackJSONData);
        $transactionType=$callbackData->TransactionType;
        $transID=$callbackData->TransID;
        $transTime=$callbackData->TransTime;
        $transAmount=$callbackData->TransAmount;
        $businessShortCode=$callbackData->BusinessShortCode;
        $billRefNumber=$callbackData->BillRefNumber;
        $invoiceNumber=$callbackData->InvoiceNumber;
        $orgAccountBalance=$callbackData->OrgAccountBalance;
        $thirdPartyTransID=$callbackData->ThirdPartyTransID;
        $MSISDN=$callbackData->MSISDN;
        $firstName=$callbackData->FirstName;
        $middleName=$callbackData->MiddleName;
        $lastName=$callbackData->LastName;

        $result=[
            $transTime=>$transTime,
            $transAmount=>$transAmount,
            $businessShortCode=>$businessShortCode,
            $billRefNumber=>$billRefNumber,
            $invoiceNumber=>$invoiceNumber,
            $orgAccountBalance=>$orgAccountBalance,
            $thirdPartyTransID=>$thirdPartyTransID,
            $MSISDN=>$MSISDN,
            $firstName=>$firstName,
            $lastName=>$lastName,
            $middleName=>$middleName,
            $transID=>$transID,
            $transactionType=>$transactionType

        ];

        return json_encode($result);
    }
}