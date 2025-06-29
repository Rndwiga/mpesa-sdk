<?php
/**
 * MpesaAccountBalance
 *
 * Handles account balance queries to the Mpesa API.
 *
 * @package Rndwiga\Mpesa\Libraries\Account\Account
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Libraries\Account\Account;

use InvalidArgumentException;
use RuntimeException;
use Rndwiga\Mpesa\Libraries\BaseRequest;
use Rndwiga\Mpesa\Libraries\MpesaApiConnection;

class MpesaAccountBalance extends BaseRequest
{
    /**
     * Provides a sample request for querying account balance
     *
     * This method demonstrates how to use the MpesaAccountBalance class
     * to query an account balance from the Mpesa API.
     *
     * @return string JSON response from the API
     */
    public function sampleRequest(): string
    {
        $response = (new MpesaAccountBalance())
            ->setApplicationStatus(false)
            ->setInitiatorName(env('INITIATOR_NAME'))
            ->setSecurityCredential(env('SECURITY_CREDENTIAL'))
            ->setConsumerKey(env('CONSUMER_KEY'))
            ->setConsumerSecret(env('CONSUMER_SECRET'))
            ->setCommandId("AccountBalance")
            ->setPartyA(env('PARTY_A'))
            ->setIdentifierType(4)
            ->setRemarks("Understanding Account Balance")
            ->setQueueTimeOutUrl(env('QUEUE_TIMEOUT_URL'))
            ->setResultUrl(env('RESULT_URL'))
            ->makeAccountBalanceCall();

        return $response;
    }

    /**
     * Make an account balance query to the Mpesa API
     *
     * This method sends a request to the Mpesa API to query the account balance
     * for the specified party.
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
     * - CommandID: Usually "AccountBalance"
     * - InitiatorName: The name of the initiator
     * - SecurityCredential: The security credential
     * - PartyA: The party whose balance is being queried
     * - IdentifierType: The type of identifier (usually 4 for organization shortcode)
     * - Remarks: Comments about the transaction
     * - QueueTimeOutURL: The URL to receive timeout notifications
     * - ResultURL: The URL to receive the result
     *
     * @return string JSON response from the API
     * @throws InvalidArgumentException If required properties are not set
     * @throws RuntimeException If there's an error in the API request
     */
    public function makeAccountBalanceCall(): string
    {
        // Validate required properties
        $requiredProperties = [
            'ApplicationStatus', 'ConsumerKey', 'ConsumerSecret', 'CommandID',
            'InitiatorName', 'SecurityCredential', 'PartyA', 'IdentifierType',
            'Remarks', 'QueueTimeOutURL', 'ResultURL'
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
            ? 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query'
            : 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query';

        try {
            // Generate access token
            $token = $this->generateAccessToken($this->ApplicationStatus, $this->ConsumerKey, $this->ConsumerSecret);

            // Prepare request data
            $requestData = [
                'CommandID' => $this->CommandID,
                'Initiator' => $this->InitiatorName,
                'SecurityCredential' => $this->SecurityCredential,
                'PartyA' => $this->PartyA,
                'IdentifierType' => $this->IdentifierType,
                'Remarks' => $this->Remarks,
                'QueueTimeOutURL' => $this->QueueTimeOutURL,
                'ResultURL' => $this->ResultURL
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
