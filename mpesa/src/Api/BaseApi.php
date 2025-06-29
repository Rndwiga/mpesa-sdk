<?php
/**
 * BaseApi
 *
 * Base class for all Mpesa API handlers.
 *
 * @package Rndwiga\Mpesa\Api
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Api;

use Rndwiga\Mpesa\Client\MpesaClient;
use Rndwiga\Mpesa\Utils\MpesaUtils;

abstract class BaseApi implements ApiInterface
{
    /**
     * The Mpesa client
     *
     * @var MpesaClient
     */
    protected $client;

    /**
     * API endpoints
     */
    protected const B2C_ENDPOINT = '/mpesa/b2c/v1/paymentrequest';
    protected const B2C_VALIDATE_ENDPOINT = '/mpesa/b2c-validate-id/v1.0.1/paymentrequest';
    protected const B2B_ENDPOINT = '/mpesa/b2b/v1/paymentrequest';
    protected const ACCOUNT_BALANCE_ENDPOINT = '/mpesa/accountbalance/v1/query';
    protected const TRANSACTION_STATUS_ENDPOINT = '/mpesa/transactionstatus/v1/query';
    protected const REVERSAL_ENDPOINT = '/mpesa/reversal/v1/request';
    protected const C2B_REGISTER_URL_ENDPOINT = '/mpesa/c2b/v1/registerurl';
    protected const C2B_SIMULATE_ENDPOINT = '/mpesa/c2b/v1/simulate';
    protected const EXPRESS_ENDPOINT = '/mpesa/stkpush/v1/processrequest';
    protected const EXPRESS_QUERY_ENDPOINT = '/mpesa/stkpushquery/v1/query';

    /**
     * Constructor
     *
     * @param MpesaClient $client The Mpesa client
     */
    public function __construct(MpesaClient $client)
    {
        $this->client = $client;
    }

    /**
     * Send a request to the Mpesa API
     *
     * @param array $params The parameters to send in the request
     * @param string $endpoint The API endpoint to call
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return mixed The response from the API
     */
    public function sendRequest(array $params, bool $verifySSL = true)
    {
        try {
            $response = $this->client->post($this->getEndpoint(), $params, $verifySSL);
            return MpesaUtils::processApiResponse($response);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errorMessage' => $e->getMessage()
            ];
        }
    }

    /**
     * Get the API endpoint for the specific API handler
     *
     * @return string The API endpoint
     */
    abstract protected function getEndpoint(): string;

    /**
     * Generate security credentials for API authentication
     *
     * @param string $initiatorPassword The initiator password
     * @return string The encrypted security credential
     */
    protected function generateSecurityCredential(string $initiatorPassword): string
    {
        return $this->client->generateSecurityCredentials($initiatorPassword);
    }

    /**
     * Validate a phone number
     *
     * @param string|int $phoneNumber The phone number to validate
     * @return string The validated phone number
     */
    protected function validatePhoneNumber($phoneNumber): string
    {
        return MpesaUtils::validatePhoneNumber($phoneNumber);
    }

    /**
     * Validate an amount
     *
     * @param int|float $amount The amount to validate
     * @return int The validated amount
     */
    protected function validateAmount($amount): int
    {
        return MpesaUtils::validateAmount($amount);
    }
}