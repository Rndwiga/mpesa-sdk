<?php
/**
 * TransactionReversalCallbacks
 *
 * Handles callbacks from Mpesa API for Transaction Reversal requests.
 *
 * @package Rndwiga\Mpesa\Libraries\Account\Reversal
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Libraries\Account\Reversal;

use InvalidArgumentException;

class TransactionReversalCallbacks
{
    /**
     * Process a Transaction Reversal request callback from Mpesa API
     *
     * This method extracts and formats the data from a Transaction Reversal callback.
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
     *     "TransactionID": "LK56GT016"
     *   }
     * }
     * ```
     *
     * @param array $reversalDetailsArray The transaction reversal callback data from Mpesa API
     * @return array Formatted response with extracted transaction details
     * @throws InvalidArgumentException If the callback data is invalid
     */
    public function processReversalRequestCallBack(array $reversalDetailsArray): array
    {
        // Validate input
        if (empty($reversalDetailsArray)) {
            throw new InvalidArgumentException('Reversal details array cannot be empty');
        }

        // Convert array to object for easier access
        $callbackData = json_decode(json_encode($reversalDetailsArray));

        // Validate callback data structure
        if (!isset($callbackData->Result) || 
            !isset($callbackData->Result->ResultCode) || 
            !isset($callbackData->Result->TransactionID)) {
            throw new InvalidArgumentException('Invalid callback data structure: Missing required fields');
        }

        // Extract values
        $resultType = $callbackData->Result->ResultType ?? null;
        $resultCode = $callbackData->Result->ResultCode ?? null;
        $resultDesc = $callbackData->Result->ResultDesc ?? null;
        $originatorConversationID = $callbackData->Result->OriginatorConversationID ?? null;
        $conversationID = $callbackData->Result->ConversationID ?? null;
        $transactionID = $callbackData->Result->TransactionID ?? null;

        // Format the response
        return [
            "resultType" => $resultType,
            "resultCode" => $resultCode,
            "resultDesc" => $resultDesc,
            "conversationID" => $conversationID,
            "transactionID" => $transactionID,
            "originatorConversationID" => $originatorConversationID
        ];
    }
}
