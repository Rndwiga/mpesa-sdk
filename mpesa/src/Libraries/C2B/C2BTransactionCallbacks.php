<?php
/**
 * C2BTransactionCallbacks
 *
 * Handles callbacks from Mpesa API for Customer to Business (C2B) transactions.
 *
 * @package Rndwiga\Mpesa\Libraries\C2B
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Libraries\C2B;

use InvalidArgumentException;

class C2BTransactionCallbacks
{

    /**
     * Process a C2B transaction callback from Mpesa API
     *
     * This is a helper method that extracts and formats data from C2B callbacks.
     * It's used by both validation and confirmation callback handlers.
     *
     * Example callback data structure:
     * ```json
     * {
     *   "TransactionType": "Pay Bill",
     *   "TransID": "LK631GQCSP",
     *   "TransTime": "20171106225323",
     *   "TransAmount": "100.00",
     *   "BusinessShortCode": "600000",
     *   "BillRefNumber": "Test",
     *   "InvoiceNumber": "",
     *   "OrgAccountBalance": "900.00",
     *   "ThirdPartyTransID": "",
     *   "MSISDN": "254708374149",
     *   "FirstName": "John",
     *   "MiddleName": "Doe",
     *   "LastName": "Smith"
     * }
     * ```
     *
     * @param string|null $callbackJSONData The JSON callback data from Mpesa API (defaults to php://input)
     * @return array Extracted transaction details
     * @throws InvalidArgumentException If the callback data is invalid
     */
    private function processC2BCallback(string $callbackJSONData = null): array
    {
        // If no data is provided, read from input stream
        if ($callbackJSONData === null) {
            $callbackJSONData = file_get_contents('php://input');
        }

        // Validate input
        if (empty($callbackJSONData)) {
            throw new InvalidArgumentException('Callback data cannot be empty');
        }

        // Decode JSON data
        $callbackData = json_decode($callbackJSONData);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON in callback data: ' . json_last_error_msg());
        }

        // Validate callback data structure
        $requiredFields = [
            'TransactionType', 'TransID', 'TransTime', 'TransAmount', 
            'BusinessShortCode', 'MSISDN'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($callbackData->$field)) {
                throw new InvalidArgumentException("Invalid callback data structure: Missing required field '{$field}'");
            }
        }

        // Extract values with null coalescing for optional fields
        return [
            'TransactionType' => $callbackData->TransactionType,
            'TransID' => $callbackData->TransID,
            'TransTime' => $callbackData->TransTime,
            'TransAmount' => $callbackData->TransAmount,
            'BusinessShortCode' => $callbackData->BusinessShortCode,
            'BillRefNumber' => $callbackData->BillRefNumber ?? '',
            'InvoiceNumber' => $callbackData->InvoiceNumber ?? '',
            'OrgAccountBalance' => $callbackData->OrgAccountBalance ?? '',
            'ThirdPartyTransID' => $callbackData->ThirdPartyTransID ?? '',
            'MSISDN' => $callbackData->MSISDN,
            'FirstName' => $callbackData->FirstName ?? '',
            'MiddleName' => $callbackData->MiddleName ?? '',
            'LastName' => $callbackData->LastName ?? ''
        ];
    }

    /**
     * Process a C2B Validation request callback from Mpesa API
     *
     * This method handles validation callbacks for C2B transactions.
     * It extracts and formats the data from the callback.
     *
     * @param string|null $callbackJSONData The JSON callback data from Mpesa API (defaults to php://input)
     * @return string JSON formatted response with extracted transaction details
     * @throws InvalidArgumentException If the callback data is invalid
     */
    public function processC2BRequestValidation(string $callbackJSONData = null): string
    {
        try {
            $result = $this->processC2BCallback($callbackJSONData);

            // Add validation-specific processing here if needed

            return json_encode([
                'status' => 'success',
                'message' => 'Validation processed successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process a C2B Confirmation request callback from Mpesa API
     *
     * This method handles confirmation callbacks for C2B transactions.
     * It extracts and formats the data from the callback.
     *
     * @param string|null $callbackJSONData The JSON callback data from Mpesa API (defaults to php://input)
     * @return string JSON formatted response with extracted transaction details
     * @throws InvalidArgumentException If the callback data is invalid
     */
    public function processC2BRequestConfirmation(string $callbackJSONData = null): string
    {
        try {
            $result = $this->processC2BCallback($callbackJSONData);

            // Add confirmation-specific processing here if needed

            return json_encode([
                'status' => 'success',
                'message' => 'Confirmation processed successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
