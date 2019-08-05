<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 3/27/18
 * Time: 3:29 PM
 */

namespace Rndwiga\Toolbox\Infrastructure\Services;

use Illuminate\Support\Facades\File;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AppJsonManager
{

    public static function saveToFile($fileName,$directoryName,$payload = []){

        $dir = storage_path((new AppStorage())->setLogFolder($directoryName));
        $path = $dir.$fileName;
        $bytes = File::put($path,json_encode($payload,JSON_PRETTY_PRINT));
        if ($bytes === false){
            return 'could not write to file';
        }
        return array_merge((array)$payload,['file'=>$path]);
    }

    public static function saveToFilePath(string $filePath, array $payLoad){
        $bytes = File::put($filePath,json_encode($payLoad,JSON_PRETTY_PRINT));
        if ($bytes === false){
            return 'could not write to file';
        }
        return array_merge((array)$payLoad,['file'=>$filePath]);
    }

    public static function readJsonFile($file, $toArray = false){
        $jsonData = file_get_contents($file);
        if ($toArray === true){
           return json_decode($jsonData, true);
        }
        return json_decode($jsonData);
    }

    public static function addDataToJsonFile($file,$dataToAdd = []){
        $jsonData = file_get_contents($file);
        $arrayData = json_decode($jsonData, true);

        array_push($arrayData,(array)$dataToAdd);
        $backToJson = json_encode($arrayData, JSON_PRETTY_PRINT);
        $bytes = File::put($file,$backToJson);
        if ($bytes === false){
            return 'could not write to file';
        }

        return $file;
    }

    public static function validateJsonData($string)
    {
        // decode the JSON data
        $result = json_decode($string);

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        if ($error !== '') {
            // throw the Exception or exit // or whatever :)
            return [
                'status' => 'fail',
                'response' => $error
            ];
        }

        // everything is OK
        return [
            'status' => 'success',
            'response' => $result
        ];
    }
}