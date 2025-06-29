# Mpesa SDK

An opinionated approach to Mpesa API implementation.

## Installation

You can install the package via composer:

```bash
composer require rndwiga/mpesa
```

## Requirements

- PHP 8.1 or higher
- phpseclib/phpseclib 2.0 or higher
- psr/log 3.0 or higher (for logging)
- OpenSSL and JSON PHP extensions

## Features

This package provides a fluent interface for interacting with all major M-Pesa API services:

### API Services

1. **B2C (Business to Customer)** - Send money from a business to customers
2. **B2B (Business to Business)** - Send money from one business to another
3. **C2B (Customer to Business)** - Receive money from customers
4. **Express (STK Push)** - Prompt customers to enter their M-Pesa PIN on their phones
5. **Account Balance** - Check account balance
6. **Transaction Status** - Check the status of a transaction
7. **Transaction Reversal** - Reverse a transaction

### Additional Features

1. **PSR-3 Logging** - Comprehensive logging of API requests and responses
2. **Token Caching** - Efficient caching of access tokens to reduce API calls
3. **Enhanced Webhook Handling** - Simplified processing of M-Pesa callbacks
4. **Custom Exceptions** - Detailed exception handling for better error management

## Usage

### Initialization

```php
use Rndwiga\Mpesa\MpesaAPI;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Rndwiga\Mpesa\Utils\FileCache;

// Create a logger (optional)
$logger = new Logger('mpesa');
$logger->pushHandler(new StreamHandler('path/to/your/mpesa.log', Logger::DEBUG));

// Create a custom cache (optional)
$cache = new FileCache('/path/to/custom/cache/directory');

// Initialize the Mpesa API with all options
$mpesa = new MpesaAPI(
    'your_consumer_key',
    'your_consumer_secret',
    false, // false for sandbox, true for production
    $logger, // optional PSR-3 logger
    $cache   // optional cache implementation
);

// Or use the minimal initialization
$mpesa = new MpesaAPI(
    'your_consumer_key',
    'your_consumer_secret',
    false // false for sandbox, true for production
);
```

### B2C Payment

```php
$response = $mpesa->b2c()
    ->setInitiatorName('your_initiator_name')
    ->setSecurityCredential('your_initiator_password')
    ->setCommandId(Rndwiga\Mpesa\Api\B2C::COMMAND_ID_BUSINESS_PAYMENT)
    ->setAmount(100) // Amount in KES
    ->setPartyA('your_shortcode')
    ->setPartyB('254722000000') // Customer phone number
    ->setRemarks('Test payment')
    ->setQueueTimeoutUrl('https://example.com/timeout')
    ->setResultUrl('https://example.com/result')
    ->setOccasion('Test occasion')
    ->makePayment();
```

### B2B Payment

```php
$response = $mpesa->b2b()
    ->setInitiatorName('your_initiator_name')
    ->setSecurityCredential('your_initiator_password')
    ->setCommandId(Rndwiga\Mpesa\Api\B2B::COMMAND_ID_BUSINESS_PAY_BILL)
    ->setSenderIdentifierType(Rndwiga\Mpesa\Api\B2B::SENDER_IDENTIFIER_SHORTCODE)
    ->setReceiverIdentifierType(Rndwiga\Mpesa\Api\B2B::RECEIVER_IDENTIFIER_SHORTCODE)
    ->setAmount(100) // Amount in KES
    ->setPartyA('your_shortcode')
    ->setPartyB('receiver_shortcode')
    ->setAccountReference('Test reference')
    ->setRemarks('Test payment')
    ->setQueueTimeoutUrl('https://example.com/timeout')
    ->setResultUrl('https://example.com/result')
    ->makePayment();
```

### C2B Registration and Simulation

```php
// Register URLs
$response = $mpesa->c2b()
    ->setShortcode('your_shortcode')
    ->setResponseType(Rndwiga\Mpesa\Api\C2B::RESPONSE_TYPE_COMPLETED)
    ->setConfirmationUrl('https://example.com/confirmation')
    ->setValidationUrl('https://example.com/validation')
    ->registerUrls();

// Simulate C2B transaction (only works in sandbox)
$response = $mpesa->c2b()
    ->setShortcode('your_shortcode')
    ->setCommandId(Rndwiga\Mpesa\Api\C2B::COMMAND_ID_CUSTOMER_PAYBILL_ONLINE)
    ->setAmount(100) // Amount in KES
    ->setMsisdn('254722000000') // Customer phone number
    ->setBillRefNumber('Test reference')
    ->simulate();
```

