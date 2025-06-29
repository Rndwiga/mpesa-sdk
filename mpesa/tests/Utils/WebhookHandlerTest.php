<?php

use Psr\Log\LoggerInterface;
use Rndwiga\Mpesa\Utils\WebhookHandler;

beforeEach(function () {
    // Create mock logger
    $this->logger = mock(LoggerInterface::class);
    $this->logger->shouldReceive('info')->byDefault();
    $this->logger->shouldReceive('debug')->byDefault();
    $this->logger->shouldReceive('error')->byDefault();
    $this->logger->shouldReceive('warning')->byDefault();

    // Create webhook handler with mocked logger
    $this->handler = new WebhookHandler($this->logger);
});

afterEach(function () {
    // No need to close Mockery in Pest
});

test('capture and parse callback', function () {
    // Sample C2B callback data
    $callbackData = json_encode([
        'TransactionType' => 'Pay Bill',
        'TransID' => 'RKTQDM5HTY',
        'TransTime' => '20191122063845',
        'TransAmount' => '10',
        'BusinessShortCode' => '600638',
        'BillRefNumber' => 'Test',
        'InvoiceNumber' => '',
        'OrgAccountBalance' => '49197.00',
        'ThirdPartyTransID' => '',
        'MSISDN' => '254708374149',
        'FirstName' => 'John',
        'MiddleName' => 'Doe',
        'LastName' => ''
    ]);

    // The logger should log that we're capturing and parsing the callback
    $this->logger->shouldReceive('debug')
        ->once()
        ->with('Captured webhook callback', Mockery::hasKey('rawData'));

    $this->logger->shouldReceive('info')
        ->once()
        ->with('Parsed webhook callback data', Mockery::hasKey('parsedData'));

    // Capture and parse the callback
    $this->handler->captureCallback($callbackData)->parseCallback();

    // Get the parsed data
    $data = $this->handler->getData();

    // Verify the data was parsed correctly
    expect($data['TransactionType'])->toBe('Pay Bill');
    expect($data['TransID'])->toBe('RKTQDM5HTY');
    expect($data['TransAmount'])->toBe('10');
    expect($data['MSISDN'])->toBe('254708374149');
    expect($data['FirstName'])->toBe('John');
});

test('is callback type', function () {
    // Sample C2B callback data
    $callbackData = json_encode([
        'TransactionType' => 'Pay Bill',
        'TransID' => 'RKTQDM5HTY',
        'TransAmount' => '10',
        'MSISDN' => '254708374149'
    ]);

    // Capture and parse the callback
    $this->handler->captureCallback($callbackData)->parseCallback();

    // Test callback type detection
    expect($this->handler->isCallbackType('c2b'))->toBeTrue();
    expect($this->handler->isCallbackType('b2c'))->toBeFalse();
    expect($this->handler->isCallbackType('express'))->toBeFalse();
});

test('get value', function () {
    // Sample nested callback data
    $callbackData = json_encode([
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => '29115-34620561-1',
                'CheckoutRequestID' => 'ws_CO_191121063338074',
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'CallbackMetadata' => [
                    'Item' => [
                        [
                            'Name' => 'Amount',
                            'Value' => 1
                        ],
                        [
                            'Name' => 'MpesaReceiptNumber',
                            'Value' => 'NLJ7RT61SV'
                        ]
                    ]
                ]
            ]
        ]
    ]);

    // Capture and parse the callback
    $this->handler->captureCallback($callbackData)->parseCallback();

    // Test getting values with dot notation
    expect($this->handler->getValue('Body.stkCallback.MerchantRequestID'))->toBe('29115-34620561-1');
    expect($this->handler->getValue('Body.stkCallback.ResultCode'))->toBe(0);

    // Test getting a value that doesn't exist
    expect($this->handler->getValue('Body.nonExistentKey'))->toBeNull();
    expect($this->handler->getValue('Body.nonExistentKey', 'default'))->toBe('default');
});

test('generate responses', function () {
    // Test success response
    $successMessage = 'Transaction processed successfully';
    $expectedSuccess = json_encode([
        'ResultDesc' => $successMessage,
        'ResultCode' => '0'
    ]);

    expect($this->handler->generateSuccessResponse($successMessage))->toBe($expectedSuccess);

    // Test error response
    $errorMessage = 'Transaction failed';
    $errorCode = '1032';
    $expectedError = json_encode([
        'ResultDesc' => $errorMessage,
        'ResultCode' => $errorCode
    ]);

    expect($this->handler->generateErrorResponse($errorMessage, $errorCode))->toBe($expectedError);
});
