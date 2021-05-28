<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 9/13/18
 * Time: 12:09 PM
 * @param null $authTheme
 * @param null $frontendTheme
 * @return \Illuminate\Config\Repository|mixed
 */

use Illuminate\Support\Facades\Session;

    if (! function_exists('storagePath')){
         function storagePath(string $path = null){
            if (function_exists('storage_path')){
                return storage_path($path);
            }
            return __DIR__ . $path;
            //return  dirname(__DIR__,3);
        }
    }

    if (! function_exists('env')){
         function env(string $variable){
             return getenv($variable);
        }
    }

    if (! function_exists('str_slug')){
        function str_slug($string){
            return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
        }
    }