<?php
/**
 * ValidationException
 *
 * Exception thrown when there is a validation error with the Mpesa API request parameters.
 *
 * @package Rndwiga\Mpesa\Exceptions
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Exceptions;

class ValidationException extends MpesaException
{
    /**
     * Constructor
     *
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     * @param array|null $errorData Additional error data
     */
    public function __construct(string $message = "Validation failed", int $code = 400, \Throwable $previous = null, ?array $errorData = null)
    {
        parent::__construct($message, $code, $previous, $errorData);
    }
}