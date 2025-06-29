<?php
/**
 * B2B
 *
 * Handles Business to Business (B2B) API operations.
 *
 * @package Rndwiga\Mpesa\Api
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Api;

use InvalidArgumentException;
use Rndwiga\Mpesa\Client\MpesaClient;

class B2B extends BaseApi
{
    /**
     * Command ID options for B2B
     */
    public const COMMAND_ID_BUSINESS_PAY_BILL = 'BusinessPayBill';
    public const COMMAND_ID_MERCHANT_TO_MERCHANT = 'MerchantToMerchantTransfer';
    public const COMMAND_ID_MERCHANT_TO_WORKING = 'MerchantTransferFromMerchantToWorking';
    public const COMMAND_ID_MERCHANT_SERVICES = 'MerchantServicesMMFAccountTransfer';
    public const COMMAND_ID_AGENCY_FLOAT = 'AgencyFloatAdvance';

    /**
     * Sender identifier types
     */
    public const SENDER_IDENTIFIER_MSISDN = 1;
    public const SENDER_IDENTIFIER_TILL_NUMBER = 2;
    public const SENDER_IDENTIFIER_SHORTCODE = 4;

    /**
     * Receiver identifier types
     */
    public const RECEIVER_IDENTIFIER_MSISDN = 1;
    public const RECEIVER_IDENTIFIER_TILL_NUMBER = 2;
    public const RECEIVER_IDENTIFIER_SHORTCODE = 4;
    public const RECEIVER_IDENTIFIER_ORGANIZATION = 11;

    /**
     * Initiator name (the username of the M-Pesa B2B account API operator)
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
     * Sender identifier type
     *
     * @var int
     */
    protected $senderIdentifierType;

    /**
     * Receiver identifier type
     *
     * @var int
     */
    protected $receiverIdentifierType;

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
     * Organization receiving the transaction
     *
     * @var string
     */
    protected $partyB;

    /**
     * Account reference
     *
     * @var string
     */
    protected $accountReference;

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
     * Get the API endpoint for B2B operations
     *
     * @return string The API endpoint
     */
    protected function getEndpoint(): string
    {
        return self::B2B_ENDPOINT;
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
    public function setCommandId(string $commandId = self::COMMAND_ID_BUSINESS_PAY_BILL): self
    {
        $validCommandIds = [
            self::COMMAND_ID_BUSINESS_PAY_BILL,
            self::COMMAND_ID_MERCHANT_TO_MERCHANT,
            self::COMMAND_ID_MERCHANT_TO_WORKING,
            self::COMMAND_ID_MERCHANT_SERVICES,
            self::COMMAND_ID_AGENCY_FLOAT
        ];

        if (!in_array($commandId, $validCommandIds)) {
            throw new InvalidArgumentException("Invalid command ID for B2B: {$commandId}");
        }

        $this->commandId = $commandId;
        return $this;
    }

    /**
     * Set the sender identifier type
     *
     * @param int $senderIdentifierType The sender identifier type
     * @return $this
     */
    public function setSenderIdentifierType(int $senderIdentifierType): self
    {
        $validIdentifierTypes = [
            self::SENDER_IDENTIFIER_MSISDN,
            self::SENDER_IDENTIFIER_TILL_NUMBER,
            self::SENDER_IDENTIFIER_SHORTCODE
        ];

        if (!in_array($senderIdentifierType, $validIdentifierTypes)) {
            throw new InvalidArgumentException("Invalid sender identifier type: {$senderIdentifierType}");
        }

        $this->senderIdentifierType = $senderIdentifierType;
        return $this;
    }

    /**
     * Set the receiver identifier type
     *
     * @param int $receiverIdentifierType The receiver identifier type
     * @return $this
     */
    public function setReceiverIdentifierType(int $receiverIdentifierType): self
    {
        $validIdentifierTypes = [
            self::RECEIVER_IDENTIFIER_MSISDN,
            self::RECEIVER_IDENTIFIER_TILL_NUMBER,
            self::RECEIVER_IDENTIFIER_SHORTCODE,
            self::RECEIVER_IDENTIFIER_ORGANIZATION
        ];

        if (!in_array($receiverIdentifierType, $validIdentifierTypes)) {
            throw new InvalidArgumentException("Invalid receiver identifier type: {$receiverIdentifierType}");
        }

        $this->receiverIdentifierType = $receiverIdentifierType;
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
     * Set the party A (organization shortcode sending the transaction)
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
     * Set the party B (organization receiving the transaction)
     *
     * @param string $partyB The party B
     * @return $this
     */
    public function setPartyB(string $partyB): self
    {
        $this->partyB = $partyB;
        return $this;
    }

    /**
     * Set the account reference
     *
     * @param string $accountReference The account reference
     * @return $this
     */
    public function setAccountReference(string $accountReference): self
    {
        $this->accountReference = $accountReference;
        return $this;
    }

    /**
     * Set the remarks
     *
     * @param string $remarks The remarks
     * @return $this
     */
    public function setRemarks(string $remarks = "Business Payment"): self
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
     * Make a B2B payment
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
            !isset($this->commandId) || !isset($this->senderIdentifierType) || 
            !isset($this->receiverIdentifierType) || !isset($this->amount) || 
            !isset($this->partyA) || !isset($this->partyB) || 
            !isset($this->accountReference) || !isset($this->queueTimeoutUrl) || 
            !isset($this->resultUrl)) {
            throw new InvalidArgumentException("Missing required parameters for B2B payment");
        }

        // Prepare request data
        $requestData = [
            'Initiator' => $this->initiatorName,
            'SecurityCredential' => $this->securityCredential,
            'CommandID' => $this->commandId,
            'SenderIdentifierType' => $this->senderIdentifierType,
            'RecieverIdentifierType' => $this->receiverIdentifierType,
            'Amount' => $this->amount,
            'PartyA' => $this->partyA,
            'PartyB' => $this->partyB,
            'AccountReference' => $this->accountReference,
            'Remarks' => $this->remarks,
            'QueueTimeOutURL' => $this->queueTimeoutUrl,
            'ResultURL' => $this->resultUrl
        ];

        return $this->sendRequest($requestData, $verifySSL);
    }
}