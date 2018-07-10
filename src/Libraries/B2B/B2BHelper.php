<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 5/8/18
 * Time: 12:44 PM
 */

namespace Rndwiga\Mpesa\Libraries\B2B;

use Rndwiga\Payment\Gateway\Libraries\Mpesa\B2B\MpesaB2BCalls;

class B2BHelper
{
    private $mpesaB2BCalls;

    public function __construct()
    {
        $this->mpesaB2BCalls = new MpesaB2BCalls();
    }

    public function sendFundsToBusiness(){

        $response = $this->mpesaB2BCalls->setPartyB("600000")
            ->setCommandID("BusinessToBusinessTransfer")
            ->setSenderIdentifierType("4")
            ->setRecieverIdentifierType("4")
            ->setAccountReference("BusinessPaybill")
            ->setRemarks("Business Payment To Business")
            ->b2b(100);
        return $response;
    }
}