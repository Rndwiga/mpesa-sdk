<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 8/5/19
 * Time: 8:45 AM
 */

require __DIR__ . '/bootstrap.php';
use Ramsey\Uuid\Uuid;
use Rndwiga\Mpesa\MpesaAPI;
use Rndwiga\Mpesa\Api\B2C;
use Rndwiga\Mpesa\Api\Account;
use Rndwiga\Mpesa\Utils\Logger;
use Rndwiga\Mpesa\Utils\FileCache;
$mpesa = new MpesaRequest();
print_r($mpesa->b2cRequest());

class MpesaRequest {

    public function appPath(){
        return storagePath();
    }

    public function b2cRequest(){
        // Create a logger
        $logger = new Logger('b2cRequest', 'b2c_request');

        // Create a cache
        $cache = new FileCache(__DIR__ . '/cache');

        // Initialize the Mpesa API
        $mpesa = new MpesaAPI(
            getenv('CONSUMER_KEY'),
            getenv('CONSUMER_SECRET'),
            getenv('APPLICATION_STATUS') === 'true', // false for sandbox, true for production
            $logger,
            $cache
        );

        try {
            $response = $mpesa->b2c()
                ->setInitiatorName(getenv('INITIATOR_NAME'))
                ->setSecurityCredential(getenv('SECURITY_CREDENTIAL'))
                ->setCommandId(getenv('COMMAND_ID') ?: B2C::COMMAND_ID_BUSINESS_PAYMENT)
                ->setAmount(100) // Amount in KES
                ->setPartyA(getenv('PARTY_A'))
                ->setPartyB('254722000000')
                ->setRemarks('Test payment')
                ->setQueueTimeoutUrl(getenv('QUEUE_TIMEOUT_URL'))
                ->setResultUrl(getenv('RESULT_URL'))
                ->setOccasion('Test occasion')
                ->makePayment();

            $logger->info('B2C Request', is_array($response) ? $response : ['response' => $response]);
            return $response;
        } catch (\Exception $e) {
            $logger->error('B2C Request Error', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function balanceRequest(){
        // Create a logger
        $logger = new Logger('accountBalance', 'account_balance');

        // Create a cache
        $cache = new FileCache(__DIR__ . '/cache');

        // Initialize the Mpesa API
        $mpesa = new MpesaAPI(
            getenv('CONSUMER_KEY'),
            getenv('CONSUMER_SECRET'),
            getenv('APPLICATION_STATUS') === 'true', // false for sandbox, true for production
            $logger,
            $cache
        );

        try {
            $response = $mpesa->account()
                ->setInitiatorName(getenv('INITIATOR_NAME'))
                ->setSecurityCredential(getenv('SECURITY_CREDENTIAL'))
                ->setPartyA(getenv('PARTY_A'))
                ->setIdentifierType(Account::IDENTIFIER_TYPE_SHORTCODE)
                ->setRemarks('Test balance check')
                ->setQueueTimeoutUrl(getenv('QUEUE_TIMEOUT_URL'))
                ->setResultUrl(getenv('RESULT_URL'))
                ->checkBalance();

            $logger->info('Account Balance', is_array($response) ? $response : ['response' => $response]);
            return $response;
        } catch (\Exception $e) {
            $logger->error('Account Balance Error', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }


}
