<?php
/**
 * AccountBalanceTransactionCallbacks
 *
 * Handles callbacks from Mpesa API for Account Balance transactions.
 *
 * @package Rndwiga\Mpesa\Libraries\Account\Account
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Libraries\Account\Account;

use InvalidArgumentException;
use Rndwiga\Mpesa\Libraries\MpesaBaseClass;

class AccountBalanceTransactionCallbacks extends MpesaBaseClass
{
    /**
     * Process an account balance request or response
     *
     * This method determines the status of an account balance request or response
     * and adds a status indicator to the response array.
     *
     * @param array $balanceRequestResponse The request or response data from Mpesa API
     * @param bool $isRequest Whether this is a request (true) or response (false)
     * @return array The processed response with added status indicator
     * @throws InvalidArgumentException If the response data is invalid
     */
    public function processRequest(array $balanceRequestResponse, bool $isRequest = true): array
    {
        // Validate input
        if (empty($balanceRequestResponse)) {
            throw new InvalidArgumentException('Balance request response cannot be empty');
        }

        // Process request
        if ($isRequest === true) {
            // Check if it's a success response
            if (isset($balanceRequestResponse['ResponseCode'])) {
                $balanceRequestResponse['transactionRequestStatus'] = 
                    ($balanceRequestResponse['ResponseCode'] == 0) ? 'success' : 'fail';
                return $balanceRequestResponse;
            } 
            // Check if it's an error response
            elseif (isset($balanceRequestResponse['fault'])) {
                $balanceRequestResponse['transactionRequestStatus'] = 'fault';
                return $balanceRequestResponse;
            }
            // Invalid response format
            else {
                throw new InvalidArgumentException('Invalid balance request response format');
            }
        } 
        // Process response
        else {
            if (!isset($balanceRequestResponse['resultType'])) {
                throw new InvalidArgumentException('Invalid balance response format: Missing resultType');
            }

            $balanceRequestResponse['transactionResultStatus'] = 
                ($balanceRequestResponse['resultType'] == 0) ? 'success' : 'fail';
            return $balanceRequestResponse;
        }
    }

    /**
     * Process an Account Balance request callback from Mpesa API
     *
     * This method extracts and formats the data from an Account Balance transaction callback.
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
     *           "Key": "AccountBalance",
     *           "Value": "Working Account|KES|481000.00|481000.00|0.00|0.00"
     *         },
     *         {
     *           "Key": "BOCompletedTime",
     *           "Value": "20180708145034"
     *         }
     *       ]
     *     },
     *     "ReferenceData": {
     *       "ReferenceItem": {
     *         "Key": "QueueTimeoutURL",
     *         "Value": "https://your-domain.com/api/timeout"
     *       }
     *     }
     *   }
     * }
     * ```
     *
     * @param array $transactionResult The transaction result data from Mpesa API
     * @return array Formatted response with extracted transaction details
     * @throws InvalidArgumentException If the transaction result is invalid
     */
    public function processAccountBalanceRequestCallback(array $transactionResult): array
    {
        // Validate input
        if (empty($transactionResult)) {
            throw new InvalidArgumentException('Transaction result cannot be empty');
        }

        // Convert array to object for easier access
        $callbackData = json_decode(json_encode($transactionResult));

        // Validate callback data structure
        if (!isset($callbackData->Result) || 
            !isset($callbackData->Result->ResultParameters) || 
            !isset($callbackData->Result->ResultParameters->ResultParameter)) {
            throw new InvalidArgumentException('Invalid callback data structure: Missing required fields');
        }

        // Extract result parameters
        $resultParams = $callbackData->Result->ResultParameters->ResultParameter;

        // Validate result parameters
        if (count($resultParams) < 2) {
            throw new InvalidArgumentException('Invalid callback data: Missing expected result parameters');
        }

        // Extract values
        $accountBalance = $resultParams[0]->Value ?? null;
        $BOCompletedTime = $resultParams[1]->Value ?? null;

        if (!$accountBalance || !$BOCompletedTime) {
            throw new InvalidArgumentException('Invalid callback data: Missing account balance or completed time');
        }

        // Format the response
        return [
            "resultType" => $callbackData->Result->ResultType ?? null,
            "resultCode" => $callbackData->Result->ResultCode ?? null,
            "resultDesc" => $callbackData->Result->ResultDesc ?? null,
            "originatorConversationID" => $callbackData->Result->OriginatorConversationID ?? null,
            "conversationID" => $callbackData->Result->ConversationID ?? null,
            "transactionID" => $callbackData->Result->TransactionID ?? null,
            "accountBalance" => $this->processAccountBalanceString($accountBalance),
            "BOCompletedTime" => $this->processCompletedTime($BOCompletedTime),
        ];
    }

    /**
     * Process the account balance string from Mpesa API
     *
     * This method parses the account balance string from Mpesa API and
     * extracts the account details into a structured array.
     *
     * Example account balance string:
     * "Working Account|KES|481000.00|481000.00|0.00|0.00&Float Account|KES|0.00|0.00|0.00|0.00&Utility Account|KES|0.00|0.00|0.00|0.00"
     *
     * @param string $accountBalanceDetails The account balance string from Mpesa API
     * @return array Structured array with account details
     */
    private function processAccountBalanceString(string $accountBalanceDetails): array
    {
        // Split the account balance string by '&' to get individual account details
        $accountDetails = explode('&', $accountBalanceDetails);
        $accountInfo = [];

        // Process each account detail
        array_walk($accountDetails, function ($account, $key) use (&$accountInfo) {
            // Split each account detail by '|' to get account properties
            $info = explode('|', $account);

            // Ensure we have enough elements in the info array
            if (count($info) < 6) {
                return;
            }

            // Special handling for utility account (index 2)
            if ($key == 2) {
                $accountInfo[] = (int) $info[2];
                $accountInfo['utilityAccountArray'] = [
                    'accountName' => $info[0],
                    'accountCurrency' => $info[1],
                    'accountBalance1' => $info[2],
                    'accountBalance2' => $info[3],
                    'accountBalance3' => $info[4],
                    'accountBalance4' => $info[5],
                ];
            } else {
                $accountInfo[] = (int) $info[2];
            }
        });

        return $accountInfo;
    }
}
