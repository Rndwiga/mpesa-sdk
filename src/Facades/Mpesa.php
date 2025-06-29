<?php

namespace Rndwiga\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Rndwiga\Mpesa\Api\B2C b2c()
 * @method static \Rndwiga\Mpesa\Api\B2B b2b()
 * @method static \Rndwiga\Mpesa\Api\C2B c2b()
 * @method static \Rndwiga\Mpesa\Api\Express express()
 * @method static \Rndwiga\Mpesa\Api\Account account()
 * @method static \Rndwiga\Mpesa\Utils\WebhookHandler webhook(string $callbackData = null)
 * @method static string finishTransaction(string $message = 'success')
 * 
 * @see \Rndwiga\Mpesa\MpesaAPI
 */
class Mpesa extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mpesa';
    }
}