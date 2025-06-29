<?php
/**
 * C2B Example
 *
 * This example demonstrates how to use the Mpesa API SDK to register C2B URLs
 * and simulate a C2B transaction (simulation only works in sandbox).
 */

require_once __DIR__ . '/../bootstrap.php';

use Rndwiga\Mpesa\MpesaAPI;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Rndwiga\Mpesa\Api\C2B;

// Create a logger
$logger = new Logger('mpesa');
$logger->pushHandler(new StreamHandler(__DIR__ . '/mpesa.log', Logger::DEBUG));

// Get credentials from environment variables
$consumerKey = getenv('MPESA_C2B_CONSUMER_KEY') ?: getenv('CONSUMER_KEY');
$consumerSecret = getenv('MPESA_C2B_CONSUMER_SECRETE') ?: getenv('CONSUMER_SECRET');
$shortcode = getenv('MPESA_C2B_SHORT_CODE') ?: getenv('PARTY_A');

// Initialize the Mpesa API with logger
$mpesa = new MpesaAPI(
    $consumerKey,
    $consumerSecret,
    getenv('MPESA_C2B_INTEGRATION_STATUS') === 'true' || getenv('APPLICATION_STATUS') === 'true', // false for sandbox, true for production
    $logger
);

// Part 1: Register URLs
echo "Registering C2B URLs...\n";

try {
    $response = $mpesa->c2b()
        ->setShortcode($shortcode)
        ->setResponseType(C2B::RESPONSE_TYPE_COMPLETED)
        ->setConfirmationUrl(getenv('MPESA_C2B_RESULT_URL') ?: getenv('RESULT_URL'))
        ->setValidationUrl(getenv('MPESA_CALLBACK_URL') ?: getenv('QUEUE_TIMEOUT_URL'))
        ->registerUrls();

    // Print the response
    echo "URL Registration Response:\n";
    print_r($response);

    // Check if the request was successful
    if (isset($response['success']) && $response['success']) {
        echo "\nC2B URLs registered successfully!\n";
        echo "Response Description: " . $response['data']->ResponseDescription . "\n";
    } else {
        echo "\nC2B URL registration failed!\n";
        if (isset($response['errorMessage'])) {
            echo "Error: " . $response['errorMessage'] . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Error registering URLs: " . $e->getMessage() . "\n";
}

// Part 2: Simulate a C2B transaction (only works in sandbox)
echo "\nSimulating C2B transaction...\n";

try {
    $response = $mpesa->c2b()
        ->setShortcode($shortcode)
        ->setCommandId(C2B::COMMAND_ID_CUSTOMER_PAYBILL_ONLINE)
        ->setAmount(100) // Amount in KES
        ->setMsisdn('254722000000') // Customer phone number
        ->setBillRefNumber('Test reference')
        ->simulate();

    // Print the response
    echo "Simulation Response:\n";
    print_r($response);

    // Check if the request was successful
    if (isset($response['success']) && $response['success']) {
        echo "\nC2B transaction simulated successfully!\n";
        echo "Response Description: " . $response['data']->ResponseDescription . "\n";
    } else {
        echo "\nC2B transaction simulation failed!\n";
        if (isset($response['errorMessage'])) {
            echo "Error: " . $response['errorMessage'] . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Error simulating transaction: " . $e->getMessage() . "\n";
}

// Part 3: Example of processing a C2B callback
echo "\nExample of processing a C2B callback:\n";

// This would normally come from the POST data in your callback URL
$sampleCallback = json_encode([
    'TransactionType' => 'Pay Bill',
    'TransID' => 'RKTQDM5HTY',
    'TransTime' => '20191122063845',
    'TransAmount' => '100',
    'BusinessShortCode' => $shortcode,
    'BillRefNumber' => 'Test reference',
    'InvoiceNumber' => '',
    'OrgAccountBalance' => '49197.00',
    'ThirdPartyTransID' => '',
    'MSISDN' => '254722000000',
    'FirstName' => 'John',
    'MiddleName' => 'Doe',
    'LastName' => ''
]);

// Using the new WebhookHandler to process the callback
$webhook = $mpesa->webhook($sampleCallback);
$webhook->parseCallback();

// Check if it's a C2B callback
if ($webhook->isCallbackType('c2b')) {
    echo "Received C2B callback\n";

    // Extract key information
    $transactionId = $webhook->getValue('TransID');
    $transactionType = $webhook->getValue('TransactionType');
    $transactionTime = $webhook->getValue('TransTime');
    $amount = $webhook->getValue('TransAmount');
    $shortcode = $webhook->getValue('BusinessShortCode');
    $billRefNumber = $webhook->getValue('BillRefNumber');
    $phoneNumber = $webhook->getValue('MSISDN');
    $firstName = $webhook->getValue('FirstName');

    echo "Transaction ID: $transactionId\n";
    echo "Transaction Type: $transactionType\n";
    echo "Transaction Time: $transactionTime\n";
    echo "Amount: $amount\n";
    echo "Business Shortcode: $shortcode\n";
    echo "Bill Reference Number: $billRefNumber\n";
    echo "Phone Number: $phoneNumber\n";
    echo "Customer Name: $firstName\n";

    // In a real application, you would:
    // 1. Verify the transaction against your records
    // 2. Update your database
    // 3. Fulfill the customer's order

    // Send a success response back to Mpesa
    echo "\nResponse to Mpesa: " . $webhook->generateSuccessResponse('C2B transaction processed successfully') . "\n";
} else {
    echo "Not a C2B callback\n";
}

// Part 4: Example of a validation callback handler
echo "\nExample of a validation callback handler:\n";

// This would be implemented in your validation URL endpoint
function handleValidationCallback($mpesa, $callbackData) {
    // In a real application, you would:
    // 1. Verify the account number (BillRefNumber)
    // 2. Check if the account is active
    // 3. Check if the amount is acceptable

    // For this example, we'll accept all transactions
    $isValid = true;

    if ($isValid) {
        // Accept the transaction
        return $mpesa->webhook()->generateSuccessResponse('Transaction is valid');
    } else {
        // Reject the transaction
        return $mpesa->webhook()->generateErrorResponse('Transaction is invalid', '1');
    }
}

// Simulate a validation callback
$validationCallback = json_encode([
    'TransactionType' => 'Pay Bill',
    'TransID' => 'VKQDM5HTY',
    'TransTime' => '20191122063845',
    'TransAmount' => '100',
    'BusinessShortCode' => $shortcode,
    'BillRefNumber' => 'Test reference',
    'MSISDN' => '254722000000'
]);

echo "Validation response: " . handleValidationCallback($mpesa, $validationCallback) . "\n";
