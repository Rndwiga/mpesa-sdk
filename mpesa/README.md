# Laravel Safaricom-Mpesa SDK

A comprehensive PHP package for integrating with Safaricom M-Pesa API services in Laravel applications.

## Installation

You can install the package via composer:

```bash
composer require rndwiga/mpesa
```

## Requirements

- PHP 8.1 or higher
- phpseclib/phpseclib 2.0 or higher
- OpenSSL and JSON PHP extensions

## Features

This package provides a fluent interface for interacting with all major M-Pesa API services:

1. **B2C (Business to Customer)** - Send money from a business to customers
2. **B2B (Business to Business)** - Send money from one business to another
3. **C2B (Customer to Business)** - Receive money from customers
4. **Express (STK Push)** - Prompt customers to enter their M-Pesa PIN on their phones
5. **Account Balance** - Check account balance
6. **Transaction Status** - Check the status of a transaction

## Configuration

The package requires several environment variables to be set in your Laravel .env file:

```
# Application Status (false for sandbox, true for production)
APPLICATION_STATUS=false

# M-Pesa API Credentials
CONSUMER_KEY=your_consumer_key
CONSUMER_SECRET=your_consumer_secret
INITIATOR_NAME=your_initiator_name
SECURITY_CREDENTIAL=your_security_credential

# B2C Settings
PARTY_A=your_shortcode
COMMAND_ID=BusinessPayment
QUEUE_TIMEOUT_URL=https://your-domain.com/api/timeout
RESULT_URL=https://your-domain.com/api/result

# Express (STK Push) Settings
BUSINESS_SHORT_CODE=your_business_shortcode
LIPA_NA_MPESA_PASSKEY=your_passkey
TRANSACTION_TYPE=CustomerPayBillOnline
CALLBACK_URL=https://your-domain.com/api/callback
ACCOUNT_REFERENCE=your_reference
TRANSACTION_DESC=your_description
```

## Usage

### B2C (Business to Customer)

```php
use Rndwiga\Mpesa\Libraries\B2C\MpesaB2CCalls;

$response = (new MpesaB2CCalls())
    ->setApplicationStatus(false) // false for sandbox, true for production
    ->setInitiatorName(env('INITIATOR_NAME'))
    ->setSecurityCredential(env('SECURITY_CREDENTIAL'))
    ->setConsumerKey(env('CONSUMER_KEY'))
    ->setConsumerSecret(env('CONSUMER_SECRET'))
    ->setCommandId('BusinessPayment') // Options: SalaryPayment, BusinessPayment, PromotionPayment
    ->setAmount(100) // Amount to send
    ->setPartyA(env('PARTY_A')) // Your shortcode
    ->setPartyB(254712345678) // Customer phone number
    ->setRemarks("Payment for services") // Transaction remarks
    ->setOccasion("Service payment") // Transaction occasion
    ->setQueueTimeOutUrl(env('QUEUE_TIMEOUT_URL')) // Timeout URL
    ->setResultUrl(env('RESULT_URL')) // Result URL
    ->makeB2cCall();
```

### B2B (Business to Business)

```php
use Rndwiga\Mpesa\Libraries\B2B\MpesaB2BCalls;

$response = (new MpesaB2BCalls())
    ->setApplicationStatus(false) // false for sandbox, true for production
    ->setInitiatorName(env('INITIATOR_NAME'))
    ->setSecurityCredential(env('SECURITY_CREDENTIAL'))
    ->setConsumerKey(env('CONSUMER_KEY'))
    ->setConsumerSecret(env('CONSUMER_SECRET'))
    ->setCommandId('BusinessPayBill') // Options: BusinessPayBill, MerchantToMerchantTransfer, etc.
    ->setAmount(100) // Amount to send
    ->setSenderIdentifierType(4) // 4 for organization shortcode
    ->setReceiverIdentifierType(4) // 4 for organization shortcode
    ->setPartyA(env('PARTY_A')) // Your shortcode
    ->setPartyB(600000) // Receiver shortcode
    ->setAccountReference('Account reference')
    ->setRemarks("Payment for services") // Transaction remarks
    ->setQueueTimeOutUrl(env('QUEUE_TIMEOUT_URL')) // Timeout URL
    ->setResultUrl(env('RESULT_URL')) // Result URL
    ->makeB2BCall();
```

### C2B (Customer to Business)

