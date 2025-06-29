
# Safaricom M-Pesa SDK

An opinionated fluent Safaricom M-Pesa SDK that provides a simple and elegant way to integrate with the Safaricom M-Pesa API.

## Table of Contents

- [Tech Stack](#tech-stack)
- [Installation](#installation)
  - [Laravel Integration](#laravel-integration)
- [Requirements](#requirements)
- [Configuration](#configuration)
  - [Laravel Configuration](#laravel-configuration)
- [Features](#features)
- [Usage](#usage)
  - [Initialization](#initialization)
  - [Laravel Usage](#laravel-usage)
  - [B2C (Business to Customer)](#b2c-business-to-customer)
  - [B2B (Business to Business)](#b2b-business-to-business)
  - [C2B (Customer to Business)](#c2b-customer-to-business)
  - [Express (STK Push)](#express-stk-push)
  - [Account Balance](#account-balance)
  - [Transaction Status](#transaction-status)
  - [Transaction Reversal](#transaction-reversal)
- [Handling Callbacks](#handling-callbacks)
- [Utilities](#utilities)
  - [Logging](#logging)
  - [Storage](#storage)
  - [JSON Management](#json-management)
  - [Helper Functions](#helper-functions)
- [Error Handling](#error-handling)
- [Custom Implementations](#custom-implementations)
  - [Custom Cache Implementation](#custom-cache-implementation)
- [Examples](#examples)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Tech Stack

**Server:** Nginx, Apache, PHP 8.1+

## Installation 

Install the package via Composer:

```bash
composer require rndwiga/mpesa-sdk
```

Or clone the project:

```bash 
git clone https://github.com/Rndwiga/mpesa-sdk.git
cd mpesa-sdk
composer update
```

### Laravel Integration

This package supports Laravel integration out of the box. After installing the package, the service provider will be automatically registered thanks to Laravel's package auto-discovery feature.

If you're using Laravel < 5.5 or have disabled auto-discovery, add the service provider and facade to your `config/app.php` file:

```php
'providers' => [
    // ...
    Rndwiga\Mpesa\MpesaServiceProvider::class,
],

'aliases' => [
    // ...
    'Mpesa' => Rndwiga\Mpesa\Facades\Mpesa::class,
],
```

Then publish the configuration file:

```bash
php artisan vendor:publish --tag=mpesa-config
```

## Requirements

- PHP 8.1 or higher
- phpseclib/phpseclib 2.0 or higher
- psr/log 3.0 or higher (for logging)
- OpenSSL and JSON PHP extensions

## Configuration

The SDK requires several environment variables to be set for proper operation:

```
# M-Pesa API Credentials
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_INITIATOR_NAME=your_initiator_name
MPESA_INITIATOR_PASSWORD=your_initiator_password

# Business Shortcode
MPESA_SHORTCODE=your_shortcode
MPESA_BUSINESS_SHORTCODE=your_business_shortcode

# Express (STK Push) Settings
MPESA_PASSKEY=your_passkey

# Environment
MPESA_PRODUCTION=false

# Callback URLs
MPESA_CALLBACK_URL=https://your-domain.com/api/callback
MPESA_TIMEOUT_URL=https://your-domain.com/api/timeout
MPESA_RESULT_URL=https://your-domain.com/api/result
```

### Laravel Configuration

When using the package with Laravel, you can publish the configuration file to customize the settings:

```bash
php artisan vendor:publish --tag=mpesa-config
```

This will create a `config/mpesa.php` file in your Laravel application where you can configure all the M-Pesa settings. The configuration values will be automatically loaded from your `.env` file using the prefixed environment variables (e.g., `MPESA_CONSUMER_KEY`).

Example Laravel `.env` configuration:

```
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_PRODUCTION=false
MPESA_INITIATOR_NAME=your_initiator_name
MPESA_INITIATOR_PASSWORD=your_initiator_password
MPESA_SHORTCODE=your_shortcode
MPESA_BUSINESS_SHORTCODE=your_business_shortcode
MPESA_PASSKEY=your_passkey
MPESA_CALLBACK_URL=https://your-domain.com/api/callback
MPESA_TIMEOUT_URL=https://your-domain.com/api/timeout
MPESA_RESULT_URL=https://your-domain.com/api/result
```

## Features

The SDK provides the following features:

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
5. **Utilities** - Logging, Storage, JSON Management, and Helper Functions

## Usage

### Initialization

Initialize the MpesaAPI with your credentials:

```php
use Rndwiga\Mpesa\MpesaAPI;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Rndwiga\Mpesa\Utils\FileCache;

// Create a logger (optional)
$logger = new Logger('mpesa');
$logger->pushHandler(new StreamHandler('path/to/mpesa.log', Logger::DEBUG));

// Create a cache (optional)
$cache = new FileCache('path/to/cache');

// Initialize the Mpesa API
$mpesa = new MpesaAPI(
    'your_consumer_key',
    'your_consumer_secret',
    false, // false for sandbox, true for production
    $logger, // optional
    $cache  // optional
);
```

### Laravel Usage

When using the package with Laravel, you can access the Mpesa API through the facade:

```php
use Rndwiga\Mpesa\Facades\Mpesa;

// The Mpesa facade automatically uses the configuration from config/mpesa.php
// No need to manually initialize the API

// Example: Send money using B2C
$response = Mpesa::b2c()
    ->setInitiatorName(config('mpesa.initiator_name'))
    ->setSecurityCredential(config('mpesa.initiator_password'))
    ->setCommandId('BusinessPayment')
    ->setAmount(100)
    ->setPartyA(config('mpesa.shortcode'))
    ->setPartyB('254712345678')
    ->setRemarks('Payment for services')
    ->setQueueTimeoutUrl(config('mpesa.timeout_url'))
    ->setResultUrl(config('mpesa.result_url'))
    ->setOccasion('Service payment')
    ->makePayment();

// Example: STK Push
$response = Mpesa::express()
    ->setBusinessShortCode(config('mpesa.business_shortcode'))
    ->setPasskey(config('mpesa.passkey'))
    ->setTransactionType('CustomerPayBillOnline')
    ->setAmount(100)
    ->setPartyA('254712345678')
    ->setPartyB(config('mpesa.business_shortcode'))
    ->setPhoneNumber('254712345678')
    ->setCallbackUrl(config('mpesa.callback_url'))
    ->setAccountReference('Payment for order #123')
    ->setTransactionDesc('Payment for product/service')
    ->push();
```

You can also use dependency injection in your controllers:

```php
use Rndwiga\Mpesa\MpesaAPI;

class PaymentController extends Controller
{
    protected $mpesa;

    public function __construct(MpesaAPI $mpesa)
    {
        $this->mpesa = $mpesa;
    }

    public function processPayment()
    {
        $response = $this->mpesa->express()
            ->setBusinessShortCode(config('mpesa.business_shortcode'))
            // ... other settings
            ->push();

        // Process the response
        return response()->json($response);
    }
}
```

### B2C (Business to Customer)

Send money from a business to a customer:

```php
$response = $mpesa->b2c()
    ->setInitiatorName('your_initiator_name')
    ->setSecurityCredential('your_initiator_password')
    ->setCommandId('BusinessPayment') // Options: SalaryPayment, BusinessPayment, PromotionPayment
    ->setAmount(100) // Amount to send
    ->setPartyA('your_shortcode') // Your shortcode
    ->setPartyB('254712345678') // Customer phone number
    ->setRemarks('Payment for services') // Transaction remarks
    ->setQueueTimeoutUrl('https://example.com/timeout') // Timeout URL
    ->setResultUrl('https://example.com/result') // Result URL
    ->setOccasion('Service payment') // Optional: Transaction occasion
    ->makePayment();
```

### B2B (Business to Business)

Send money from one business to another:

```php
$response = $mpesa->b2b()
    ->setInitiatorName('your_initiator_name')
    ->setSecurityCredential('your_initiator_password')
    ->setCommandId('BusinessPayBill') // Options: BusinessPayBill, MerchantToMerchantTransfer, etc.
    ->setAmount(100) // Amount to send
    ->setSenderIdentifierType(4) // 4 for organization shortcode
    ->setReceiverIdentifierType(4) // 4 for organization shortcode
    ->setPartyA('your_shortcode') // Your shortcode
    ->setPartyB('600000') // Receiver shortcode
    ->setAccountReference('Account reference')
    ->setRemarks('Payment for services') // Transaction remarks
    ->setQueueTimeoutUrl('https://example.com/timeout') // Timeout URL
    ->setResultUrl('https://example.com/result') // Result URL
    ->makePayment();
```

### C2B (Customer to Business)

Register URLs for C2B transactions:

```php
$response = $mpesa->c2b()
    ->setShortCode('your_shortcode') // Your shortcode
    ->setResponseType('Completed') // Options: Completed, Cancelled
    ->setConfirmationURL('https://example.com/confirmation')
    ->setValidationURL('https://example.com/validation')
    ->registerURLs();
```

Simulate a C2B transaction (for testing in sandbox):

```php
$response = $mpesa->c2b()
    ->setShortCode('your_shortcode') // Your shortcode
    ->setCommandID('CustomerPayBillOnline') // Options: CustomerPayBillOnline, CustomerBuyGoodsOnline
    ->setAmount(100) // Amount to send
    ->setMsisdn('254712345678') // Customer phone number
    ->setBillRefNumber('REF123') // Reference number
    ->simulate();
```

### Express (STK Push)

Prompt a customer to enter their M-Pesa PIN on their phone:

```php
$response = $mpesa->express()
    ->setBusinessShortCode('your_business_shortcode')
    ->setPasskey('your_passkey')
    ->setTransactionType('CustomerPayBillOnline')
    ->setAmount(100) // Amount to charge
    ->setPartyA('254712345678') // Customer phone number
    ->setPartyB('your_business_shortcode') // Your shortcode
    ->setPhoneNumber('254712345678') // Customer phone number
    ->setCallbackUrl('https://example.com/callback') // Callback URL
    ->setAccountReference('Payment for order #123') // Reference
    ->setTransactionDesc('Payment for product/service') // Description
    ->push();
```

Query the status of an STK push:

```php
$response = $mpesa->express()
    ->setBusinessShortCode('your_business_shortcode')
    ->setPasskey('your_passkey')
    ->query('ws_CO_DMZ_123456789_123456789');
```

### Account Balance

Check your account balance:

```php
$response = $mpesa->account()
    ->setInitiatorName('your_initiator_name')
    ->setSecurityCredential('your_initiator_password')
    ->setCommandId('AccountBalance')
    ->setPartyA('your_shortcode') // Your shortcode
    ->setIdentifierType(4) // 4 for organization shortcode
    ->setRemarks('Account balance query')
    ->setQueueTimeoutUrl('https://example.com/timeout') // Timeout URL
    ->setResultUrl('https://example.com/result') // Result URL
    ->queryBalance();
```

### Transaction Status

Check the status of a transaction:

```php
$response = $mpesa->account()
    ->setInitiatorName('your_initiator_name')
    ->setSecurityCredential('your_initiator_password')
    ->setCommandId('TransactionStatusQuery')
    ->setPartyA('your_shortcode') // Your shortcode
    ->setIdentifierType(4) // 4 for organization shortcode
    ->setTransactionID('LKXXXX1234') // The M-Pesa Transaction ID
    ->setRemarks('Transaction status query')
    ->setQueueTimeoutUrl('https://example.com/timeout') // Timeout URL
    ->setResultUrl('https://example.com/result') // Result URL
    ->queryTransactionStatus();
```

### Transaction Reversal

Reverse a transaction:

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

## Handling Callbacks

### Using the WebhookHandler (Recommended)

Process callbacks from M-Pesa:

```php
// Get the callback data
$callbackData = file_get_contents('php://input');

// Create a webhook handler
$webhook = $mpesa->webhook($callbackData);

// Check the callback type
if ($webhook->isCallbackType('express')) {
    // Handle Express (STK Push) callback
    $resultCode = $webhook->getValue('Body.stkCallback.ResultCode');
    $resultDesc = $webhook->getValue('Body.stkCallback.ResultDesc');

    if ($resultCode == 0) {
        // Transaction was successful
        $amount = $webhook->getValueFromItems('Amount');
        $receiptNumber = $webhook->getValueFromItems('MpesaReceiptNumber');
        $transactionDate = $webhook->getValueFromItems('TransactionDate');
        $phoneNumber = $webhook->getValueFromItems('PhoneNumber');

        // Process the payment
        // ...
    }

    // Send a success response back to Mpesa
    echo $webhook->generateSuccessResponse('Callback processed successfully');
} elseif ($webhook->isCallbackType('b2c')) {
    // Handle B2C callback
    // ...
} elseif ($webhook->isCallbackType('c2b')) {
    // Handle C2B callback
    // ...
}
```

### Alternative Method

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

### Legacy Method

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

## Utilities

### Logging

The SDK includes multiple logging options:

#### Using PSR-3 Compatible Logger

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('mpesa');
$logger->pushHandler(new StreamHandler('path/to/mpesa.log', Logger::DEBUG));

// Use the logger with the MpesaAPI
$mpesa = new MpesaAPI(
    'your_consumer_key',
    'your_consumer_secret',
    false,
    $logger
);

// The logger will automatically log API requests and responses
```

#### Using the Built-in Logger

```php
use Rndwiga\Mpesa\Utils\Logger;

// Create a logger with folder and file name
$logger = new Logger('mpesa_logs', 'transactions');

// Log data at different levels
$logger->logDebugData(['transaction_id' => '123456', 'status' => 'pending']);
$logger->logInfoData(['message' => 'Payment received', 'amount' => 1000]);
$logger->logWarningData(['message' => 'Retry attempt', 'attempt' => 3]);
$logger->logErrorData(['message' => 'Transaction failed', 'reason' => 'Timeout']);

// Or use PSR-3 methods
$logger->debug('Debug message', ['context' => 'value']);
$logger->info('Info message', ['context' => 'value']);
$logger->warning('Warning message', ['context' => 'value']);
$logger->error('Error message', ['context' => 'value']);

// Get the log file path
$logFilePath = $logger->getLogFile('log'); // Returns path to transactions.log
```

### Storage

The SDK includes multiple storage options:

#### Using File Cache

```php
use Rndwiga\Mpesa\Utils\FileCache;

$cache = new FileCache('path/to/cache');

// Use the cache with the MpesaAPI
$mpesa = new MpesaAPI(
    'your_consumer_key',
    'your_consumer_secret',
    false,
    null,
    $cache
);

// The cache will automatically store access tokens
```

#### Using the Storage Class

```php
use Rndwiga\Mpesa\Utils\Storage;

// Create a storage instance
$storage = new Storage('mpesa_data');
$storage->setLogFolder('transactions');

// Create a storage path
$path = $storage->createStorage();

// Get the full storage path
$fullPath = $storage->storagePath($path);

// Compress a file
$compressedFile = Storage::gzCompressFile('path/to/file.txt', 9, true);

// Generate a random ID
$randomId = Storage::generateRandomId();
```

### JSON Management

The SDK includes utilities for working with JSON data:

#### Using WebhookHandler

```php
use Rndwiga\Mpesa\Utils\WebhookHandler;

// Parse JSON data
$webhook = new WebhookHandler();
$webhook->captureCallback($jsonData);
$webhook->parseCallback();

// Extract values using dot notation
$value = $webhook->getValue('path.to.value');

// Generate JSON responses
$response = $webhook->generateSuccessResponse('Success message');
```

#### Using JsonManager

```php
use Rndwiga\Mpesa\Utils\JsonManager;

// Save data to a JSON file
$result = JsonManager::saveToFile('data.json', 'transactions', ['id' => 123, 'amount' => 1000]);

// Save data to a specific file path
$result = JsonManager::saveToFilePath('/path/to/file.json', ['id' => 123, 'amount' => 1000]);

// Read a JSON file
$data = JsonManager::readJsonFile('/path/to/file.json', true);

// Add data to an existing JSON file
$result = JsonManager::addDataToJsonFile('/path/to/file.json', ['id' => 456, 'amount' => 2000]);

// Validate JSON data
$validation = JsonManager::validateJsonData('{"id": 123, "amount": 1000}');
if ($validation['status'] === 'success') {
    // JSON is valid
    $data = $validation['response'];
}
```

### Helper Functions

The SDK includes helper functions for common tasks:

```php
use function Rndwiga\Mpesa\Utils\mpesa_env;
use function Rndwiga\Mpesa\Utils\mpesa_str_slug;

// Get an environment variable
$apiKey = mpesa_env('MPESA_API_KEY');

// Convert a string to a URL-friendly slug
$slug = mpesa_str_slug('Hello World'); // Returns 'hello-world'
```

When using the package with Laravel, these helper functions are designed to work seamlessly with Laravel's built-in functions. The `mpesa_env()` function will use Laravel's `env()` function if available, and `mpesa_str_slug()` will use Laravel's `Str::slug()` if available.

## Error Handling

The SDK provides detailed error handling for API responses:

```php
try {
    $response = $mpesa->express()->push();

    if (isset($response['success']) && $response['success']) {
        // Request was successful
        $checkoutRequestId = $response['data']->CheckoutRequestID;
        // ...
    } else {
        // Request failed
        $errorMessage = $response['errorMessage'] ?? 'Unknown error';
        // Handle the error
    }
} catch (\Exception $e) {
    // Handle exceptions
    $errorMessage = $e->getMessage();
    // ...
}
```

The package also provides custom exception classes for better error handling:

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

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.
