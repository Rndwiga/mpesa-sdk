<?php
/**
 * MpesaException
 *
 * Base exception class for Mpesa API errors.
 *
 * @package Rndwiga\Mpesa\Exceptions
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Exceptions;

use Exception;

class MpesaException extends Exception
{
    /**
     * The error data
     *
     * @var array|null
     */
    protected $errorData;
    
    /**
     * Constructor
     *
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     * @param array|null $errorData Additional error data
     */
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null, ?array $errorData = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorData = $errorData;
    }
    
    /**
     * Get the error data
     *
     * @return array|null The error data
     */
    public function getErrorData(): ?array
    {
        return $this->errorData;
    }
}