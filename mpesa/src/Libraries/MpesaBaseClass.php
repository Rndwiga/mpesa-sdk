<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 10/30/18
 * Time: 5:40 PM
 */

namespace Rndwiga\Mpesa\Libraries;


class MpesaBaseClass
{
    public function processCompletedTime(string $timeDetails){
        return (new \DateTime($timeDetails))->format('Y-m-d H:i:s'); //2018-10-14 14:24:28
    }
}