<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gateway Config File
    |--------------------------------------------------------------------------
    |
    */
    'module' => [
        'name' => 'Gateway',
        'middle_wares' => [],
        'namespace' => 'Tyondo\Cirembo\Modules\Gateway',
        'payment_gateway' => [
            'mpesa' => [
                'application_status' => 'sandbox', //[live or sandbox]
                'live' => [
                    'short_code' => '964558',
                    'initiator_name' => '',
                    'security_credentials_password' => '',
                    'consumer_key' => 'C8dVAkZPW2lwId1Kex92fMRmFinj5bEH',
                    'consumer_secret' => 'T5pf7vkzGKrnaKVG',
                    'confirmation_url' => 'https://cirembo.ciremboframework.com/tools/gateway/webhook/mpesa/confirmation',
                    'validation_url' => 'https://cirembo.ciremboframework.com/tools/gateway/webhook/mpesa/validation',
                    'b2c_queue_time_out_url' => 'https://demo.ciremboframework.com/tools/gateway/webhook/mpesa/queue/timeout',
                    'b2c_result_url' => 'https://demo.ciremboframework.com/tools/gateway/webhook/mpesa/result/url',
                ],
                'test' => [
                    'short_code' => 600448, //this the testing short_code
                    'initiator_name' => 'testWeb',
                    'security_credentials_password' => '12qw',
                    'consumer_key' => 'Ckhb9a0Xa1RTyYNg9JXz7MfssBsO3pJb',
                    'consumer_secret' => '9OV2pHOClWy1ccyH',
                    'confirmation_url' => 'https://demo.ciremboframework.com/tools/gateway/webhook/mpesa/confirmation',
                    'validation_url' => 'https://demo.ciremboframework.com/tools/gateway/webhook/mpesa/validation',
                    'b2c_queue_time_out_url' => 'https://demo.ciremboframework.com/tools/gateway/webhook/mpesa/queue/timeout',
                    'b2c_result_url' => 'https://demo.ciremboframework.com/tools/gateway/webhook/mpesa/result/url',
                ]
            ],
        ],
        'gateway' => [
            'mpesa' => [
                'b2c' => [
                    'appName' => '',
                    /** @is_live true/false */
                    "is_live"=>false,
                    /** @InitiatorName */
                    "InitiatorName"=>"apitest489",

                    /** @InitiatorPassword */
                    "InitiatorPassword"=>"489reset",

                    /** @B2cShortCode */
                    'B2cShortCode'=>"601489",

                    /** @B2cConsumerKey */
                    'B2cConsumerKey'=>'3Glsg2Ax0G3UdGGJNZX5hiStbAml3C4y',

                    /** @B2cConsumerSecret */
                    'B2cConsumerSecret'=>'wx73pd4jSgyZwkKE',

                    "QueueTimeOutURL" => "https://mmt.musoni.co.ke/api/tools/gateway/mpesa/b2c/webhook/queue",
                    "ResultURL"=>"https://mmt.musoni.co.ke/api/tools/gateway/mpesa/b2c/webhook/result",
                ],
                'b2b' => [
                    'appName' => '',
                    /** @is_live true/false */
                    "is_live"=>false,
                    /** @InitiatorName */
                    "InitiatorName"=>"apitest489",

                    /** @InitiatorPassword */
                    "InitiatorPassword"=>"489reset",

                    /** @B2cShortCode */
                    'B2bShortCode'=>"601489",

                    /** @B2cConsumerKey */
                    'B2bConsumerKey'=>'54Y10pAIeAc4lwSnZ7SvPaUvUVDPzxLa',

                    /** @B2cConsumerSecret */
                    'B2bConsumerSecret'=>'I3aobDvVh9KtLWxX',

                    "QueueTimeOutURL" => "https://mmt.musoni.co.ke/api/tools/gateway/mpesa/b2c/webhook/queue",
                    "ResultURL"=>"https://mmt.musoni.co.ke/api/tools/gateway/mpesa/b2c/webhook/result",
                ],

                'c2b' => [
                    /** @is_live true/false */
                    "is_live"=>false,
                    /** @InitiatorName */
                    "InitiatorName"=>"Maggie",

                    /** @InitiatorPassword */
                    "InitiatorPassword"=>"123456",

                    /** @B2cShortCode */
                    "C2bShortCode"=>"55555",

                    /** @C2bConsumerKey */
                    'C2bConsumerKey'=>'3Glsg2Ax0G3UdGGJNZX5hiStbAml3C4y',

                    /** @C2bConsumerSecret */
                    'C2bConsumerSecret'=>'hhdGFHDgjdcscasd',

                    "QueueTimeOutURL" => "",
                    "ResultURL"=>"",
                    "CallBackURL"=>"",
                    "PassKey"=>""
                ]
            ],
            'live' => [
                'b2c' => [
                    'appName' => '',
                    /** @is_live true/false */
                    "is_live"=>false,
                    /** @InitiatorName */
                    "InitiatorName"=>"TestInit593",

                    /** @InitiatorPassword */
                    "InitiatorPassword"=>"12345678",

                    /** @B2cShortCode */
                    'B2cShortCode'=>"600593",

                    /** @B2cConsumerKey */
                    'B2cConsumerKey'=>'3Glsg2Ax0G3UdGGJNZX5hiStbAml3C4y',

                    /** @B2cConsumerSecret */
                    'B2cConsumerSecret'=>'wx73pd4jSgyZwkKE',

                    "QueueTimeOutURL" => "https://webhook.site/762f5cc8-d8b0-4bd6-af61-438994fbea9a",
                    "ResultURL"=>"https://webhook.site/6cb22bbf-7f9a-4894-8809-c06e797d64fa",
                ],
                'c2b' => [
                    /** @is_live true/false */
                    "is_live"=>false,
                    /** @InitiatorName */
                    "InitiatorName"=>"Maggie",

                    /** @InitiatorPassword */
                    "InitiatorPassword"=>"123456",

                    /** @B2cShortCode */
                    "C2bShortCode"=>"55555",

                    /** @C2bConsumerKey */
                    'C2bConsumerKey'=>'3Glsg2Ax0G3UdGGJNZX5hiStbAml3C4y',

                    /** @C2bConsumerSecret */
                    'C2bConsumerSecret'=>'hhdGFHDgjdcscasd',

                    "QueueTimeOutURL" => "",
                    "ResultURL"=>"",
                    "CallBackURL"=>"",
                    "PassKey"=>""
                ]
            ]
        ]
    ],


];
