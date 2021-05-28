<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 8/5/19
 * Time: 8:45 AM
 */

require __DIR__ . '/bootstrap.php';
use Ramsey\Uuid\Uuid;
use Rndwiga\Mpesa\Libraries\Account\Account\MpesaAccountBalance;
use Rndwiga\Mpesa\Libraries\B2C\MpesaB2CCalls;
use Rndwiga\Toolbox\Infrastructure\Services\AppLogger;
$mpesa = new MpesaRequest();
print_r($mpesa->b2cRequest());

class MpesaRequest {

    public function appPath(){
        return storagePath();
    }

    public  function b2cRequest(){
      $response = (new MpesaB2CCalls())->sampleRequest();
      (new AppLogger('b2cRequest','b2c_request'))->logInfo(json_decode($response,true));
        return $response;
    }

    public function balanceRequest(){
       $response = (new MpesaAccountBalance())->sampleRequest();
        (new AppLogger('b2cRequest','account_balance'))->logInfo(json_decode($response,true));
        return $response;
    }


}