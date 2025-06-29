<?php
/**
 * B2CTransactionCallbacks
 *
 * Handles callbacks from Mpesa API for B2C transactions.
 *
 * @package Rndwiga\Mpesa\Libraries\B2C
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Libraries\B2C;

use InvalidArgumentException;

class B2CTransactionCallbacks
{
    /**
     * Process a B2C transaction callback from Mpesa API
     *
     * This method handles the initial processing of B2C transaction callbacks.
     * It determines whether the transaction was successful or failed and routes
     * the callback data to the appropriate processing method.
     *
     * Implementation notes:
     * - For production use, you should implement transaction tracking by:
     *   1. Retrieving the ConversationID and OriginatorConversationID from your database
     *   2. Confirming that the response matches the original request
     *   3. Marking failed transactions for retry
     *   4. Marking successful transactions as complete
     *
     * @param string $requestCallback The JSON callback data from Mpesa API
     * @return string JSON response with processed callback data
     * @throws InvalidArgumentException If the callback data is invalid
     */
    public function b2CRequestCallback(string $requestCallback): string
    {
        if (empty($requestCallback)) {
            throw new InvalidArgumentException('Callback data cannot be empty');
        }

        $callbackData = json_decode($requestCallback);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON in callback data: ' . json_last_error_msg());
        }

        if (!isset($callbackData->Result)) {
            throw new InvalidArgumentException('Invalid callback data structure: Missing Result object');
        }

        // Check if the transaction was successful
        if (isset($callbackData->Result->ResultType) && 
            isset($callbackData->Result->ResultCode) && 
            $callbackData->Result->ResultType == 0 && 
            $callbackData->Result->ResultCode == 0) {
            return $this->processB2CRequestCallback($requestCallback);
        } else {
            return $this->processB2CFailedRequest($requestCallback);
        }
    }

    /**
     * Process a successful B2C transaction callback
     *
     * This method extracts and formats the data from a successful B2C transaction callback.
     * 
     * Example callback data structure:
     * ```json
     * {
     *   "Result": {
     *     "ResultType": 0,
     *     "ResultCode": 0,
     *     "ResultDesc": "The service request is processed successfully.",
     *     "OriginatorConversationID": "25773-1299115-1",
     *     "ConversationID": "AG_20180903_00005efc187c4b350a69",
     *     "TransactionID": "MI31FXT9ZP",
     *     "ResultParameters": {
     *       "ResultParameter": [
     *         {
     *           "Key": "TransactionAmount",
     *           "Value": 19000
     *         },
     *         {
     *           "Key": "TransactionReceipt",
     *           "Value": "MI31FXT9ZP"
     *         },
     *         {
     *           "Key": "ReceiverPartyPublicName",
     *           "Value": "254728309492 - MOGOKO ROBERT AMEMBA"
     *         },
     *         {
     *           "Key": "TransactionCompletedDateTime",
     *           "Value": "03.09.2018 12:40:07"
     *         },
     *         {
     *           "Key": "B2CUtilityAccountAvailableFunds",
     *           "Value": 4986599
     *         },
     *         {
     *           "Key": "B2CWorkingAccountAvailableFunds",
     *           "Value": 0
     *         },
     *         {
     *           "Key": "B2CRecipientIsRegisteredCustomer",
     *           "Value": "Y"
     *         },
     *         {
     *           "Key": "B2CChargesPaidAccountAvailableFunds",
     *           "Value": 0
     *         }
     *       ]
     *     },
     *     "ReferenceData": {
     *       "ReferenceItem": {
     *         "Key": "QueueTimeoutURL",
     *         "Value": "http://internalapi.safaricom.co.ke/mpesa/b2cresults/v1/submit"
     *       }
     *     }
     *   }
     * }
     * ```
     *
     * @param string $callbackJSONData The JSON callback data from Mpesa API
     * @return string JSON formatted response with extracted transaction details
     * @throws InvalidArgumentException If the callback data is invalid
     */
    public function processB2CRequestCallback(string $callbackJSONData): string
    {
        if (empty($callbackJSONData)) {
            throw new InvalidArgumentException('Callback data cannot be empty');
        }

        $callbackData = json_decode($callbackJSONData);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON in callback data: ' . json_last_error_msg());
        }

        if (!isset($callbackData->Result) || 
            !isset($callbackData->Result->ResultParameters) || 
            !isset($callbackData->Result->ResultParameters->ResultParameter)) {
            throw new InvalidArgumentException('Invalid callback data structure: Missing required fields');
        }

        // Extract the ResultParameter array for easier access
        $params = $callbackData->Result->ResultParameters->ResultParameter;

        // Create a response with all transaction details
        return json_encode([
            "status" => 'success',
            "data" => [
                "resultType" => $callbackData->Result->ResultType,
                "resultCode" => $callbackData->Result->ResultCode,
                "resultDesc" => $callbackData->Result->ResultDesc,
                "originatorConversationID" => $callbackData->Result->OriginatorConversationID,
                "conversationID" => $callbackData->Result->ConversationID,
                "transactionID" => $callbackData->Result->TransactionID,
                "transactionAmount" => $params[0]->Value ?? null,
                "transactionReceipt" => $params[1]->Value ?? null,
                "receiverPartyPublicName" => $params[2]->Value ?? null,
                "transactionCompletedDateTime" => $params[3]->Value ?? null,
                "b2CUtilityAccountAvailableFunds" => $params[4]->Value ?? null,
                "b2CWorkingAccountAvailableFunds" => $params[5]->Value ?? null,
                "b2CRecipientIsRegisteredCustomer" => $params[6]->Value ?? null,
                "b2CChargesPaidAccountAvailableFunds" => $params[7]->Value ?? null,
            ]
        ]);
    }


    // The processB2CRequestCallbackDemo method has been removed as it was redundant
    // and contained similar functionality to the processB2CRequestCallback method.





    /**
     * Process a failed B2C transaction callback
     *
     * This method extracts and formats the data from a failed B2C transaction callback.
     * 
     * Example callback data structure for a failed transaction:
     * ```json
     * {
     *   "Result": {
     *     "ResultType": 0,
     *     "ResultCode": 2001,
     *     "ResultDesc": "The initiator information is invalid.",
     *     "OriginatorConversationID": "26636-768521-1",
     *     "ConversationID": "AG_20180503_000068d3116b5b5762ea",
     *     "TransactionID": "ME381H4F9W",
     *     "ReferenceData": {
     *       "ReferenceItem": {
     *         "Key": "QueueTimeoutURL",
     *         "Value": "https://internalsandbox.safaricom.co.ke/mpesa/b2cresults/v1/submit"
     *       }
     *     }
     *   }
     * }
     * ```
     *
     * @param string $callbackJSONData The JSON callback data from Mpesa API
     * @return string JSON formatted response with extracted transaction details
     * @throws InvalidArgumentException If the callback data is invalid
     */
    public function processB2CFailedRequest(string $callbackJSONData): string
    {
        if (empty($callbackJSONData)) {
            throw new InvalidArgumentException('Callback data cannot be empty');
        }

        $callbackData = json_decode($callbackJSONData);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON in callback data: ' . json_last_error_msg());
        }

        if (!isset($callbackData->Result)) {
            throw new InvalidArgumentException('Invalid callback data structure: Missing Result object');
        }

        // Create a response with the failure details
        return json_encode([
            "status" => "fail",
            "data" => [
                "resultType" => $callbackData->Result->ResultType ?? null,
                "resultCode" => $callbackData->Result->ResultCode ?? null,
                "resultDesc" => $callbackData->Result->ResultDesc ?? null,
                "originatorConversationID" => $callbackData->Result->OriginatorConversationID ?? null,
                "conversationID" => $callbackData->Result->ConversationID ?? null,
                "transactionID" => $callbackData->Result->TransactionID ?? null,
            ]
        ]);
    }
}
