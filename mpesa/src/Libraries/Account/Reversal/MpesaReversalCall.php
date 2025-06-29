<?php
/**
 * MpesaReversalCall
 *
 * Handles transaction reversal requests to the Mpesa API.
 *
 * @package Rndwiga\Mpesa\Libraries\Account\Reversal
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Libraries\Account\Reversal;

use InvalidArgumentException;
use RuntimeException;
use Rndwiga\Mpesa\Libraries\BaseRequest;
use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaReversalCall extends BaseRequest
{
    /**
     * Provides a sample request for reversing a transaction
     *
     * This method demonstrates how to use the MpesaReversalCall class
     * to reverse a transaction using the Mpesa API.
     * 
     * Note: This is just an example. Replace the placeholder values with your actual credentials.
     *
     * @return string JSON response from the API
     */
    public function sampleRequest(): string
    {
        $response = (new MpesaReversalCall())
            ->setApplicationStatus(false) // false for sandbox, true for production
            ->setInitiatorName(env('INITIATOR_NAME'))
            ->setSecurityCredential(env('SECURITY_CREDENTIAL'))
            ->setConsumerKey(env('CONSUMER_KEY'))
            ->setConsumerSecret(env('CONSUMER_SECRET'))
            ->setCommandId("TransactionReversal")
            ->setReceiverParty(env('PARTY_A')) // The party receiving the reversal
            ->setReceiverIdentifierType(4) // 4 for organization shortcode
            ->setTransactionID("LKXXXX1234") // The M-Pesa Transaction ID to reverse
            ->setAmount(100) // Amount to reverse
            ->setRemarks("Reversing erroneous payment")
            ->setOccasion("Transaction reversal")
            ->setQueueTimeOutUrl(env('QUEUE_TIMEOUT_URL'))
            ->setResultUrl(env('RESULT_URL'))
            ->makeReversalRequestCall();

        return $response;
    }

    /**
     * Make a transaction reversal request to the Mpesa API
     *
     * This method sends a request to the Mpesa API to reverse a transaction.
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
     * - CommandID: Usually "TransactionReversal"
     * - InitiatorName: The name of the initiator
     * - SecurityCredential: The security credential
     * - TransactionID: The M-Pesa Transaction ID to reverse
     * - Amount: Amount to reverse
     * - ReceiverParty: The party receiving the reversal
     * - ReceiverIdentifierType: The type of identifier (usually 4 for organization shortcode)
     * - Remarks: Comments about the reversal
     * - Occasion: The occasion for the reversal
     * - QueueTimeOutURL: The URL to receive timeout notifications
     * - ResultURL: The URL to receive the result
     *
     * @param string|null $transactionId Optional transaction ID (if not provided, uses the one set with setTransactionID)
     * @return string JSON response from the API
     * @throws InvalidArgumentException If required properties are not set
     * @throws RuntimeException If there's an error in the API request
     */
    public function makeReversalRequestCall(string $transactionId = null): string
    {
        // If transaction ID is provided, set it
        if ($transactionId !== null) {
            $this->setTransactionID($transactionId);
        }

        // Validate required properties
        $requiredProperties = [
            'ApplicationStatus', 'ConsumerKey', 'ConsumerSecret', 'CommandID',
            'InitiatorName', 'SecurityCredential', 'TransactionID', 'Amount',
            'ReceiverParty', 'ReceiverIdentifierType', 'Remarks', 'Occasion',
            'QueueTimeOutURL', 'ResultURL'
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
            ? 'https://api.safaricom.co.ke/mpesa/reversal/v1/request'
            : 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request';

        try {
            // Generate access token
            $token = $this->generateAccessToken($this->ApplicationStatus, $this->ConsumerKey, $this->ConsumerSecret);

            // Prepare request data
            $requestData = [
                'Initiator' => $this->InitiatorName,
                'SecurityCredential' => $this->SecurityCredential,
                'CommandID' => $this->CommandID,
                'TransactionID' => $this->TransactionID,
                'Amount' => $this->Amount,
                'ReceiverParty' => $this->ReceiverParty,
                'RecieverIdentifierType' => $this->ReceiverIdentifierType,
                'ResultURL' => $this->ResultURL,
                'QueueTimeOutURL' => $this->QueueTimeOutURL,
                'Remarks' => $this->Remarks,
                'Occasion' => $this->Occasion
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
