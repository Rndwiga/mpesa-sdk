<?php
/**
 * Transaction Status Example
 *
 * This example demonstrates how to use the Mpesa API SDK to check the status of a transaction.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Rndwiga\Mpesa\MpesaAPI;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Rndwiga\Mpesa\Api\Account;

// Create a logger
$logger = new Logger('mpesa');
$logger->pushHandler(new StreamHandler(__DIR__ . '/mpesa.log', Logger::DEBUG));

// Replace these with your actual credentials
$consumerKey = 'your_consumer_key';
$consumerSecret = 'your_consumer_secret';
$initiatorName = 'your_initiator_name';
$initiatorPassword = 'your_initiator_password';
$shortcode = 'your_shortcode'; // PartyA
$transactionId = 'LKXXXX1234'; // Replace with an actual transaction ID

// Initialize the Mpesa API with logger
$mpesa = new MpesaAPI(
    $consumerKey,
    $consumerSecret,
    false, // false for sandbox, true for production
    $logger
);

// Check transaction status
echo "Checking transaction status...\n";

try {
    $response = $mpesa->account()
        ->setInitiatorName($initiatorName)
        ->setSecurityCredential($initiatorPassword)
        ->setTransactionId($transactionId)
        ->setPartyA($shortcode)
        ->setIdentifierType(Account::IDENTIFIER_TYPE_SHORTCODE)
        ->setRemarks('Test status check')
        ->setQueueTimeoutUrl('https://example.com/timeout')
        ->setResultUrl('https://example.com/result')
        ->setOccasion('Test occasion')
        ->checkTransactionStatus();

    // Print the response
    echo "Response:\n";
    print_r($response);

    // Check if the request was successful
    if (isset($response['success']) && $response['success']) {
        echo "\nTransaction status check initiated successfully!\n";
        echo "Conversation ID: " . $response['data']->ConversationID . "\n";
        echo "Originator Conversation ID: " . $response['data']->OriginatorConversationID . "\n";
        echo "Response Description: " . $response['data']->ResponseDescription . "\n";
    } else {
        echo "\nTransaction status check failed!\n";
        if (isset($response['errorMessage'])) {
            echo "Error: " . $response['errorMessage'] . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Error checking transaction status: " . $e->getMessage() . "\n";
}

// Example of processing a transaction status callback
echo "\nExample of processing a transaction status callback:\n";

// This would normally come from the POST data in your callback URL
$sampleCallback = json_encode([
    'Result' => [
        'ResultType' => 0,
        'ResultCode' => 0,
        'ResultDesc' => 'The service request is processed successfully.',
        'OriginatorConversationID' => '29112-34567890-1',
        'ConversationID' => 'AG_20191219_00007fc37fc37fc37fc3',
        'TransactionID' => 'LKXXXX1234',
        'ResultParameters' => [
            'ResultParameter' => [
                [
                    'Key' => 'OriginatorConversationID',
                    'Value' => '29112-34567890-1'
                ],
                [
                    'Key' => 'ConversationID',
                    'Value' => 'AG_20191219_00007fc37fc37fc37fc3'
                ],
                [
                    'Key' => 'TransactionStatus',
                    'Value' => 'Completed'
                ],
                [
                    'Key' => 'ReceiptNo',
                    'Value' => 'LKXXXX1234'
                ],
                [
                    'Key' => 'TransactionAmount',
                    'Value' => '100'
                ],
                [
                    'Key' => 'TransactionDate',
                    'Value' => '19.12.2019 12:01:59'
                ],
                [
                    'Key' => 'PhoneNumber',
                    'Value' => '254722000000'
                ],
                [
                    'Key' => 'Reason',
                    'Value' => ''
                ]
            ]
        ],
        'ReferenceData' => [
            'ReferenceItem' => [
                'Key' => 'QueueTimeoutURL',
                'Value' => 'https://example.com/timeout'
            ]
        ]
    ]
]);

// Using the new WebhookHandler to process the callback
$webhook = $mpesa->webhook($sampleCallback);
$webhook->parseCallback();

// Check if it's a transaction status callback
if ($webhook->isCallbackType('status')) {
    echo "Received transaction status callback\n";
    
    // Extract key information
    $resultCode = $webhook->getValue('Result.ResultCode');
    $resultDesc = $webhook->getValue('Result.ResultDesc');
    $transactionId = $webhook->getValue('Result.TransactionID');
    
    // Find specific parameters using dot notation
    $status = null;
    $receiptNo = null;
    $amount = null;
    $date = null;
    $phoneNumber = null;
    $reason = null;
    
    $parameters = $webhook->getValue('Result.ResultParameters.ResultParameter');
    
    if (is_array($parameters)) {
        foreach ($parameters as $param) {
            switch ($param['Key']) {
                case 'TransactionStatus':
                    $status = $param['Value'];
                    break;
                case 'ReceiptNo':
                    $receiptNo = $param['Value'];
                    break;
                case 'TransactionAmount':
                    $amount = $param['Value'];
                    break;
                case 'TransactionDate':
                    $date = $param['Value'];
                    break;
                case 'PhoneNumber':
                    $phoneNumber = $param['Value'];
                    break;
                case 'Reason':
                    $reason = $param['Value'];
                    break;
            }
        }
    }
    
    echo "Transaction ID: $transactionId\n";
    echo "Status: $status\n";
    echo "Receipt Number: $receiptNo\n";
    echo "Amount: $amount\n";
    echo "Date: $date\n";
    echo "Phone Number: $phoneNumber\n";
    
    if (!empty($reason)) {
        echo "Reason: $reason\n";
    }
    
    echo "Result: $resultDesc (Code: $resultCode)\n";
    
    // In a real application, you would:
    // 1. Update your database with the transaction status
    // 2. Notify the user of the transaction status
    // 3. Take appropriate action based on the status
    
    // Send a success response back to Mpesa
    echo "\nResponse to Mpesa: " . $webhook->generateSuccessResponse('Transaction status received successfully') . "\n";
} else {
    echo "Not a transaction status callback\n";
}