### STK Push (Express)

```php
// Initiate STK Push
$response = $mpesa->express()
    ->setBusinessShortCode('your_business_shortcode')
    ->setPasskey('your_passkey')
    ->setTimestamp() // Will use current time
    ->setPassword() // Will generate password from shortcode, passkey, and timestamp
    ->setTransactionType(Rndwiga\Mpesa\Api\Express::TRANSACTION_TYPE_CUSTOMER_PAYBILL_ONLINE)
    ->setAmount(1) // Amount in KES
    ->setPartyA('254722000000') // Customer phone number
    ->setPartyB('your_business_shortcode')
    ->setPhoneNumber('254722000000') // Customer phone number
    ->setCallbackUrl('https://example.com/callback')
    ->setAccountReference('Test Reference')
    ->setTransactionDesc('Test Payment')
    ->push();

// Query STK Push status
$response = $mpesa->express()
    ->setBusinessShortCode('your_business_shortcode')
    ->setPasskey('your_passkey')
    ->query('checkout_request_id');
```

### Account Balance

```php
$response = $mpesa->account()
    ->setInitiatorName('your_initiator_name')
    ->setSecurityCredential('your_initiator_password')
    ->setPartyA('your_shortcode')
    ->setIdentifierType(Rndwiga\Mpesa\Api\Account::IDENTIFIER_TYPE_SHORTCODE)
    ->setRemarks('Test balance check')
    ->setQueueTimeoutUrl('https://example.com/timeout')
    ->setResultUrl('https://example.com/result')
    ->checkBalance();
```

### Transaction Status

```php
$response = $mpesa->account()
    ->setInitiatorName('your_initiator_name')
    ->setSecurityCredential('your_initiator_password')
    ->setTransactionId('LKXXXX1234')
    ->setPartyA('your_shortcode')
    ->setIdentifierType(Rndwiga\Mpesa\Api\Account::IDENTIFIER_TYPE_SHORTCODE)
    ->setRemarks('Test status check')
    ->setQueueTimeoutUrl('https://example.com/timeout')
    ->setResultUrl('https://example.com/result')
    ->setOccasion('Test occasion')
    ->checkTransactionStatus();
```

### Transaction Reversal

```php
$response = $mpesa->account()
    ->setInitiatorName('your_initiator_name')
    ->setSecurityCredential('your_initiator_password')
    ->setTransactionId('LKXXXX1234')
    ->setAmount(100) // Amount to reverse
    ->setReceiverParty('receiver_shortcode')
    ->setReceiverIdentifierType(Rndwiga\Mpesa\Api\Account::IDENTIFIER_TYPE_SHORTCODE)
    ->setRemarks('Test reversal')
    ->setQueueTimeoutUrl('https://example.com/timeout')
    ->setResultUrl('https://example.com/result')
    ->setOccasion('Test occasion')
    ->reverseTransaction();
```

### Processing Callbacks

#### Using the WebhookHandler (Recommended)

```php
// Create a webhook handler and capture the callback data
$webhook = $mpesa->webhook();
$webhook->captureCallback()->parseCallback();

// Check the type of callback
if ($webhook->isCallbackType('c2b')) {
    // Process C2B callback
    $transactionId = $webhook->getValue('TransID');
    $amount = $webhook->getValue('TransAmount');
    $phoneNumber = $webhook->getValue('MSISDN');

    // Do something with the data...

} elseif ($webhook->isCallbackType('express')) {
    // Process STK Push callback
    $resultCode = $webhook->getValue('Body.stkCallback.ResultCode');
    $resultDesc = $webhook->getValue('Body.stkCallback.ResultDesc');

    // Get nested values using dot notation
    $amount = $webhook->getValue('Body.stkCallback.CallbackMetadata.Item.0.Value');
    $mpesaReceiptNumber = $webhook->getValue('Body.stkCallback.CallbackMetadata.Item.1.Value');

    // Do something with the data...
}

// Send a success response back to Mpesa
echo $webhook->generateSuccessResponse('Transaction processed successfully');

// Or send an error response if needed
// echo $webhook->generateErrorResponse('Transaction failed', '1');
```

#### Legacy Method

