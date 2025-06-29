<?php
/**
 * TransactionStatusCallback
 *
 * Handles callbacks from Mpesa API for Transaction Status queries.
 *
 * @package Rndwiga\Mpesa\Libraries\Account\Transaction
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Libraries\Account\Transaction;

use InvalidArgumentException;
use Rndwiga\Mpesa\Libraries\MpesaBaseClass;

class TransactionStatusCallback extends MpesaBaseClass
{
    /**
     * Process a transaction request response or result
     *
     * This method determines the status of a transaction request or response
     * and adds a status indicator to the response array.
     *
     * @param array $transactionRequest The request or response data from Mpesa API
     * @param bool $isRequest Whether this is a request (true) or response (false)
     * @return array The processed response with added status indicator
     * @throws InvalidArgumentException If the response data is invalid
     */
    public function processTransactionRequestResponse(array $transactionRequest, bool $isRequest = true): array
    {
        // Validate input
        if (empty($transactionRequest)) {
            throw new InvalidArgumentException('Transaction request response cannot be empty');
        }

        // Process request
        if ($isRequest === true) {
            // Check if it's a success response
            if (isset($transactionRequest['ResponseCode'])) {
                $transactionRequest['transactionRequestStatus'] = 
                    ($transactionRequest['ResponseCode'] == 0) ? 'success' : 'fail';
                return $transactionRequest;
            } 
            // Check if it's an error response
            elseif (isset($transactionRequest['fault'])) {
                $transactionRequest['transactionRequestStatus'] = 'fault';
                return $transactionRequest;
            }
            // Invalid response format
            else {
                throw new InvalidArgumentException('Invalid transaction request response format');
            }
        } 
        // Process response
        else {
            if (!isset($transactionRequest['resultCode'])) {
                throw new InvalidArgumentException('Invalid transaction response format: Missing resultCode');
            }

            $transactionRequest['transactionResultStatus'] = 
                ($transactionRequest['resultCode'] == 0) ? 'success' : 'fail';
            return $transactionRequest;
        }
    }

    /**
     * Process a Transaction Status request callback from Mpesa API
     *
     * This method extracts and formats the data from a Transaction Status callback.
     * It handles both live and sandbox environments, which have slightly different
     * response structures.
     * 
     * Example callback data structure:
     * ```json
     * {
     *   "Result": {
     *     "ResultType": 0,
     *     "ResultCode": 0,
     *     "ResultDesc": "The service request is processed successfully.",
     *     "OriginatorConversationID": "29115-34620561-1",
     *     "ConversationID": "AG_20180708_00004636b4e35055927d",
     *     "TransactionID": "LK56GT016",
     *     "ResultParameters": {
     *       "ResultParameter": [
     *         {
     *           "Key": "ReceiptNo",
     *           "Value": "LK56GT016"
     *         },
     *         {
     *           "Key": "ConversationID",
     *           "Value": "AG_20180708_00004636b4e35055927d"
     *         },
     *         // ... more parameters
     *       ]
     *     }
     *   }
     * }
     * ```
     *
     * @param array $resultArray The transaction status callback data from Mpesa API
     * @param bool $isLive Whether this is a live (true) or sandbox (false) environment
     * @return array Formatted response with extracted transaction details
     * @throws InvalidArgumentException If the callback data is invalid
     */
    public function processTransactionStatusRequestCallback(array $resultArray, bool $isLive = true): array
    {
        // Validate input
        if (empty($resultArray)) {
            throw new InvalidArgumentException('Transaction status result array cannot be empty');
        }

        // Convert array to object for easier access
        $callbackData = json_decode(json_encode($resultArray));

        // Validate callback data structure
        if (!isset($callbackData->Result) || 
            !isset($callbackData->Result->ResultCode) || 
            !isset($callbackData->Result->ResultParameters) ||
            !isset($callbackData->Result->ResultParameters->ResultParameter)) {
            throw new InvalidArgumentException('Invalid callback data structure: Missing required fields');
        }

        // Extract common fields
        $resultType = $callbackData->Result->ResultType ?? null;
        $resultCode = $callbackData->Result->ResultCode ?? null;
        $resultDesc = $callbackData->Result->ResultDesc ?? null;
        $originatorConversationID = $callbackData->Result->OriginatorConversationID ?? null;
        $conversationID = $callbackData->Result->ConversationID ?? null;
        $transactionID = $callbackData->Result->TransactionID ?? null;

        // Get result parameters
        $resultParams = $callbackData->Result->ResultParameters->ResultParameter;

        // Different parameter order based on environment
        if ($isLive) {
            // Live environment parameter mapping
            $paramMap = [
                'ReceiptNo' => 0,
                'ConversationID' => 1,
                'FinalisedTime' => 2,
                'Amount' => 3,
                'TransactionStatus' => 4,
                'ReasonType' => 5,
                'TransactionReason' => 6,
                'DebitPartyCharges' => 7,
                'DebitAccountType' => 8,
                'InitiatedTime' => 9,
                'OriginatorConversationID' => 10,
                'CreditPartyName' => 11,
                'DebitPartyName' => 12
            ];
        } else {
            // Sandbox environment parameter mapping
            $paramMap = [
                'DebitPartyName' => 0,
                'CreditPartyName' => 1,
                'OriginatorConversationID' => 2,
                'InitiatedTime' => 3,
                'DebitAccountType' => 4,
                'DebitPartyCharges' => 5,
                'TransactionReason' => 6,
                'ReasonType' => 7,
                'TransactionStatus' => 8,
                'FinalisedTime' => 9,
                'Amount' => 10,
                'ConversationID' => 11,
                'ReceiptNo' => 12
            ];
        }

        // Extract values using the parameter map
        $values = [];
        foreach ($paramMap as $key => $index) {
            $values[$key] = isset($resultParams[$index]) ? $resultParams[$index]->Value ?? null : null;
        }

        // Format the response
        return [
            "resultType" => $resultType,
            "resultCode" => $resultCode,
            "resultDesc" => $resultDesc,
            "StatusOriginatorConversationID" => $originatorConversationID,
            "StatusConversationID" => $conversationID,
            "TransactionID" => $transactionID,
            "ReceiptNo" => $values['ReceiptNo'] ?? null,
            "ConversationID" => $values['ConversationID'] ?? null,
            "FinalisedTime" => isset($values['FinalisedTime']) ? $this->processCompletedTime($values['FinalisedTime']) : null,
            "Amount" => $values['Amount'] ?? null,
            "TransactionStatus" => $values['TransactionStatus'] ?? null,
            "ReasonType" => $values['ReasonType'] ?? null,
            "TransactionReason" => $values['TransactionReason'] ?? '',
            "DebitPartyCharges" => $values['DebitPartyCharges'] ?? null,
            "DebitAccountType" => $values['DebitAccountType'] ?? null,
            "InitiatedTime" => isset($values['InitiatedTime']) ? $this->processCompletedTime($values['InitiatedTime']) : null,
            "OriginatorConversationID" => $values['OriginatorConversationID'] ?? null,
            "CreditPartyName" => $values['CreditPartyName'] ?? null,
            "DebitPartyName" => $values['DebitPartyName'] ?? null
        ];
    }
}
