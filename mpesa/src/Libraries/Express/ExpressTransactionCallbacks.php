<?php
/**
 * ExpressTransactionCallbacks
 *
 * Handles callbacks from Mpesa API for Express (STK Push) transactions.
 *
 * @package Rndwiga\Mpesa\Libraries\Express
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Libraries\Express;

use InvalidArgumentException;

class ExpressTransactionCallbacks
{
    /**
     * Process an STK Push transaction callback from Mpesa API
     *
     * This method extracts and formats the data from an STK Push transaction callback.
     * 
     * Example callback data structure:
     * ```json
     * {
     *   "Body": {
     *     "stkCallback": {
     *       "MerchantRequestID": "29115-34620561-1",
     *       "CheckoutRequestID": "ws_CO_191219202020154",
     *       "ResultCode": 0,
     *       "ResultDesc": "The service request is processed successfully.",
     *       "CallbackMetadata": {
     *         "Item": [
     *           {
     *             "Name": "Amount",
     *             "Value": 1
     *           },
     *           {
     *             "Name": "MpesaReceiptNumber",
     *             "Value": "NLJ7RT61SV"
     *           },
     *           {
     *             "Name": "Balance"
     *           },
     *           {
     *             "Name": "TransactionDate",
     *             "Value": 20191219202115
     *           },
     *           {
     *             "Name": "PhoneNumber",
     *             "Value": 254722000000
     *           }
     *         ]
     *       }
     *     }
     *   }
     * }
     * ```
     *
     * @param string|null $callbackJSONData The JSON callback data from Mpesa API (defaults to php://input)
     * @return string JSON formatted response with extracted transaction details
     * @throws InvalidArgumentException If the callback data is invalid
     */
    public function processSTKPushRequestCallback(string $callbackJSONData = null): string
    {
        // If no data is provided, read from input stream
        if ($callbackJSONData === null) {
            $callbackJSONData = file_get_contents('php://input');
        }

        if (empty($callbackJSONData)) {
            throw new InvalidArgumentException('Callback data cannot be empty');
        }

        $callbackData = json_decode($callbackJSONData);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON in callback data: ' . json_last_error_msg());
        }

        // Check if the callback data has the expected structure
        if (!isset($callbackData->Body->stkCallback) || 
            !isset($callbackData->Body->stkCallback->CallbackMetadata) || 
            !isset($callbackData->Body->stkCallback->CallbackMetadata->Item)) {
            throw new InvalidArgumentException('Invalid callback data structure: Missing required fields');
        }

        $stkCallback = $callbackData->Body->stkCallback;
        $items = $stkCallback->CallbackMetadata->Item;

        // Create a response with all transaction details
        $result = [
            "resultDesc" => $stkCallback->ResultDesc ?? null,
            "resultCode" => $stkCallback->ResultCode ?? null,
            "merchantRequestID" => $stkCallback->MerchantRequestID ?? null,
            "checkoutRequestID" => $stkCallback->CheckoutRequestID ?? null,
            "amount" => $this->getItemValue($items, 0, 'Amount'),
            "mpesaReceiptNumber" => $this->getItemValue($items, 1, 'MpesaReceiptNumber'),
            "balance" => $this->getItemValue($items, 2, 'Balance'),
            "transactionDate" => $this->getItemValue($items, 3, 'TransactionDate'),
            "phoneNumber" => $this->getItemValue($items, 4, 'PhoneNumber')
        ];

        return json_encode($result);
    }

    /**
     * Process an STK Push query request callback from Mpesa API
     *
     * This method extracts and formats the data from an STK Push query transaction callback.
     * 
     * Example callback data structure:
     * ```json
     * {
     *   "ResponseCode": "0",
     *   "ResponseDescription": "The service request has been accepted successfully",
     *   "MerchantRequestID": "25465-1234567-1",
     *   "CheckoutRequestID": "ws_CO_DMZ_12345678_12345678",
     *   "ResultCode": "0",
     *   "ResultDesc": "The service request is processed successfully"
     * }
     * ```
     *
     * @param string|null $callbackJSONData The JSON callback data from Mpesa API (defaults to php://input)
     * @return string JSON formatted response with extracted transaction details
     * @throws InvalidArgumentException If the callback data is invalid
     */
    public function processSTKPushQueryRequestCallback(string $callbackJSONData = null): string
    {
        // If no data is provided, read from input stream
        if ($callbackJSONData === null) {
            $callbackJSONData = file_get_contents('php://input');
        }

        if (empty($callbackJSONData)) {
            throw new InvalidArgumentException('Callback data cannot be empty');
        }

        $callbackData = json_decode($callbackJSONData);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON in callback data: ' . json_last_error_msg());
        }

        // Check if the callback data has the expected structure
        if (!isset($callbackData->ResponseCode) || 
            !isset($callbackData->MerchantRequestID) || 
            !isset($callbackData->CheckoutRequestID)) {
            throw new InvalidArgumentException('Invalid callback data structure: Missing required fields');
        }

        // Create a response with all transaction details
        $result = [
            "resultCode" => $callbackData->ResultCode ?? null,
            "responseDescription" => $callbackData->ResponseDescription ?? null,
            "responseCode" => $callbackData->ResponseCode ?? null,
            "merchantRequestID" => $callbackData->MerchantRequestID ?? null,
            "checkoutRequestID" => $callbackData->CheckoutRequestID ?? null,
            "resultDesc" => $callbackData->ResultDesc ?? null
        ];

        return json_encode($result);
    }

    /**
     * Helper method to safely get a value from the callback items array
     *
     * @param array $items The array of items from the callback
     * @param int $index The index of the item to retrieve
     * @param string $expectedName The expected name of the item
     * @return mixed|null The value of the item or null if not found
     */
    private function getItemValue(array $items, int $index, string $expectedName)
    {
        if (!isset($items[$index])) {
            return null;
        }

        $item = $items[$index];

        // Check if the item has the expected name
        if (!isset($item->Name) || $item->Name !== $expectedName) {
            // Try to find the item by name instead of index
            foreach ($items as $i) {
                if (isset($i->Name) && $i->Name === $expectedName && isset($i->Value)) {
                    return $i->Value;
                }
            }
            return null;
        }

        return $item->Value ?? null;
    }
}
