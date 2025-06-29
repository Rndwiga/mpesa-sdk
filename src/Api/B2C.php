<?php
/**
 * B2C
 *
 * Handles Business to Customer (B2C) API operations.
 *
 * @package Rndwiga\Mpesa\Api
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Api;

use InvalidArgumentException;
use Rndwiga\Mpesa\Client\MpesaClient;

class B2C extends BaseApi
{
    /**
     * Command ID options for B2C
     */
    public const COMMAND_ID_SALARY_PAYMENT = 'SalaryPayment';
    public const COMMAND_ID_BUSINESS_PAYMENT = 'BusinessPayment';
    public const COMMAND_ID_PROMOTION_PAYMENT = 'PromotionPayment';

    /**
     * Initiator name (the username of the M-Pesa B2C account API operator)
     *
     * @var string
     */
    protected $initiatorName;

    /**
     * Security credential (encrypted password)
     *
     * @var string
     */
    protected $securityCredential;

    /**
     * Command ID (type of transaction)
     *
     * @var string
     */
    protected $commandId;

    /**
     * Amount to be sent
     *
     * @var int
     */
    protected $amount;

    /**
     * Organization shortcode sending the transaction
     *
     * @var string
     */
    protected $partyA;

    /**
     * Phone number receiving the transaction
     *
     * @var string
     */
    protected $partyB;

    /**
     * Comments for the transaction
     *
     * @var string
     */
    protected $remarks;

    /**
     * Timeout URL for the transaction
     *
     * @var string
     */
    protected $queueTimeoutUrl;

    /**
     * Result URL for the transaction
     *
     * @var string
     */
    protected $resultUrl;

    /**
     * Additional information about the transaction
     *
     * @var string
     */
    protected $occasion;

    /**
     * Get the API endpoint for B2C operations
     *
     * @return string The API endpoint
     */
    protected function getEndpoint(): string
    {
        return self::B2C_ENDPOINT;
    }

    /**
     * Set the initiator name
     *
     * @param string $initiatorName The initiator name
     * @return $this
     */
    public function setInitiatorName(string $initiatorName): self
    {
        $this->initiatorName = $initiatorName;
        return $this;
    }

    /**
     * Set the security credential
     *
     * @param string $initiatorPassword The initiator password
     * @return $this
     */
    public function setSecurityCredential(string $initiatorPassword): self
    {
        $this->securityCredential = $this->generateSecurityCredential($initiatorPassword);
        return $this;
    }

    /**
     * Set the command ID
     *
     * @param string $commandId The command ID
     * @return $this
     */
    public function setCommandId(string $commandId = self::COMMAND_ID_BUSINESS_PAYMENT): self
    {
        $validCommandIds = [
            self::COMMAND_ID_SALARY_PAYMENT,
            self::COMMAND_ID_BUSINESS_PAYMENT,
            self::COMMAND_ID_PROMOTION_PAYMENT
        ];

        if (!in_array($commandId, $validCommandIds)) {
            throw new InvalidArgumentException("Invalid command ID for B2C: {$commandId}");
        }

        $this->commandId = $commandId;
        return $this;
    }

    /**
     * Set the amount
     *
     * @param int $amount The amount
     * @return $this
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $this->validateAmount($amount);
        return $this;
    }

    /**
     * Set the party A (organization shortcode)
     *
     * @param string $partyA The party A
     * @return $this
     */
    public function setPartyA(string $partyA): self
    {
        $this->partyA = $partyA;
        return $this;
    }

    /**
     * Set the party B (phone number)
     *
     * @param string|int $partyB The party B
     * @return $this
     */
    public function setPartyB($partyB): self
    {
        $this->partyB = $this->validatePhoneNumber($partyB);
        return $this;
    }

    /**
     * Set the remarks
     *
     * @param string $remarks The remarks
     * @return $this
     */
    public function setRemarks(string $remarks = "Business Payment To Client"): self
    {
        $this->remarks = $remarks;
        return $this;
    }

    /**
     * Set the queue timeout URL
     *
     * @param string $queueTimeoutUrl The queue timeout URL
     * @return $this
     */
    public function setQueueTimeoutUrl(string $queueTimeoutUrl): self
    {
        $this->queueTimeoutUrl = $queueTimeoutUrl;
        return $this;
    }

    /**
     * Set the result URL
     *
     * @param string $resultUrl The result URL
     * @return $this
     */
    public function setResultUrl(string $resultUrl): self
    {
        $this->resultUrl = $resultUrl;
        return $this;
    }

    /**
     * Set the occasion
     *
     * @param string $occasion The occasion
     * @return $this
     */
    public function setOccasion(string $occasion = "Business Payment To Client"): self
    {
        $this->occasion = $occasion;
        return $this;
    }

    /**
     * Make a B2C payment
     *
     * @param int|null $amount The amount to transfer (optional if already set)
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return mixed The API response
     */
    public function makePayment($amount = null, bool $verifySSL = true)
    {
        // Set the amount if it's provided as a parameter
        if ($amount !== null) {
            $this->setAmount($amount);
        }

        // Validate required fields
        if (!isset($this->initiatorName) || !isset($this->securityCredential) || 
            !isset($this->commandId) || !isset($this->amount) || 
            !isset($this->partyA) || !isset($this->partyB) || 
            !isset($this->queueTimeoutUrl) || !isset($this->resultUrl)) {
            throw new InvalidArgumentException("Missing required parameters for B2C payment");
        }

        // Prepare request data
        $requestData = [
            'InitiatorName' => $this->initiatorName,
            'SecurityCredential' => $this->securityCredential,
            'CommandID' => $this->commandId,
            'Amount' => $this->amount,
            'PartyA' => $this->partyA,
            'PartyB' => $this->partyB,
            'Remarks' => $this->remarks,
            'QueueTimeOutURL' => $this->queueTimeoutUrl,
            'ResultURL' => $this->resultUrl,
            'Occasion' => $this->occasion
        ];

        return $this->sendRequest($requestData, $verifySSL);
    }

    /**
     * Make a B2C payment with an originator conversation ID
     *
     * @param string $originatorConversationID The originator conversation ID
     * @param int|null $amount The amount to transfer (optional if already set)
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return mixed The API response
     */
    public function makePaymentWithOriginatorID(string $originatorConversationID, $amount = null, bool $verifySSL = true)
    {
        // Set the amount if it's provided as a parameter
        if ($amount !== null) {
            $this->setAmount($amount);
        }

        // Validate required fields
        if (!isset($this->initiatorName) || !isset($this->securityCredential) || 
            !isset($this->commandId) || !isset($this->amount) || 
            !isset($this->partyA) || !isset($this->partyB) || 
            !isset($this->queueTimeoutUrl) || !isset($this->resultUrl)) {
            throw new InvalidArgumentException("Missing required parameters for B2C payment");
        }

        // Prepare request data
        $requestData = [
            'OriginatorConversationID' => $originatorConversationID,
            'InitiatorName' => $this->initiatorName,
            'SecurityCredential' => $this->securityCredential,
            'CommandID' => $this->commandId,
            'Amount' => $this->amount,
            'PartyA' => $this->partyA,
            'PartyB' => $this->partyB,
            'Remarks' => $this->remarks,
            'QueueTimeOutURL' => $this->queueTimeoutUrl,
            'ResultURL' => $this->resultUrl,
            'Occasion' => $this->occasion
        ];

        // Use the validate endpoint for this type of request
        return $this->client->post(self::B2C_VALIDATE_ENDPOINT, $requestData, $verifySSL);
    }
}