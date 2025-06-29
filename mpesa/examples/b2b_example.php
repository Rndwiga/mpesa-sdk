<?php
/**
 * B2B Example
 *
 * This example demonstrates how to use the Mpesa API SDK to make a B2B payment.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Rndwiga\Mpesa\MpesaAPI;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Rndwiga\Mpesa\Api\B2B;

// Create a logger
$logger = new Logger('mpesa');
$logger->pushHandler(new StreamHandler(__DIR__ . '/mpesa.log', Logger::DEBUG));

// Replace these with your actual credentials
$consumerKey = 'your_consumer_key';
$consumerSecret = 'your_consumer_secret';
$initiatorName = 'your_initiator_name';
$initiatorPassword = 'your_initiator_password';
$senderShortcode = 'your_sender_shortcode'; // PartyA
$receiverShortcode = 'receiver_shortcode'; // PartyB

// Initialize the Mpesa API with logger
$mpesa = new MpesaAPI(
    $consumerKey,
    $consumerSecret,
    false, // false for sandbox, true for production
    $logger
);

try {
    // Make a B2B payment
    $response = $mpesa->b2b()
        ->setInitiatorName($initiatorName)
        ->setSecurityCredential($initiatorPassword)
        ->setCommandId(B2B::COMMAND_ID_BUSINESS_PAY_BILL)
        ->setSenderIdentifierType(B2B::SENDER_IDENTIFIER_SHORTCODE)
        ->setReceiverIdentifierType(B2B::RECEIVER_IDENTIFIER_SHORTCODE)
        ->setAmount(100) // Amount in KES
        ->setPartyA($senderShortcode)
        ->setPartyB($receiverShortcode)
        ->setAccountReference('Test reference')
        ->setRemarks('Test payment')
        ->setQueueTimeoutUrl('https://example.com/timeout')
        ->setResultUrl('https://example.com/result')
        ->makePayment();

    // Print the response
    echo "Response:\n";
    print_r($response);

    // Check if the request was successful
    if (isset($response['success']) && $response['success']) {
        echo "\nB2B payment initiated successfully!\n";
        echo "Conversation ID: " . $response['data']->ConversationID . "\n";
        echo "Originator Conversation ID: " . $response['data']->OriginatorConversationID . "\n";
        echo "Response Description: " . $response['data']->ResponseDescription . "\n";
    } else {
        echo "\nB2B payment failed!\n";
        if (isset($response['errorMessage'])) {
            echo "Error: " . $response['errorMessage'] . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example of processing a B2B callback
echo "\nExample of processing a B2B callback:\n";

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
                    'Key' => 'TransactionAmount',
                    'Value' => 100
                ],
                [
                    'Key' => 'TransactionReceipt',
                    'Value' => 'LGH3197RIB'
                ],
                [
                    'Key' => 'B2CRecipientIsRegisteredCustomer',
                    'Value' => 'Y'
                ],
                [
                    'Key' => 'B2CChargesPaidAccountAvailableFunds',
                    'Value' => -4510.00
                ],
                [
                    'Key' => 'ReceiverPartyPublicName',
                    'Value' => '254700000000 - Receiver Company'
                ],
                [
                    'Key' => 'TransactionCompletedDateTime',
                    'Value' => '19.12.2019 12:01:59'
                ],
                [
                    'Key' => 'B2CUtilityAccountAvailableFunds',
                    'Value' => 10116.00
                ],
                [
                    'Key' => 'B2CWorkingAccountAvailableFunds',
                    'Value' => 900000.00
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

// Check if it's a B2B callback
if ($webhook->isCallbackType('b2b')) {
    echo "Received B2B callback\n";
    
    // Extract key information
    $resultCode = $webhook->getValue('Result.ResultCode');
    $resultDesc = $webhook->getValue('Result.ResultDesc');
    $transactionId = $webhook->getValue('Result.TransactionID');
    
    // Find specific parameters using dot notation
    $amount = null;
    $receipt = null;
    $parameters = $webhook->getValue('Result.ResultParameters.ResultParameter');
    
    if (is_array($parameters)) {
        foreach ($parameters as $param) {
            if ($param['Key'] === 'TransactionAmount') {
                $amount = $param['Value'];
            } elseif ($param['Key'] === 'TransactionReceipt') {
                $receipt = $param['Value'];
            }
        }
    }
    
    echo "Transaction ID: $transactionId\n";
    echo "Amount: $amount\n";
    echo "Receipt: $receipt\n";
    echo "Result: $resultDesc (Code: $resultCode)\n";
    
    // Send a success response back to Mpesa
    echo "\nResponse to Mpesa: " . $webhook->generateSuccessResponse('B2B transaction processed successfully') . "\n";
} else {
    echo "Not a B2B callback\n";
}