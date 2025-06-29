<?php
/**
 * MpesaAPI
 *
 * Main entry point for the Mpesa API SDK.
 *
 * @package Rndwiga\Mpesa
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa;

use Psr\Log\LoggerInterface;
use Rndwiga\Mpesa\Api\Account;
use Rndwiga\Mpesa\Api\B2B;
use Rndwiga\Mpesa\Api\B2C;
use Rndwiga\Mpesa\Api\C2B;
use Rndwiga\Mpesa\Api\Express;
use Rndwiga\Mpesa\Client\MpesaClient;
use Rndwiga\Mpesa\Utils\CacheInterface;
use Rndwiga\Mpesa\Utils\FileCache;
use Rndwiga\Mpesa\Utils\LoggerTrait;
use Rndwiga\Mpesa\Utils\WebhookHandler;

class MpesaAPI
{
    use LoggerTrait;
    /**
     * The Mpesa client
     *
     * @var MpesaClient
     */
    private $client;

    /**
     * The Account API handler
     *
     * @var Account|null
     */
    private $account;

    /**
     * The B2B API handler
     *
     * @var B2B|null
     */
    private $b2b;

    /**
     * The B2C API handler
     *
     * @var B2C|null
     */
    private $b2c;

    /**
     * The C2B API handler
     *
     * @var C2B|null
     */
    private $c2b;

    /**
     * The Express API handler
     *
     * @var Express|null
     */
    private $express;

    /**
     * Constructor
     *
     * @param string $consumerKey The consumer key
     * @param string $consumerSecret The consumer secret
     * @param bool $isLive Whether to use the live environment
     * @param LoggerInterface|null $logger The logger instance
     * @param CacheInterface|null $cache The cache instance
     */
    public function __construct(
        string $consumerKey, 
        string $consumerSecret, 
        bool $isLive = false, 
        ?LoggerInterface $logger = null,
        ?CacheInterface $cache = null
    ) {
        $this->setLogger($logger);
        $this->logInfo('Initializing MpesaAPI', [
            'environment' => $isLive ? 'production' : 'sandbox'
        ]);

        // Use provided cache or create a new FileCache
        $cache = $cache ?? new FileCache();

        $this->client = new MpesaClient($consumerKey, $consumerSecret, $isLive, $this->getLogger(), $cache);
    }

    /**
     * Get the Account API handler
     *
     * @return Account The Account API handler
     */
    public function account(): Account
    {
        if (!$this->account) {
            $this->account = new Account($this->client);
        }

        return $this->account;
    }

    /**
     * Get the B2B API handler
     *
     * @return B2B The B2B API handler
     */
    public function b2b(): B2B
    {
        if (!$this->b2b) {
            $this->b2b = new B2B($this->client);
        }

        return $this->b2b;
    }

    /**
     * Get the B2C API handler
     *
     * @return B2C The B2C API handler
     */
    public function b2c(): B2C
    {
        if (!$this->b2c) {
            $this->b2c = new B2C($this->client);
        }

        return $this->b2c;
    }

    /**
     * Get the C2B API handler
     *
     * @return C2B The C2B API handler
     */
    public function c2b(): C2B
    {
        if (!$this->c2b) {
            $this->c2b = new C2B($this->client);
        }

        return $this->c2b;
    }

    /**
     * Get the Express API handler
     *
     * @return Express The Express API handler
     */
    public function express(): Express
    {
        if (!$this->express) {
            $this->express = new Express($this->client);
        }

        return $this->express;
    }

    /**
     * Get the Mpesa client
     *
     * @return MpesaClient The Mpesa client
     */
    public function getClient(): MpesaClient
    {
        return $this->client;
    }

    /**
     * Finish a transaction (send a success response to Mpesa)
     *
     * @param string $message Optional custom message
     * @return string JSON response
     */
    public function finishTransaction(string $message = "Confirmation Service request accepted successfully"): string
    {
        return $this->client->finishTransaction($message);
    }

    /**
     * Create a webhook handler for processing callbacks
     *
     * @param string|null $callbackData Optional raw callback data
     * @return WebhookHandler The webhook handler
     */
    public function webhook(?string $callbackData = null): WebhookHandler
    {
        $this->logInfo('Creating webhook handler');
        $handler = new WebhookHandler($this->getLogger());

        if ($callbackData !== null) {
            $handler->captureCallback($callbackData)->parseCallback();
        }

        return $handler;
    }
}
