<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 3:11 PM
 */

namespace Rndwiga\Mpesa\Libraries\B2B;

use Rndwiga\Toolbox\Infrastructure\Services\AppLogger;

class B2BTransactionCallbacks
{
    /**
     * Process the B2B request callback (instance method)
     * 
     * @param string|null $callbackData JSON data from the callback (if null, reads from php://input)
     * @return string JSON encoded result
     */
    public function processCallback($callbackData = null)
    {
        try {
            // Get callback data
            if ($callbackData === null) {
                $callbackData = file_get_contents('php://input');
            }

            // Decode JSON data
            $decodedData = json_decode($callbackData);

            // Check if JSON is valid
            if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON data: ' . json_last_error_msg());
            }

            // Check if required properties exist
            if (!isset($decodedData->Result) || 
                !isset($decodedData->Result->ResultCode) || 
                !isset($decodedData->Result->ResultDesc)) {
                throw new \InvalidArgumentException('Missing required properties in callback data');
            }

            // Extract data from callback
            $resultCode = $decodedData->Result->ResultCode;
            $resultDesc = $decodedData->Result->ResultDesc;
            $originatorConversationID = $decodedData->Result->OriginatorConversationID ?? null;
            $conversationID = $decodedData->Result->ConversationID ?? null;
            $transactionID = $decodedData->Result->TransactionID ?? null;

            // Extract result parameters if they exist
            $resultParameters = [];
            if (isset($decodedData->Result->ResultParameters) && 
                isset($decodedData->Result->ResultParameters->ResultParameter)) {

                $params = $decodedData->Result->ResultParameters->ResultParameter;

                $transactionReceipt = isset($params[0]) ? $params[0]->Value : null;
                $transactionAmount = isset($params[1]) ? $params[1]->Value : null;
                $b2bWorkingAccountAvailableFunds = isset($params[2]) ? $params[2]->Value : null;
                $b2bUtilityAccountAvailableFunds = isset($params[3]) ? $params[3]->Value : null;
                $transactionCompletedDateTime = isset($params[4]) ? $params[4]->Value : null;
                $receiverPartyPublicName = isset($params[5]) ? $params[5]->Value : null;
                $b2bChargesPaidAccountAvailableFunds = isset($params[6]) ? $params[6]->Value : null;
                $b2bRecipientIsRegisteredCustomer = isset($params[7]) ? $params[7]->Value : null;

                $resultParameters = [
                    "transactionReceipt" => $transactionReceipt,
                    "transactionAmount" => $transactionAmount,
                    "b2bWorkingAccountAvailableFunds" => $b2bWorkingAccountAvailableFunds,
                    "b2bUtilityAccountAvailableFunds" => $b2bUtilityAccountAvailableFunds,
                    "transactionCompletedDateTime" => $transactionCompletedDateTime,
                    "receiverPartyPublicName" => $receiverPartyPublicName,
                    "b2bChargesPaidAccountAvailableFunds" => $b2bChargesPaidAccountAvailableFunds,
                    "b2bRecipientIsRegisteredCustomer" => $b2bRecipientIsRegisteredCustomer
                ];
            }

            // Build result array
            $result = [
                "resultCode" => $resultCode,
                "resultDesc" => $resultDesc,
                "originatorConversationID" => $originatorConversationID,
                "conversationID" => $conversationID,
                "transactionID" => $transactionID
            ];

            // Add result parameters if they exist
            if (!empty($resultParameters)) {
                $result = array_merge($result, $resultParameters);
            }

            return json_encode($result);

        } catch (\Exception $e) {
            // Log the error
            if (class_exists('Rndwiga\Toolbox\Infrastructure\Services\AppLogger')) {
                (new AppLogger('mpesaSDKApp_B2B', 'b2b_callback_error'))->logInfo([$e->getMessage()]);
            }

            // Return error response
            return json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Use this function to process the B2B request callback (static method for backward compatibility)
     * @return string
     */
    public static function processB2BRequestCallback()
    {
        return (new self())->processCallback();
    }
}
