<?php
/**
 * AuthenticationException
 *
 * Exception thrown when there is an authentication error with the Mpesa API.
 *
 * @package Rndwiga\Mpesa\Exceptions
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Exceptions;

class AuthenticationException extends MpesaException
{
    /**
     * Constructor
     *
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     * @param array|null $errorData Additional error data
     */
    public function __construct(string $message = "Authentication failed", int $code = 401, \Throwable $previous = null, ?array $errorData = null)
    {
        parent::__construct($message, $code, $previous, $errorData);
    }
}