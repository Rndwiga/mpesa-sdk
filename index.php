<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 8/5/19
 * Time: 8:45 AM
 */

require __DIR__ . '/bootstrap.php';
use Ramsey\Uuid\Uuid;
use Rndwiga\Mpesa\Libraries\B2C\MpesaB2CCalls;
use Rndwiga\Toolbox\Infrastructure\Services\AppLogger;
$mpesa = new MpesaRequest();
print_r($mpesa->b2cRequestV2());

class MpesaRequest {

  public  function b2cRequestV2(){
      $uuid = Uuid::uuid4();
        $response = (new MpesaB2CCalls())
            ->setApplicationStatus(false)
            ->setInitiatorName(env('INITIATOR_NAME'))
            ->setSecurityCredential(env('SECURITY_CREDENTIAL'))
            ->setConsumerKey(env('CONSUMER_KEY'))
            ->setConsumerSecret(env('CONSUMER_SECRET'))
            ->setCommandId(env('COMMAND_ID'))
            ->setAmount(10)
            ->setPartyA(env('PARTY_A'))
            ->setPartyB(254708374149)
            ->setRemarks("loan disbursement")
            ->setOccasion("funds management")
            ->setQueueTimeOutUrl(env('QUEUE_TIMEOUT_URL'))
            ->setResultUrl(env('RESULT_URL'))
            ->makeB2cCallV2($uuid->toString());

      (new AppLogger('b2cRequest','b2c_request'))->logInfo(json_decode($response,true));
        return $response;
    }
    public  function b2cRequestV1(){
      $uuid = Uuid::uuid4();
        $response = (new MpesaB2CCalls())
            ->setApplicationStatus(false)
            ->setInitiatorName(env('INITIATOR_NAME'))
            ->setSecurityCredential(env('SECURITY_CREDENTIAL'))
            ->setConsumerKey(env('CONSUMER_KEY'))
            ->setConsumerSecret(env('CONSUMER_SECRET'))
            ->setCommandId(env('COMMAND_ID'))
            ->setAmount(10)
            ->setPartyA(env('PARTY_A'))
            ->setPartyB(254708374149)
            ->setRemarks("loan disbursement")
            ->setOccasion("funds management")
            ->setQueueTimeOutUrl(env('QUEUE_TIMEOUT_URL'))
            ->setResultUrl(env('RESULT_URL'))
            ->makeB2cCall($uuid->toString());

      (new AppLogger('b2cRequest','b2c_request'))->logInfo(json_decode($response,true));
        return $response;
    }
}