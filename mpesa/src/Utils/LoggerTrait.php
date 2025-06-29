<?php
/**
 * LoggerTrait
 *
 * Provides logging functionality for Mpesa API classes.
 *
 * @package Rndwiga\Mpesa\Utils
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Utils;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait LoggerTrait
{
    /**
     * The logger instance
     *
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * Set the logger instance
     *
     * @param LoggerInterface|null $logger The logger instance
     * @return $this
     */
    public function setLogger(?LoggerInterface $logger = null): self
    {
        $this->logger = $logger ?? new NullLogger();
        return $this;
    }

    /**
     * Get the logger instance
     *
     * @return LoggerInterface The logger instance
     */
    public function getLogger(): LoggerInterface
    {
        if (!isset($this->logger)) {
            $this->setLogger();
        }

        return $this->logger;
    }

    /**
     * Log a debug message
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logDebug(string $message, array $context = []): void
    {
        $this->getLogger()->debug($message, $context);
    }

    /**
     * Log an info message
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $this->getLogger()->info($message, $context);
    }

    /**
     * Log a warning message
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logWarning(string $message, array $context = []): void
    {
        $this->getLogger()->warning($message, $context);
    }

    /**
     * Log an error message
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->getLogger()->error($message, $context);
    }
}
