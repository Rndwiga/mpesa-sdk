<?php
/**
 * Example of using the WebhookHandler to process M-Pesa callbacks
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Rndwiga\Mpesa\MpesaAPI;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create a logger
$logger = new Logger('mpesa');
$logger->pushHandler(new StreamHandler(__DIR__ . '/mpesa.log', Logger::DEBUG));

// Initialize the Mpesa API
$mpesa = new MpesaAPI(
    'your_consumer_key',
    'your_consumer_secret',
    false, // false for sandbox, true for production
    $logger
);

// This is a sample callback handler that would be used in your webhook endpoint
function handleMpesaCallback($mpesa) {
    try {
        // Create a webhook handler and capture the callback data
        $webhook = $mpesa->webhook();
        $webhook->captureCallback()->parseCallback();
        
        // Log the received callback
        echo "Received callback data: " . json_encode($webhook->getData()) . "\n";
        
        // Determine the type of callback
        if ($webhook->isCallbackType('c2b')) {
            // Process C2B callback
            $transactionId = $webhook->getValue('TransID');
            $amount = $webhook->getValue('TransAmount');
            $phoneNumber = $webhook->getValue('MSISDN');
            
            echo "Received C2B payment of KES {$amount} from {$phoneNumber}, transaction ID: {$transactionId}\n";
            
            // Here you would typically:
            // 1. Verify the transaction
            // 2. Update your database
            // 3. Fulfill the customer's order
            
        } elseif ($webhook->isCallbackType('express')) {
            // Process STK Push callback
            $resultCode = $webhook->getValue('Body.stkCallback.ResultCode');
            $resultDesc = $webhook->getValue('Body.stkCallback.ResultDesc');
            
            if ($resultCode == 0) {
                // Success
                $amount = $webhook->getValue('Body.stkCallback.CallbackMetadata.Item.0.Value');
                $mpesaReceiptNumber = $webhook->getValue('Body.stkCallback.CallbackMetadata.Item.1.Value');
                $transactionDate = $webhook->getValue('Body.stkCallback.CallbackMetadata.Item.2.Value');
                $phoneNumber = $webhook->getValue('Body.stkCallback.CallbackMetadata.Item.3.Value');
                
                echo "Successful STK payment of KES {$amount} from {$phoneNumber}, receipt: {$mpesaReceiptNumber}\n";
            } else {
                // Failed
                echo "STK Push failed: {$resultDesc} (Code: {$resultCode})\n";
            }
        } elseif ($webhook->isCallbackType('b2c')) {
            // Process B2C callback
            echo "Received B2C callback\n";
            
            // Extract relevant data from the callback
            // ...
            
        } else {
            echo "Received unknown callback type\n";
        }
        
        // Send a success response back to Mpesa
        return $webhook->generateSuccessResponse('Transaction processed successfully');
        
    } catch (\Exception $e) {
        // Log the error
        echo "Error processing callback: " . $e->getMessage() . "\n";
        
        // You might want to return an error response in some cases
        // return $webhook->generateErrorResponse('Transaction processing failed', '1');
        
        // Or still return success to acknowledge receipt
        return $mpesa->finishTransaction('Callback received with errors');
    }
}

// Simulate processing a callback
// In a real application, this would be called by your webhook endpoint

// Sample C2B callback data
$sampleC2BCallback = json_encode([
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

// To test with the sample data, you would:
// 1. Save this to a file
// 2. Use a tool like Postman to send a POST request to your webhook endpoint with this data
// 3. Or simulate it directly:

// Simulate receiving the callback
$_POST = json_decode($sampleC2BCallback, true);
$callbackData = file_put_contents('php://input', $sampleC2BCallback);

// Process the callback
$response = handleMpesaCallback($mpesa);
echo "\nResponse to Mpesa: " . $response . "\n";