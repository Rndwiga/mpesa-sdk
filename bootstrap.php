<?php
require __DIR__ . '/vendor/autoload.php';
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 8/5/19
 * Time: 8:45 AM
 */

bootstrap();

function bootstrap(){
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();
}