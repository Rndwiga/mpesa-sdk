<?php
/**
 * Account
 *
 * Handles account-related API operations (balance inquiry, transaction status, reversal).
 *
 * @package Rndwiga\Mpesa\Api
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Api;

use InvalidArgumentException;
use Rndwiga\Mpesa\Client\MpesaClient;

class Account extends BaseApi
{
    /**
     * Command ID options
     */
    public const COMMAND_ID_ACCOUNT_BALANCE = 'AccountBalance';
    public const COMMAND_ID_TRANSACTION_STATUS = 'TransactionStatusQuery';
    public const COMMAND_ID_REVERSAL = 'TransactionReversal';

    /**
     * Identifier types
     */
    public const IDENTIFIER_TYPE_MSISDN = 1;
    public const IDENTIFIER_TYPE_TILL_NUMBER = 2;
    public const IDENTIFIER_TYPE_SHORTCODE = 4;

    /**
     * Initiator name (the username of the M-Pesa account API operator)
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
     * Party A (the organization's shortcode)
     *
     * @var string
     */
    protected $partyA;

    /**
     * Identifier type
     *
     * @var int
     */
    protected $identifierType;

    /**
     * Receiver party (for reversal)
     *
     * @var string
     */
    protected $receiverParty;

    /**
     * Receiver identifier type (for reversal)
     *
     * @var int
     */
    protected $receiverIdentifierType;

    /**
     * Transaction ID (for transaction status and reversal)
     *
     * @var string
     */
    protected $transactionId;

    /**
     * Remarks
     *
     * @var string
     */
    protected $remarks;

    /**
     * Queue timeout URL
     *
     * @var string
     */
    protected $queueTimeoutUrl;

    /**
     * Result URL
     *
     * @var string
     */
    protected $resultUrl;

    /**
     * Occasion (for reversal)
     *
     * @var string
     */
    protected $occasion;

    /**
     * Amount (for reversal)
     *
     * @var int
     */
    protected $amount;

    /**
     * Get the API endpoint based on the command ID
     *
     * @return string The API endpoint
     */
    protected function getEndpoint(): string
    {
        switch ($this->commandId) {
            case self::COMMAND_ID_ACCOUNT_BALANCE:
                return self::ACCOUNT_BALANCE_ENDPOINT;
            case self::COMMAND_ID_TRANSACTION_STATUS:
                return self::TRANSACTION_STATUS_ENDPOINT;
            case self::COMMAND_ID_REVERSAL:
                return self::REVERSAL_ENDPOINT;
            default:
                throw new InvalidArgumentException("Invalid command ID: {$this->commandId}");
        }
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
    public function setCommandId(string $commandId): self
    {
        $validCommandIds = [
            self::COMMAND_ID_ACCOUNT_BALANCE,
            self::COMMAND_ID_TRANSACTION_STATUS,
            self::COMMAND_ID_REVERSAL
        ];

        if (!in_array($commandId, $validCommandIds)) {
            throw new InvalidArgumentException("Invalid command ID: {$commandId}");
        }

        $this->commandId = $commandId;
        return $this;
    }

    /**
     * Set the party A
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
     * Set the identifier type
     *
     * @param int $identifierType The identifier type
     * @return $this
     */
    public function setIdentifierType(int $identifierType): self
    {
        $validIdentifierTypes = [
            self::IDENTIFIER_TYPE_MSISDN,
            self::IDENTIFIER_TYPE_TILL_NUMBER,
            self::IDENTIFIER_TYPE_SHORTCODE
        ];

        if (!in_array($identifierType, $validIdentifierTypes)) {
            throw new InvalidArgumentException("Invalid identifier type: {$identifierType}");
        }

        $this->identifierType = $identifierType;
        return $this;
    }

    /**
     * Set the receiver party (for reversal)
     *
     * @param string $receiverParty The receiver party
     * @return $this
     */
    public function setReceiverParty(string $receiverParty): self
    {
        $this->receiverParty = $receiverParty;
        return $this;
    }

    /**
     * Set the receiver identifier type (for reversal)
     *
     * @param int $receiverIdentifierType The receiver identifier type
     * @return $this
     */
    public function setReceiverIdentifierType(int $receiverIdentifierType = 11): self
    {
        $this->receiverIdentifierType = $receiverIdentifierType;
        return $this;
    }

    /**
     * Set the transaction ID (for transaction status and reversal)
     *
     * @param string $transactionId The transaction ID
     * @return $this
     */
    public function setTransactionId(string $transactionId): self
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * Set the remarks
     *
     * @param string $remarks The remarks
     * @return $this
     */
    public function setRemarks(string $remarks): self
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
     * Set the occasion (for reversal)
     *
     * @param string $occasion The occasion
     * @return $this
     */
    public function setOccasion(string $occasion): self
    {
        $this->occasion = $occasion;
        return $this;
    }

    /**
     * Set the amount (for reversal)
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
     * Check account balance
     *
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return mixed The API response
     */
    public function checkBalance(bool $verifySSL = true)
    {
        $this->setCommandId(self::COMMAND_ID_ACCOUNT_BALANCE);

        // Validate required fields
        if (!isset($this->initiatorName) || !isset($this->securityCredential) || 
            !isset($this->partyA) || !isset($this->identifierType) || 
            !isset($this->queueTimeoutUrl) || !isset($this->resultUrl) || 
            !isset($this->remarks)) {
            throw new InvalidArgumentException("Missing required parameters for account balance check");
        }

        // Prepare request data
        $requestData = [
            'Initiator' => $this->initiatorName,
            'SecurityCredential' => $this->securityCredential,
            'CommandID' => $this->commandId,
            'PartyA' => $this->partyA,
            'IdentifierType' => $this->identifierType,
            'QueueTimeOutURL' => $this->queueTimeoutUrl,
            'ResultURL' => $this->resultUrl,
            'Remarks' => $this->remarks
        ];

        return $this->sendRequest($requestData, $verifySSL);
    }

    /**
     * Check transaction status
     *
     * @param string|null $transactionId The transaction ID (optional if already set)
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return mixed The API response
     */
    public function checkTransactionStatus(string $transactionId = null, bool $verifySSL = true)
    {
        $this->setCommandId(self::COMMAND_ID_TRANSACTION_STATUS);

        // Set the transaction ID if it's provided as a parameter
        if ($transactionId !== null) {
            $this->setTransactionId($transactionId);
        }

        // Validate required fields
        if (!isset($this->initiatorName) || !isset($this->securityCredential) || 
            !isset($this->transactionId) || !isset($this->partyA) || 
            !isset($this->identifierType) || !isset($this->queueTimeoutUrl) || 
            !isset($this->resultUrl) || !isset($this->remarks)) {
            throw new InvalidArgumentException("Missing required parameters for transaction status check");
        }

        // Prepare request data
        $requestData = [
            'Initiator' => $this->initiatorName,
            'SecurityCredential' => $this->securityCredential,
            'CommandID' => $this->commandId,
            'TransactionID' => $this->transactionId,
            'PartyA' => $this->partyA,
            'IdentifierType' => $this->identifierType,
            'QueueTimeOutURL' => $this->queueTimeoutUrl,
            'ResultURL' => $this->resultUrl,
            'Remarks' => $this->remarks,
            'Occasion' => $this->occasion ?? ''
        ];

        return $this->sendRequest($requestData, $verifySSL);
    }

    /**
     * Reverse a transaction
     *
     * @param string|null $transactionId The transaction ID (optional if already set)
     * @param int|null $amount The amount (optional if already set)
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return mixed The API response
     */
    public function reverseTransaction(string $transactionId = null, int $amount = null, bool $verifySSL = true)
    {
        $this->setCommandId(self::COMMAND_ID_REVERSAL);

        // Set the transaction ID if it's provided as a parameter
        if ($transactionId !== null) {
            $this->setTransactionId($transactionId);
        }

        // Set the amount if it's provided as a parameter
        if ($amount !== null) {
            $this->setAmount($amount);
        }

        // Validate required fields
        if (!isset($this->initiatorName) || !isset($this->securityCredential) || 
            !isset($this->transactionId) || !isset($this->amount) || 
            !isset($this->receiverParty) || !isset($this->receiverIdentifierType) || 
            !isset($this->queueTimeoutUrl) || !isset($this->resultUrl) || 
            !isset($this->remarks)) {
            throw new InvalidArgumentException("Missing required parameters for transaction reversal");
        }

        // Prepare request data
        $requestData = [
            'Initiator' => $this->initiatorName,
            'SecurityCredential' => $this->securityCredential,
            'CommandID' => $this->commandId,
            'TransactionID' => $this->transactionId,
            'Amount' => $this->amount,
            'ReceiverParty' => $this->receiverParty,
            'RecieverIdentifierType' => $this->receiverIdentifierType,
            'QueueTimeOutURL' => $this->queueTimeoutUrl,
            'ResultURL' => $this->resultUrl,
            'Remarks' => $this->remarks,
            'Occasion' => $this->occasion ?? ''
        ];

        return $this->sendRequest($requestData, $verifySSL);
    }

    /**
     * Process a callback
     *
     * @param array $callbackData The callback data from Mpesa
     * @return array The processed callback data
     */
    public function processCallback(array $callbackData): array
    {
        // Extract the relevant data from the callback
        $result = $callbackData['Result'] ?? [];
        
        $resultType = $result['ResultType'] ?? null;
        $resultCode = $result['ResultCode'] ?? null;
        $resultDesc = $result['ResultDesc'] ?? null;
        $originatorConversationID = $result['OriginatorConversationID'] ?? null;
        $conversationID = $result['ConversationID'] ?? null;
        $transactionID = $result['TransactionID'] ?? null;
        
        // Process based on result type
        switch ($resultType) {
            case 'AccountBalance':
                $resultParameters = $result['ResultParameters']['ResultParameter'] ?? [];
                $balances = [];
                
                foreach ($resultParameters as $param) {
                    if ($param['Key'] === 'AccountBalance') {
                        $balanceInfo = $param['Value'];
                        // Parse the balance info string
                        preg_match_all('/([A-Za-z]+)\s+balance\s+is\s+([0-9.]+)/', $balanceInfo, $matches);
                        
                        for ($i = 0; $i < count($matches[0]); $i++) {
                            $currency = $matches[1][$i];
                            $amount = $matches[2][$i];
                            $balances[$currency] = $amount;
                        }
                    }
                }
                
                return [
                    'success' => $resultCode === 0,
                    'type' => 'AccountBalance',
                    'data' => [
                        'resultCode' => $resultCode,
                        'resultDesc' => $resultDesc,
                        'originatorConversationID' => $originatorConversationID,
                        'conversationID' => $conversationID,
                        'transactionID' => $transactionID,
                        'balances' => $balances
                    ]
                ];
                
            case 'TransactionStatus':
                return [
                    'success' => $resultCode === 0,
                    'type' => 'TransactionStatus',
                    'data' => [
                        'resultCode' => $resultCode,
                        'resultDesc' => $resultDesc,
                        'originatorConversationID' => $originatorConversationID,
                        'conversationID' => $conversationID,
                        'transactionID' => $transactionID,
                        // Add other transaction status specific fields here
                    ]
                ];
                
            case 'Reversal':
                return [
                    'success' => $resultCode === 0,
                    'type' => 'Reversal',
                    'data' => [
                        'resultCode' => $resultCode,
                        'resultDesc' => $resultDesc,
                        'originatorConversationID' => $originatorConversationID,
                        'conversationID' => $conversationID,
                        'transactionID' => $transactionID,
                        // Add other reversal specific fields here
                    ]
                ];
                
            default:
                return [
                    'success' => false,
                    'type' => 'Unknown',
                    'data' => $result
                ];
        }
    }
}