```php
// For STK Push callbacks
$callbackData = json_decode(file_get_contents('php://input'), true);
$processedCallback = $mpesa->express()->processCallback($callbackData);

// For other callbacks (B2C, B2B, Account Balance, etc.)
$callbackData = json_decode(file_get_contents('php://input'), true);
$processedCallback = $mpesa->account()->processCallback($callbackData);

// Send a success response back to Mpesa
echo $mpesa->finishTransaction();
```

### Error Handling

The package provides custom exception classes for better error handling:

```php
use Rndwiga\Mpesa\Exceptions\MpesaException;
use Rndwiga\Mpesa\Exceptions\AuthenticationException;
use Rndwiga\Mpesa\Exceptions\ValidationException;
use Rndwiga\Mpesa\Exceptions\ApiException;

try {
    // Make an API request
    $response = $mpesa->b2c()
        ->setInitiatorName('your_initiator_name')
        ->setSecurityCredential('your_initiator_password')
        ->setCommandId(Rndwiga\Mpesa\Api\B2C::COMMAND_ID_BUSINESS_PAYMENT)
        ->setAmount(100)
        ->setPartyA('your_shortcode')
        ->setPartyB('254722000000')
        ->setRemarks('Test payment')
        ->setQueueTimeoutUrl('https://example.com/timeout')
        ->setResultUrl('https://example.com/result')
        ->setOccasion('Test occasion')
        ->makePayment();

    // Check if the request was successful
    if (!$response['success']) {
        // Handle API error
        echo "API Error: " . $response['errorMessage'];
    }

} catch (AuthenticationException $e) {
    // Handle authentication errors (e.g., invalid credentials)
    echo "Authentication Error: " . $e->getMessage();

} catch (ValidationException $e) {
    // Handle validation errors (e.g., invalid phone number)
    echo "Validation Error: " . $e->getMessage();

} catch (ApiException $e) {
    // Handle API-specific errors
    echo "API Error: " . $e->getMessage();
    echo "API Error Code: " . $e->getApiErrorCode();
    echo "Request ID: " . $e->getRequestId();

    // Get additional error data if available
    $errorData = $e->getErrorData();
    if ($errorData) {
        // Process error data
    }

} catch (MpesaException $e) {
    // Handle other Mpesa-related errors
    echo "Mpesa Error: " . $e->getMessage();

} catch (\Exception $e) {
    // Handle other unexpected errors
    echo "Error: " . $e->getMessage();
}
```

## Custom Implementations

### Custom Cache Implementation

You can create your own cache implementation by implementing the `CacheInterface`:

```php
use Rndwiga\Mpesa\Utils\CacheInterface;

class RedisCache implements CacheInterface
{
    private $redis;

    public function __construct($redisClient)
    {
        $this->redis = $redisClient;
    }

    public function get(string $key, $default = null)
    {
        $value = $this->redis->get($key);
        return $value !== false ? json_decode($value, true) : $default;
    }

    public function set(string $key, $value, ?int $ttl = null): bool
    {
        return $this->redis->setex($key, $ttl ?? 3600, json_encode($value));
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key);
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }
}

// Use your custom cache implementation
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$cache = new RedisCache($redis);

$mpesa = new MpesaAPI(
    'your_consumer_key',
    'your_consumer_secret',
    false,
    $logger,
    $cache
);
```

## Examples

Check the `examples` directory for more detailed examples:

- `b2c_example.php`: Example of B2C payment
- `b2b_example.php`: Example of B2B payment
- `c2b_example.php`: Example of C2B registration and simulation
- `express_example.php`: Example of STK Push and callback processing
- `account_balance_example.php`: Example of checking account balance
- `transaction_status_example.php`: Example of checking transaction status
- `transaction_reversal_example.php`: Example of reversing a transaction
- `webhook_example.php`: Example of using the WebhookHandler to process callbacks

All examples demonstrate the use of logging, caching, and webhook handling features.

## Testing

The package includes comprehensive tests for all components using PestPHP, a delightful testing framework for PHP:

```bash
composer test
```

You can also run the tests directly:

```bash
./vendor/bin/pest
```

Or run specific test files:

```bash
./vendor/bin/pest tests/Client/MpesaClientTest.php
./vendor/bin/pest tests/Utils/WebhookHandlerTest.php
```

PestPHP provides a more expressive and elegant syntax for writing tests. For more information, visit [pestphp.com](https://pestphp.com).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
