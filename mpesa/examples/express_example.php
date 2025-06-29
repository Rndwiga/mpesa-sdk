<?php
/**
 * Express (STK Push) Example
 *
 * This example demonstrates how to use the Mpesa API SDK to initiate an STK push.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Rndwiga\Mpesa\MpesaAPI;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Rndwiga\Mpesa\Api\Express;
use Rndwiga\Mpesa\Utils\FileCache;

// Create a logger
$logger = new Logger('mpesa');
$logger->pushHandler(new StreamHandler(__DIR__ . '/mpesa.log', Logger::DEBUG));

// Create a cache
$cache = new FileCache(__DIR__ . '/cache');

// Replace these with your actual credentials
$consumerKey = 'your_consumer_key';
$consumerSecret = 'your_consumer_secret';
$businessShortCode = 'your_business_shortcode';
$passkey = 'your_passkey';
$phoneNumber = '254722000000';

// Initialize the Mpesa API with logger and cache
$mpesa = new MpesaAPI(
    $consumerKey,
    $consumerSecret,
    false, // false for sandbox, true for production
    $logger,
    $cache
);

// Initiate an STK push
echo "Initiating STK push...\n";

try {
    $response = $mpesa->express()
        ->setBusinessShortCode($businessShortCode)
        ->setPasskey($passkey)
        ->setTimestamp() // Will use current time
        ->setPassword() // Will generate password from shortcode, passkey, and timestamp
        ->setTransactionType(Express::TRANSACTION_TYPE_CUSTOMER_PAYBILL_ONLINE)
        ->setAmount(1) // Amount in KES
        ->setPartyA($phoneNumber)
        ->setPartyB($businessShortCode)
        ->setPhoneNumber($phoneNumber)
        ->setCallbackUrl('https://example.com/callback')
        ->setAccountReference('Test Reference')
        ->setTransactionDesc('Test Payment')
        ->push();

    // Print the response
    echo "Response:\n";
    print_r($response);

    // Check if the request was successful
    if (isset($response['success']) && $response['success']) {
        echo "\nSTK push initiated successfully!\n";

        if (isset($response['data']->CheckoutRequestID)) {
            $checkoutRequestId = $response['data']->CheckoutRequestID;
            echo "Checkout Request ID: " . $checkoutRequestId . "\n";
            echo "Merchant Request ID: " . $response['data']->MerchantRequestID . "\n";
            echo "Response Description: " . $response['data']->ResponseDescription . "\n";

            // Query the status after a few seconds
            echo "\nWaiting for 5 seconds before checking status...\n";
            sleep(5);

            $statusResponse = $mpesa->express()
                ->setBusinessShortCode($businessShortCode)
                ->setPasskey($passkey)
                ->query($checkoutRequestId);

            echo "Status Response:\n";
            print_r($statusResponse);

            // Check the status response
            if (isset($statusResponse['success']) && $statusResponse['success']) {
                echo "\nStatus check successful!\n";
                echo "Result Code: " . $statusResponse['data']->ResultCode . "\n";
                echo "Result Description: " . $statusResponse['data']->ResultDesc . "\n";
            } else {
                echo "\nStatus check failed!\n";
                if (isset($statusResponse['errorMessage'])) {
                    echo "Error: " . $statusResponse['errorMessage'] . "\n";
                }
            }
        }
    } else {
        echo "\nSTK push failed!\n";
        if (isset($response['errorMessage'])) {
            echo "Error: " . $response['errorMessage'] . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example of processing a callback
echo "\nExample of processing a callback:\n";

// This would normally come from the POST data in your callback URL
$sampleCallback = json_encode([
    'Body' => [
        'stkCallback' => [
            'MerchantRequestID' => '29115-34620561-1',
            'CheckoutRequestID' => 'ws_CO_191220191020363925',
            'ResultCode' => 0,
            'ResultDesc' => 'The service request is processed successfully.',
            'CallbackMetadata' => [
                'Item' => [
                    [
                        'Name' => 'Amount',
                        'Value' => 1.00
                    ],
                    [
                        'Name' => 'MpesaReceiptNumber',
                        'Value' => 'NLJ7RT61SV'
                    ],
                    [
                        'Name' => 'TransactionDate',
                        'Value' => 20191219102115
                    ],
                    [
                        'Name' => 'PhoneNumber',
                        'Value' => 254722000000
                    ]
                ]
            ]
        ]
    ]
]);

// Using the new WebhookHandler to process the callback
$webhook = $mpesa->webhook($sampleCallback);
$webhook->parseCallback();

// Check if it's an Express callback
if ($webhook->isCallbackType('express')) {
    echo "Received Express (STK Push) callback\n";

    // Extract key information
    $resultCode = $webhook->getValue('Body.stkCallback.ResultCode');
    $resultDesc = $webhook->getValue('Body.stkCallback.ResultDesc');
    $merchantRequestId = $webhook->getValue('Body.stkCallback.MerchantRequestID');
    $checkoutRequestId = $webhook->getValue('Body.stkCallback.CheckoutRequestID');

    echo "Merchant Request ID: $merchantRequestId\n";
    echo "Checkout Request ID: $checkoutRequestId\n";

    if ($resultCode == 0) {
        // Success
        echo "Transaction was successful!\n";

        // Extract metadata using dot notation
        $items = $webhook->getValue('Body.stkCallback.CallbackMetadata.Item');

        $amount = null;
        $receiptNumber = null;
        $transactionDate = null;
        $phoneNumber = null;

        if (is_array($items)) {
            foreach ($items as $item) {
                switch ($item['Name']) {
                    case 'Amount':
                        $amount = $item['Value'];
                        break;
                    case 'MpesaReceiptNumber':
                        $receiptNumber = $item['Value'];
                        break;
                    case 'TransactionDate':
                        $transactionDate = $item['Value'];
                        break;
                    case 'PhoneNumber':
                        $phoneNumber = $item['Value'];
                        break;
                }
            }
        }

        echo "Amount: $amount\n";
        echo "Receipt Number: $receiptNumber\n";
        echo "Transaction Date: $transactionDate\n";
        echo "Phone Number: $phoneNumber\n";

        // In a real application, you would:
        // 1. Verify the transaction against your records
        // 2. Update your database
        // 3. Fulfill the customer's order
    } else {
        // Failed
        echo "Transaction failed: $resultDesc (Code: $resultCode)\n";

        // In a real application, you would:
        // 1. Log the failure
        // 2. Notify the user
        // 3. Take appropriate action based on the error
    }

    // Send a success response back to Mpesa
    echo "\nResponse to Mpesa: " . $webhook->generateSuccessResponse('STK callback processed successfully') . "\n";
} else {
    echo "Not an Express callback\n";
}
