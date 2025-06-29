<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-Pesa API Credentials
    |--------------------------------------------------------------------------
    |
    | Here you may configure your M-Pesa API credentials. These values will be used
    | when connecting to the M-Pesa API. You can get these credentials from the
    | Safaricom Developer Portal.
    |
    */
    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the environment to use for the M-Pesa API.
    | Set to true for production and false for sandbox.
    |
    */
    'production' => env('MPESA_PRODUCTION', false),
    
    /*
    |--------------------------------------------------------------------------
    | Business Credentials
    |--------------------------------------------------------------------------
    |
    | Here you may configure your business credentials for M-Pesa transactions.
    |
    */
    'initiator_name' => env('MPESA_INITIATOR_NAME', ''),
    'initiator_password' => env('MPESA_INITIATOR_PASSWORD', ''),
    'shortcode' => env('MPESA_SHORTCODE', ''),
    'business_shortcode' => env('MPESA_BUSINESS_SHORTCODE', ''),
    'passkey' => env('MPESA_PASSKEY', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    |
    | Here you may configure your callback URLs for M-Pesa transactions.
    |
    */
    'callback_url' => env('MPESA_CALLBACK_URL', ''),
    'timeout_url' => env('MPESA_TIMEOUT_URL', ''),
    'result_url' => env('MPESA_RESULT_URL', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure the storage settings for M-Pesa transactions.
    |
    */
    'storage' => [
        'use_date' => true,
        'root_folder' => 'mpesa',
        'log_folder' => 'logs',
    ],
];