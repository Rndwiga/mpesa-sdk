<?php
/**
 * Transaction Reversal Example
 *
 * This example demonstrates how to use the Mpesa API SDK to reverse a transaction.
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
$transactionId = 'LKXXXX1234'; // Replace with an actual transaction ID to reverse
$receiverShortcode = 'receiver_shortcode'; // The shortcode that received the payment

// Initialize the Mpesa API with logger
$mpesa = new MpesaAPI(
    $consumerKey,
    $consumerSecret,
    false, // false for sandbox, true for production
    $logger
);

// Reverse a transaction
echo "Reversing transaction...\n";

try {
    $response = $mpesa->account()
        ->setInitiatorName($initiatorName)
        ->setSecurityCredential($initiatorPassword)
        ->setTransactionId($transactionId)
        ->setAmount(100) // Amount to reverse
        ->setReceiverParty($receiverShortcode)
        ->setReceiverIdentifierType(Account::IDENTIFIER_TYPE_SHORTCODE)
        ->setRemarks('Test reversal')
        ->setQueueTimeoutUrl('https://example.com/timeout')
        ->setResultUrl('https://example.com/result')
        ->setOccasion('Test occasion')
        ->reverseTransaction();

    // Print the response
    echo "Response:\n";
    print_r($response);

    // Check if the request was successful
    if (isset($response['success']) && $response['success']) {
        echo "\nTransaction reversal initiated successfully!\n";
        echo "Conversation ID: " . $response['data']->ConversationID . "\n";
        echo "Originator Conversation ID: " . $response['data']->OriginatorConversationID . "\n";
        echo "Response Description: " . $response['data']->ResponseDescription . "\n";
    } else {
        echo "\nTransaction reversal failed!\n";
        if (isset($response['errorMessage'])) {
            echo "Error: " . $response['errorMessage'] . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Error reversing transaction: " . $e->getMessage() . "\n";
}

// Example of processing a transaction reversal callback
echo "\nExample of processing a transaction reversal callback:\n";

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
                    'Key' => 'DebitAccountBalance',
                    'Value' => 'Working Account|KES|481000.00|481000.00|0.00|0.00'
                ],
                [
                    'Key' => 'Amount',
                    'Value' => '100'
                ],
                [
                    'Key' => 'TransCompletedTime',
                    'Value' => '20191219120000'
                ],
                [
                    'Key' => 'OriginalTransactionID',
                    'Value' => 'LKXXXX1234'
                ],
                [
                    'Key' => 'Charge',
                    'Value' => '0.00'
                ],
                [
                    'Key' => 'CreditPartyPublicName',
                    'Value' => '254722000000 - John Doe'
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

// Check if it's a reversal callback
if ($webhook->isCallbackType('reversal')) {
    echo "Received transaction reversal callback\n";
    
    // Extract key information
    $resultCode = $webhook->getValue('Result.ResultCode');
    $resultDesc = $webhook->getValue('Result.ResultDesc');
    $transactionId = $webhook->getValue('Result.TransactionID');
    
    // Find specific parameters using dot notation
    $debitAccountBalance = null;
    $amount = null;
    $completedTime = null;
    $originalTransactionId = null;
    $charge = null;
    $creditPartyName = null;
    
    $parameters = $webhook->getValue('Result.ResultParameters.ResultParameter');
    
    if (is_array($parameters)) {
        foreach ($parameters as $param) {
            switch ($param['Key']) {
                case 'DebitAccountBalance':
                    $debitAccountBalance = $param['Value'];
                    break;
                case 'Amount':
                    $amount = $param['Value'];
                    break;
                case 'TransCompletedTime':
                    $completedTime = $param['Value'];
                    break;
                case 'OriginalTransactionID':
                    $originalTransactionId = $param['Value'];
                    break;
                case 'Charge':
                    $charge = $param['Value'];
                    break;
                case 'CreditPartyPublicName':
                    $creditPartyName = $param['Value'];
                    break;
            }
        }
    }
    
    echo "Transaction ID: $transactionId\n";
    echo "Original Transaction ID: $originalTransactionId\n";
    echo "Amount: $amount\n";
    echo "Charge: $charge\n";
    echo "Completed Time: $completedTime\n";
    echo "Credit Party: $creditPartyName\n";
    
    // Parse the account balance string
    if ($debitAccountBalance) {
        echo "Debit Account Balance:\n";
        $parts = explode('|', $debitAccountBalance);
        if (count($parts) >= 3) {
            $accountName = $parts[0];
            $currency = $parts[1];
            $balance = $parts[2];
            
            echo "- $accountName: $currency $balance\n";
        }
    }
    
    echo "Result: $resultDesc (Code: $resultCode)\n";
    
    // In a real application, you would:
    // 1. Update your database to mark the transaction as reversed
    // 2. Notify the user of the reversal
    // 3. Take appropriate action based on the reversal
    
    // Send a success response back to Mpesa
    echo "\nResponse to Mpesa: " . $webhook->generateSuccessResponse('Transaction reversal received successfully') . "\n";
} else {
    echo "Not a transaction reversal callback\n";
}