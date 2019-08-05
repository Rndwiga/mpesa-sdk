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

if (! function_exists('getActiveTheme'))
    {
        function getActiveTheme($authTheme = null,$frontendTheme = null){
            if (!is_null($authTheme)){
                return env('BASE_THEME_AUTH');
            }elseif (!is_null($frontendTheme)){
                return env('BASE_THEME_FRONTEND');
            }else{
                return env('BASE_THEME');
            }
        }
    }

    if (! function_exists('displayAlert')){
        /*
         * Usage::
         * Redirect::back()->with('message', 'error|There was an error...');
            Redirect::back()->with('message', 'message|Record updated.');
            Redirect::to('/')->with('message', 'success|Record updated.');
         */
        function displayAlert(){
            if (Session::has('message'))
            {
                list($type, $message) = explode('|', Session::get('message'));

                $type = $type == 'error' ?: 'danger';
                $type = $type == 'success' ?: 'success';
                $type = $type == 'message' ?: 'info';

                // return sprintf('<div class="alert alert-%s">%s</div>', $type, $message);
                return sprintf('<div class="alert alert-%s alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                </button>
                <strong>%s 😃	</strong>
              </div>', $type, $message);
            }

            return '';
        }

    }

    if (! function_exists('storagePath')){
         function storagePath(string $path){
            if (function_exists('storage_path')){
                return storage_path($path);
            }
            return __DIR__ .'/'. $path;
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