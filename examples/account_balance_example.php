<?php
/**
 * Account Balance Example
 *
 * This example demonstrates how to use the Mpesa API SDK to check account balance.
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

// Initialize the Mpesa API with logger
$mpesa = new MpesaAPI(
    $consumerKey,
    $consumerSecret,
    false, // false for sandbox, true for production
    $logger
);

// Check account balance
echo "Checking account balance...\n";

try {
    $response = $mpesa->account()
        ->setInitiatorName($initiatorName)
        ->setSecurityCredential($initiatorPassword)
        ->setPartyA($shortcode)
        ->setIdentifierType(Account::IDENTIFIER_TYPE_SHORTCODE)
        ->setRemarks('Test balance check')
        ->setQueueTimeoutUrl('https://example.com/timeout')
        ->setResultUrl('https://example.com/result')
        ->checkBalance();

    // Print the response
    echo "Response:\n";
    print_r($response);

    // Check if the request was successful
    if (isset($response['success']) && $response['success']) {
        echo "\nAccount balance check initiated successfully!\n";
        echo "Conversation ID: " . $response['data']->ConversationID . "\n";
        echo "Originator Conversation ID: " . $response['data']->OriginatorConversationID . "\n";
        echo "Response Description: " . $response['data']->ResponseDescription . "\n";
    } else {
        echo "\nAccount balance check failed!\n";
        if (isset($response['errorMessage'])) {
            echo "Error: " . $response['errorMessage'] . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Error checking account balance: " . $e->getMessage() . "\n";
}

// Example of processing an account balance callback
echo "\nExample of processing an account balance callback:\n";

// This would normally come from the POST data in your callback URL
$sampleCallback = json_encode([
    'Result' => [
        'ResultType' => 0,
        'ResultCode' => 0,
        'ResultDesc' => 'The service request is processed successfully.',
        'OriginatorConversationID' => '29112-34567890-1',
        'ConversationID' => 'AG_20191219_00007fc37fc37fc37fc3',
        'TransactionID' => 'LGH3197RIB',
        'ResultParameters' => [
            'ResultParameter' => [
                [
                    'Key' => 'AccountBalance',
                    'Value' => 'Working Account|KES|481000.00|481000.00|0.00|0.00&Float Account|KES|0.00|0.00|0.00|0.00&Utility Account|KES|0.00|0.00|0.00|0.00&Charges Paid Account|KES|-481000.00|-481000.00|0.00|0.00&Organization Settlement Account|KES|0.00|0.00|0.00|0.00'
                ],
                [
                    'Key' => 'BOCompletedTime',
                    'Value' => '20191219120000'
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

// Check if it's an account balance callback
if ($webhook->isCallbackType('balance')) {
    echo "Received account balance callback\n";
    
    // Extract key information
    $resultCode = $webhook->getValue('Result.ResultCode');
    $resultDesc = $webhook->getValue('Result.ResultDesc');
    
    // Find specific parameters using dot notation
    $balanceInfo = null;
    $completedTime = null;
    $parameters = $webhook->getValue('Result.ResultParameters.ResultParameter');
    
    if (is_array($parameters)) {
        foreach ($parameters as $param) {
            if ($param['Key'] === 'AccountBalance') {
                $balanceInfo = $param['Value'];
            } elseif ($param['Key'] === 'BOCompletedTime') {
                $completedTime = $param['Value'];
            }
        }
    }
    
    echo "Result: $resultDesc (Code: $resultCode)\n";
    echo "Completed Time: $completedTime\n";
    
    // Parse the account balance string
    if ($balanceInfo) {
        echo "Account Balances:\n";
        $accounts = explode('&', $balanceInfo);
        
        foreach ($accounts as $account) {
            $parts = explode('|', $account);
            if (count($parts) >= 3) {
                $accountName = $parts[0];
                $currency = $parts[1];
                $balance = $parts[2];
                
                echo "- $accountName: $currency $balance\n";
            }
        }
    }
    
    // Send a success response back to Mpesa
    echo "\nResponse to Mpesa: " . $webhook->generateSuccessResponse('Account balance received successfully') . "\n";
} else {
    echo "Not an account balance callback\n";
}