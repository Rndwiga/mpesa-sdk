<?php

namespace Rndwiga\Mpesa;

use Illuminate\Support\ServiceProvider;
use Rndwiga\Mpesa\MpesaAPI;

class MpesaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/mpesa.php', 'mpesa'
        );

        $this->app->singleton('mpesa', function ($app) {
            $config = $app['config']['mpesa'];

            return new MpesaAPI(
                $config['consumer_key'] ?? env('MPESA_CONSUMER_KEY'),
                $config['consumer_secret'] ?? env('MPESA_CONSUMER_SECRET'),
                $config['production'] ?? false,
                $app->make('log'),
                null // You can implement a Laravel cache adapter here if needed
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/mpesa.php' => config_path('mpesa.php'),
        ], 'mpesa-config');
    }
}