```php
use Rndwiga\Mpesa\Libraries\C2B\MpesaC2BCalls;

// Register URLs for C2B transactions
$response = (new MpesaC2BCalls())
    ->setApplicationStatus(false) // false for sandbox, true for production
    ->setConsumerKey(env('CONSUMER_KEY'))
    ->setConsumerSecret(env('CONSUMER_SECRET'))
    ->setShortCode(env('PARTY_A')) // Your shortcode
    ->setResponseType('Completed') // Options: Completed, Cancelled
    ->setConfirmationURL('https://your-domain.com/api/confirmation')
    ->setValidationURL('https://your-domain.com/api/validation')
    ->registerURLs();

// Simulate a C2B transaction (for testing in sandbox)
$response = (new MpesaC2BCalls())
    ->setApplicationStatus(false) // false for sandbox, true for production
    ->setConsumerKey(env('CONSUMER_KEY'))
    ->setConsumerSecret(env('CONSUMER_SECRET'))
    ->setShortCode(env('PARTY_A')) // Your shortcode
    ->setCommandID('CustomerPayBillOnline') // Options: CustomerPayBillOnline, CustomerBuyGoodsOnline
    ->setAmount(100) // Amount to send
    ->setMSISDN(254712345678) // Customer phone number
    ->setBillRefNumber('REF123') // Reference number
    ->simulateC2B();
```

### Express (STK Push)

```php
use Rndwiga\Mpesa\Libraries\Express\MpesaExpressCalls;

$response = (new MpesaExpressCalls())
    ->setApplicationStatus(false) // false for sandbox, true for production
    ->setConsumerKey(env('CONSUMER_KEY'))
    ->setConsumerSecret(env('CONSUMER_SECRET'))
    ->setBusinessShortCode(env('BUSINESS_SHORT_CODE'))
    ->setLipaNaMpesaPasskey(env('LIPA_NA_MPESA_PASSKEY'))
    ->setTransactionType('CustomerPayBillOnline')
    ->setAmount(100) // Amount to charge
    ->setPartyA(254712345678) // Customer phone number
    ->setPartyB(env('BUSINESS_SHORT_CODE')) // Your shortcode
    ->setPhoneNumber(254712345678) // Customer phone number
    ->setCallBackURL(env('CALLBACK_URL')) // Callback URL
    ->setAccountReference('Payment for order #123') // Reference
    ->setTransactionDesc('Payment for product/service') // Description
    ->STKPush();
```

### Account Balance

```php
use Rndwiga\Mpesa\Libraries\Account\MpesaAccountCalls;

$response = (new MpesaAccountCalls())
    ->setApplicationStatus(false) // false for sandbox, true for production
    ->setInitiatorName(env('INITIATOR_NAME'))
    ->setSecurityCredential(env('SECURITY_CREDENTIAL'))
    ->setConsumerKey(env('CONSUMER_KEY'))
    ->setConsumerSecret(env('CONSUMER_SECRET'))
    ->setCommandId('AccountBalance')
    ->setPartyA(env('PARTY_A')) // Your shortcode
    ->setIdentifierType(4) // 4 for organization shortcode
    ->setRemarks("Account balance query")
    ->setQueueTimeOutUrl(env('QUEUE_TIMEOUT_URL')) // Timeout URL
    ->setResultUrl(env('RESULT_URL')) // Result URL
    ->queryAccountBalance();
```

### Transaction Status

```php
use Rndwiga\Mpesa\Libraries\Account\MpesaAccountCalls;

$response = (new MpesaAccountCalls())
    ->setApplicationStatus(false) // false for sandbox, true for production
    ->setInitiatorName(env('INITIATOR_NAME'))
    ->setSecurityCredential(env('SECURITY_CREDENTIAL'))
    ->setConsumerKey(env('CONSUMER_KEY'))
    ->setConsumerSecret(env('CONSUMER_SECRET'))
    ->setCommandId('TransactionStatusQuery')
    ->setPartyA(env('PARTY_A')) // Your shortcode
    ->setIdentifierType(4) // 4 for organization shortcode
    ->setTransactionID('LKXXXX1234') // The M-Pesa Transaction ID
    ->setRemarks("Transaction status query")
    ->setQueueTimeOutUrl(env('QUEUE_TIMEOUT_URL')) // Timeout URL
    ->setResultUrl(env('RESULT_URL')) // Result URL
    ->queryTransactionStatus();
```

### Handling Callbacks

```php
use Rndwiga\Mpesa\Libraries\B2C\B2CTransactionCallbacks;
use Rndwiga\Mpesa\Libraries\Express\MpesaExpressCalls;

// Process B2C transaction callbacks
$callbackData = file_get_contents('php://input');
$response = (new B2CTransactionCallbacks())->b2CRequestCallback($callbackData);

// Process Express (STK Push) transaction callbacks
$callbackData = file_get_contents('php://input');
$callbackData = json_decode($callbackData, true);
$response = (new MpesaExpressCalls())->processTransactionResult($callbackData);
```

## Error Handling

The package provides detailed error handling for API responses:

```php
$response = json_decode($apiResponse, true);
if (isset($response['errorCode'])) {
    $errorDetails = (new MpesaExpressCalls())->responseErrorDetails($response);
    // Handle error based on errorDetails
}
```

## License

This package is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
