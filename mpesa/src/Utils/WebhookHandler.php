<?php
/**
 * WebhookHandler
 *
 * Handles webhook callbacks from Mpesa API.
 *
 * @package Rndwiga\Mpesa\Utils
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Utils;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class WebhookHandler
{
    use LoggerTrait;
    
    /**
     * The raw callback data
     *
     * @var string|null
     */
    protected $rawData;
    
    /**
     * The parsed callback data
     *
     * @var array|null
     */
    protected $parsedData;
    
    /**
     * Constructor
     *
     * @param LoggerInterface|null $logger The logger instance
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->setLogger($logger);
    }
    
    /**
     * Get the raw callback data from the request
     *
     * @param string|null $input Optional raw input data (if not provided, will use php://input)
     * @return $this
     */
    public function captureCallback(?string $input = null): self
    {
        $this->rawData = $input ?? file_get_contents('php://input');
        $this->logDebug('Captured webhook callback', ['rawData' => $this->rawData]);
        
        return $this;
    }
    
    /**
     * Parse the callback data
     *
     * @return $this
     * @throws InvalidArgumentException If the callback data is invalid
     */
    public function parseCallback(): self
    {
        if (empty($this->rawData)) {
            $this->logError('No callback data to parse');
            throw new InvalidArgumentException('No callback data to parse');
        }
        
        $data = json_decode($this->rawData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logError('Invalid JSON in callback data', ['error' => json_last_error_msg()]);
            throw new InvalidArgumentException('Invalid JSON in callback data: ' . json_last_error_msg());
        }
        
        $this->parsedData = $data;
        $this->logInfo('Parsed webhook callback data', ['parsedData' => $this->parsedData]);
        
        return $this;
    }
    
    /**
     * Get the parsed callback data
     *
     * @return array The parsed callback data
     * @throws InvalidArgumentException If the callback data has not been parsed
     */
    public function getData(): array
    {
        if ($this->parsedData === null) {
            $this->logError('Callback data has not been parsed');
            throw new InvalidArgumentException('Callback data has not been parsed. Call parseCallback() first.');
        }
        
        return $this->parsedData;
    }
    
    /**
     * Get a specific value from the callback data
     *
     * @param string $key The key to get
     * @param mixed $default The default value to return if the key doesn't exist
     * @return mixed The value or default
     * @throws InvalidArgumentException If the callback data has not been parsed
     */
    public function getValue(string $key, $default = null)
    {
        $data = $this->getData();
        
        // Handle nested keys with dot notation (e.g., 'Body.stkCallback.CallbackMetadata')
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $data;
            
            foreach ($keys as $nestedKey) {
                if (!isset($value[$nestedKey])) {
                    return $default;
                }
                $value = $value[$nestedKey];
            }
            
            return $value;
        }
        
        return $data[$key] ?? $default;
    }
    
    /**
     * Check if the callback is for a specific type
     *
     * @param string $type The callback type to check for
     * @return bool True if the callback is of the specified type
     */
    public function isCallbackType(string $type): bool
    {
        try {
            $data = $this->getData();
            
            // Different callback types have different structures
            switch ($type) {
                case 'c2b':
                    return isset($data['TransactionType']) && in_array($data['TransactionType'], ['Pay Bill', 'Buy Goods']);
                
                case 'b2c':
                    return isset($data['Result']['ResultParameters']['ResultParameter']) && 
                           $this->findParameterByKey($data['Result']['ResultParameters']['ResultParameter'], 'TransactionReceipt');
                
                case 'b2b':
                    return isset($data['Result']['ResultParameters']['ResultParameter']) && 
                           $this->findParameterByKey($data['Result']['ResultParameters']['ResultParameter'], 'TransactionID');
                
                case 'express':
                    return isset($data['Body']['stkCallback']);
                
                case 'balance':
                    return isset($data['Result']['ResultParameters']['ResultParameter']) && 
                           $this->findParameterByKey($data['Result']['ResultParameters']['ResultParameter'], 'AccountBalance');
                
                case 'status':
                    return isset($data['Result']['ResultParameters']['ResultParameter']) && 
                           $this->findParameterByKey($data['Result']['ResultParameters']['ResultParameter'], 'OriginatorConversationID');
                
                case 'reversal':
                    return isset($data['Result']['ResultParameters']['ResultParameter']) && 
                           $this->findParameterByKey($data['Result']['ResultParameters']['ResultParameter'], 'DebitAccountBalance');
                
                default:
                    return false;
            }
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
    
    /**
     * Find a parameter by key in a list of parameters
     *
     * @param array $parameters The parameters to search
     * @param string $key The key to find
     * @return bool True if the key is found
     */
    protected function findParameterByKey(array $parameters, string $key): bool
    {
        foreach ($parameters as $param) {
            if (isset($param['Key']) && $param['Key'] === $key) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate a success response for Mpesa
     *
     * @param string $message The response message
     * @return string JSON response
     */
    public function generateSuccessResponse(string $message = "Confirmation Service request accepted successfully"): string
    {
        $response = [
            "ResultDesc" => $message,
            "ResultCode" => "0"
        ];
        
        $this->logInfo('Generated success response', $response);
        return json_encode($response);
    }
    
    /**
     * Generate an error response for Mpesa
     *
     * @param string $message The error message
     * @param string $code The error code
     * @return string JSON response
     */
    public function generateErrorResponse(string $message = "Confirmation Service request failed", string $code = "1"): string
    {
        $response = [
            "ResultDesc" => $message,
            "ResultCode" => $code
        ];
        
        $this->logWarning('Generated error response', $response);
        return json_encode($response);
    }
}