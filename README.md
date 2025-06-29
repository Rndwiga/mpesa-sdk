
![Logo](http://tyondo.com/tyondo/img/logo.png)

# Safaricom M-Pesa SDK

This is an opinionated fluent Safaricom M-Pesa SDK that provides a simple and elegant way to integrate with the Safaricom M-Pesa API.

## Table of Contents

- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Features](#features)
- [Usage](#usage)
  - [B2C (Business to Customer)](#b2c-business-to-customer)
  - [B2B (Business to Business)](#b2b-business-to-business)
  - [C2B (Customer to Business)](#c2b-customer-to-business)
  - [Express (STK Push)](#express-stk-push)
  - [Account Balance](#account-balance)
  - [Transaction Status](#transaction-status)
- [Handling Callbacks](#handling-callbacks)
- [Toolbox Utilities](#toolbox-utilities)
  - [Logging](#logging)
  - [Storage](#storage)
  - [JSON Management](#json-management)
  - [Helper Functions](#helper-functions)
- [Error Handling](#error-handling)
- [Contributing](#contributing)
- [License](#license)

## Tech Stack

**Server:** Nginx, Apache, PHP

## Installation 

Clone the project using the following:

```bash 
git clone https://github.com/Rndwiga/mpesa-sdk.git
cd mpesa-sdk
composer update
```

## Configuration

The SDK requires several environment variables to be set for proper operation:

```
# Application Status (true for live, false for sandbox)
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

## Features

The SDK provides the following features:

1. **B2C (Business to Customer)** - Send money from a business to customers
2. **B2B (Business to Business)** - Send money from one business to another
3. **C2B (Customer to Business)** - Receive money from customers
4. **Express (STK Push)** - Prompt customers to enter their M-Pesa PIN on their phones
5. **Account Balance** - Check account balance
6. **Transaction Status** - Check the status of a transaction
7. **Toolbox Utilities** - Logging, Storage, and JSON Management utilities

## Usage

### B2C (Business to Customer)

Send money from a business to a customer:

```php
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

Send money from one business to another:

```php
$response = (new \Rndwiga\Mpesa\Libraries\B2B\MpesaB2BCalls())
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

Register URLs for C2B transactions:

```php
$response = (new \Rndwiga\Mpesa\Libraries\C2B\MpesaC2BCalls())
    ->setApplicationStatus(false) // false for sandbox, true for production
    ->setConsumerKey(env('CONSUMER_KEY'))
    ->setConsumerSecret(env('CONSUMER_SECRET'))
    ->setShortCode(env('PARTY_A')) // Your shortcode
    ->setResponseType('Completed') // Options: Completed, Cancelled
    ->setConfirmationURL('https://your-domain.com/api/confirmation')
    ->setValidationURL('https://your-domain.com/api/validation')
    ->registerURLs();
```

Simulate a C2B transaction (for testing in sandbox):

```php
$response = (new \Rndwiga\Mpesa\Libraries\C2B\MpesaC2BCalls())
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

### Account Balance

Check your account balance:

```php
$response = (new \Rndwiga\Mpesa\Libraries\Account\MpesaAccountCalls())
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

Check the status of a transaction:

```php
$response = (new \Rndwiga\Mpesa\Libraries\Account\MpesaAccountCalls())
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

### Express (STK Push)

Prompt a customer to enter their M-Pesa PIN on their phone:

```php
$response = (new MpesaExpressCalls())
    ->setApplicationStatus('sandbox') // 'sandbox' or 'live'
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

### Handling Callbacks

Process B2C transaction callbacks:

```php
$callbackData = file_get_contents('php://input');
$response = (new B2CTransactionCallbacks())->b2CRequestCallback($callbackData);
```

Process Express (STK Push) transaction callbacks:

```php
$callbackData = file_get_contents('php://input');
$callbackData = json_decode($callbackData, true);
$response = (new MpesaExpressCalls())->processTransactionResult($callbackData);
```

## Toolbox Utilities

### Logging

Log information to files:

```php
(new AppLogger('folderName', 'fileName'))
    ->setMaxNumberOfLines(5000) // Optional: Set max lines in log file
    ->logInfo(['key' => 'value', 'message' => 'Log message']);
```

### Storage

Create storage directories:

```php
$storagePath = (new AppStorage())
    ->setRootFolder('customFolder') // Optional: Default is 'appLogs'
    ->setLogFolder('subFolder')
    ->createStorage();
```

### JSON Management

Save and read JSON data:

```php
// Save JSON data to file
AppJsonManager::saveToFile('data.json', 'directoryName', ['key' => 'value']);

// Read JSON data from file
$data = AppJsonManager::readJsonFile('path/to/file.json', true); // true to return as array

// Validate JSON data
$result = AppJsonManager::validateJsonData('{"key": "value"}');
```

## Error Handling

The SDK provides detailed error handling for API responses:

```php
$response = json_decode($apiResponse, true);
if (isset($response['errorCode'])) {
    $errorDetails = (new MpesaExpressCalls())->responseErrorDetails($response);
    // Handle error based on errorDetails
}
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.
