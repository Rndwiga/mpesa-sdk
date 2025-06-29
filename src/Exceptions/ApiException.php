<?php
/**
 * ApiException
 *
 * Exception thrown when there is an error from the Mpesa API itself.
 *
 * @package Rndwiga\Mpesa\Exceptions
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Exceptions;

class ApiException extends MpesaException
{
    /**
     * The API error code
     *
     * @var string|null
     */
    protected $apiErrorCode;
    
    /**
     * The request ID from the API
     *
     * @var string|null
     */
    protected $requestId;
    
    /**
     * Constructor
     *
     * @param string $message The error message
     * @param int $code The HTTP status code
     * @param string|null $apiErrorCode The API-specific error code
     * @param string|null $requestId The request ID from the API
     * @param \Throwable|null $previous The previous exception
     * @param array|null $errorData Additional error data
     */
    public function __construct(
        string $message = "API request failed", 
        int $code = 500, 
        ?string $apiErrorCode = null,
        ?string $requestId = null,
        \Throwable $previous = null, 
        ?array $errorData = null
    ) {
        parent::__construct($message, $code, $previous, $errorData);
        $this->apiErrorCode = $apiErrorCode;
        $this->requestId = $requestId;
    }
    
    /**
     * Get the API error code
     *
     * @return string|null The API error code
     */
    public function getApiErrorCode(): ?string
    {
        return $this->apiErrorCode;
    }
    
    /**
     * Get the request ID
     *
     * @return string|null The request ID
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}