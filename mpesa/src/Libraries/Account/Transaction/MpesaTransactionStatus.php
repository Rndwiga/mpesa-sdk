<?php
/**
 * MpesaTransactionStatus
 *
 * Handles transaction status queries to the Mpesa API.
 *
 * @package Rndwiga\Mpesa\Libraries\Account\Transaction
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Libraries\Account\Transaction;

use InvalidArgumentException;
use RuntimeException;
use Rndwiga\Mpesa\Libraries\BaseRequest;

class MpesaTransactionStatus extends BaseRequest
{
    /**
     * Provides a sample request for querying transaction status
     *
     * This method demonstrates how to use the MpesaTransactionStatus class
     * to query the status of a transaction using the Mpesa API.
     * 
     * Note: This is just an example. Replace the placeholder values with your actual credentials.
     *
     * @return string JSON response from the API
     */
    public function sampleRequest(): string
    {
        $response = (new MpesaTransactionStatus())
            ->setApplicationStatus(false) // false for sandbox, true for production
            ->setInitiatorName(env('INITIATOR_NAME'))
            ->setSecurityCredential(env('SECURITY_CREDENTIAL'))
            ->setConsumerKey(env('CONSUMER_KEY'))
            ->setConsumerSecret(env('CONSUMER_SECRET'))
            ->setCommandId("TransactionStatusQuery")
            ->setPartyA(env('PARTY_A')) // Your shortcode
            ->setIdentifierType(4) // 4 for organization shortcode
            ->setRemarks("Transaction status query")
            ->setTransactionID("LKXXXX1234") // The M-Pesa Transaction ID to query
            ->setQueueTimeOutUrl(env('QUEUE_TIMEOUT_URL'))
            ->setResultUrl(env('RESULT_URL'))
            ->makeTransactionStatusCall();

        return $response;
    }

    /**
     * Make a transaction status query to the Mpesa API
     *
     * This method sends a request to the Mpesa API to query the status of a transaction.
     * 
     * Example successful response:
     * ```json
     * {
     *   "OriginatorConversationID": "29115-34620561-1",
     *   "ConversationID": "AG_20180708_00004636b4e35055927d",
     *   "ResponseCode": "0",
     *   "ResponseDescription": "Accept the service request successfully."
     * }
     * ```
     *
     * Required properties that must be set before calling this method:
     * - ApplicationStatus: true for production, false for sandbox
     * - ConsumerKey: Your API consumer key
     * - ConsumerSecret: Your API consumer secret
     * - CommandID: Usually "TransactionStatusQuery"
     * - InitiatorName: The name of the initiator
     * - SecurityCredential: The security credential
     * - TransactionID: The M-Pesa Transaction ID to query
     * - PartyA: Your shortcode
     * - IdentifierType: The type of identifier (usually 4 for organization shortcode)
     * - Remarks: Comments about the query
     * - Occasion: The occasion for the query (optional)
     * - QueueTimeOutURL: The URL to receive timeout notifications
     * - ResultURL: The URL to receive the result
     *
     * @return string JSON response from the API
     * @throws InvalidArgumentException If required properties are not set
     * @throws RuntimeException If there's an error in the API request
     */
    public function makeTransactionStatusCall(): string
    {
        // Validate required properties
        $requiredProperties = [
            'ApplicationStatus', 'ConsumerKey', 'ConsumerSecret', 'CommandID',
            'InitiatorName', 'SecurityCredential', 'TransactionID', 'PartyA',
            'IdentifierType', 'Remarks', 'ResultURL', 'QueueTimeOutURL'
        ];

        foreach ($requiredProperties as $property) {
            if (empty($this->$property)) {
                throw new InvalidArgumentException("Required property '{$property}' is not set");
            }
        }

        // Determine if we're using live or sandbox environment
        $isLive = $this->ApplicationStatus === true;

        // Get the appropriate URL based on environment
        $url = $isLive 
            ? 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query'
            : 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query';

        try {
            // Generate access token
            $token = $this->generateAccessToken($this->ApplicationStatus, $this->ConsumerKey, $this->ConsumerSecret);

            // Prepare request data
            $requestData = [
                'Initiator' => $this->InitiatorName,
                'SecurityCredential' => $this->SecurityCredential,
                'CommandID' => $this->CommandID,
                'TransactionID' => $this->TransactionID,
                'PartyA' => $this->PartyA,
                'IdentifierType' => $this->IdentifierType,
                'ResultURL' => $this->ResultURL,
                'QueueTimeOutURL' => $this->QueueTimeOutURL,
                'Remarks' => $this->Remarks,
                'Occasion' => $this->Occasion ?? ''
            ];

            // Initialize cURL session
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ]);

            // Set SSL verification based on environment
            if (!$isLive) {
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            }

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($curl, CURLOPT_HEADER, false);

            // Execute cURL request
            $response = curl_exec($curl);
            $error = curl_error($curl);

            curl_close($curl);

            // Handle cURL errors
            if ($error) {
                throw new RuntimeException("cURL Error: " . $error);
            }

            return $response;

        } catch (\Exception $e) {
            // Convert any exceptions to JSON error response
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
