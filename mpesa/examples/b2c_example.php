<?php
/**
 * B2C Example
 *
 * This example demonstrates how to use the Mpesa API SDK to make a B2C payment.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Rndwiga\Mpesa\MpesaAPI;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Rndwiga\Mpesa\Api\B2C;
use Rndwiga\Mpesa\Utils\FileCache;

// Create a logger
$logger = new Logger('mpesa');
$logger->pushHandler(new StreamHandler(__DIR__ . '/mpesa.log', Logger::DEBUG));

// Create a cache
$cache = new FileCache(__DIR__ . '/cache');

// Replace these with your actual credentials
$consumerKey = 'your_consumer_key';
$consumerSecret = 'your_consumer_secret';
$initiatorName = 'your_initiator_name';
$initiatorPassword = 'your_initiator_password';
$shortcode = 'your_shortcode'; // PartyA
$phoneNumber = '254722000000'; // PartyB

// Initialize the Mpesa API with logger and cache
$mpesa = new MpesaAPI(
    $consumerKey,
    $consumerSecret,
    false, // false for sandbox, true for production
    $logger,
    $cache
);

// Make a B2C payment
echo "Making B2C payment...\n";

try {
    $response = $mpesa->b2c()
        ->setInitiatorName($initiatorName)
        ->setSecurityCredential($initiatorPassword)
        ->setCommandId(B2C::COMMAND_ID_BUSINESS_PAYMENT)
        ->setAmount(100) // Amount in KES
        ->setPartyA($shortcode)
        ->setPartyB($phoneNumber)
        ->setRemarks('Test payment')
        ->setQueueTimeoutUrl('https://example.com/timeout')
        ->setResultUrl('https://example.com/result')
        ->setOccasion('Test occasion')
        ->makePayment();

    // Print the response
    echo "Response:\n";
    print_r($response);

    // Check if the request was successful
    if (isset($response['success']) && $response['success']) {
        echo "\nB2C payment initiated successfully!\n";
        echo "Conversation ID: " . $response['data']->ConversationID . "\n";
        echo "Originator Conversation ID: " . $response['data']->OriginatorConversationID . "\n";
        echo "Response Description: " . $response['data']->ResponseDescription . "\n";
    } else {
        echo "\nB2C payment failed!\n";
        if (isset($response['errorMessage'])) {
            echo "Error: " . $response['errorMessage'] . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example of processing a B2C callback
echo "\nExample of processing a B2C callback:\n";

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
                    'Value' => '254722000000 - John Doe'
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

// Check if it's a B2C callback
if ($webhook->isCallbackType('b2c')) {
    echo "Received B2C callback\n";

    // Extract key information
    $resultCode = $webhook->getValue('Result.ResultCode');
    $resultDesc = $webhook->getValue('Result.ResultDesc');
    $transactionId = $webhook->getValue('Result.TransactionID');

    // Find specific parameters using dot notation
    $amount = null;
    $receipt = null;
    $recipientName = null;
    $completedTime = null;

    $parameters = $webhook->getValue('Result.ResultParameters.ResultParameter');

    if (is_array($parameters)) {
        foreach ($parameters as $param) {
            switch ($param['Key']) {
                case 'TransactionAmount':
                    $amount = $param['Value'];
                    break;
                case 'TransactionReceipt':
                    $receipt = $param['Value'];
                    break;
                case 'ReceiverPartyPublicName':
                    $recipientName = $param['Value'];
                    break;
                case 'TransactionCompletedDateTime':
                    $completedTime = $param['Value'];
                    break;
            }
        }
    }

    echo "Transaction ID: $transactionId\n";
    echo "Receipt: $receipt\n";
    echo "Amount: $amount\n";
    echo "Recipient: $recipientName\n";
    echo "Completed Time: $completedTime\n";
    echo "Result: $resultDesc (Code: $resultCode)\n";

    // In a real application, you would:
    // 1. Verify the transaction against your records
    // 2. Update your database
    // 3. Notify the user of the payment

    // Send a success response back to Mpesa
    echo "\nResponse to Mpesa: " . $webhook->generateSuccessResponse('B2C transaction processed successfully') . "\n";
} else {
    echo "Not a B2C callback\n";
}
