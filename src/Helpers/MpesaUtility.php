<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 3/27/18
 * Time: 3:29 PM
 */

namespace Rndwiga\Mpesa\Helpers;

use Illuminate\Support\Facades\File;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MpesaUtility
{

    private static $logger;

    /***
     * @param $dataToLog - json data to log
     * @param $fileName - name of the file
     * @param string $folderName - Each script calling this function can store the info in one specific folder
     * @param string $method - This can be [debug,error ]    protected static $levels = [
                                                                                        self::DEBUG     => 'DEBUG',
                                                                                        self::INFO      => 'INFO',
                                                                                        self::NOTICE    => 'NOTICE',
                                                                                        self::WARNING   => 'WARNING',
                                                                                        self::ERROR     => 'ERROR',
                                                                                        self::CRITICAL  => 'CRITICAL',
                                                                                        self::ALERT     => 'ALERT',
                                                                                        self::EMERGENCY => 'EMERGENCY',
                                                                                        ];
     * @param int $maxNumberOfLines - Maximum number of lines in one log file
     */
    public static function logInfo($dataToLog, $fileName, $folderName = 'appLog', $method = 'debug', $maxNumberOfLines = 10000){
        self::$logger = new Logger('gateway');
        // Trim log file to a max length
        $path = storage_path(self::createStorage($folderName,true).'/'.$fileName.'.log');
        if (! file_exists($path)) {
            fopen($path, "w");
        }
        $lines = file($path);
        if (count($lines) >= $maxNumberOfLines) {
            file_put_contents($path, implode('', array_slice($lines, -$maxNumberOfLines, $maxNumberOfLines)));
        }

        // Define custom Monolog handler
        try {
            $handler = new StreamHandler($path, Logger::DEBUG);
        } catch (\Exception $e) {
        } //This will have both DEBUG and ERROR messages
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        // Set defined handler and log the message
        self::$logger->setHandlers([$handler]);
        // self::$logger->pushHandler($handler);
        self::$logger->$method(json_encode($dataToLog));
    }

    public static function createStorage($folderName, $useDate = false)
    {
        /*
        * This function is for creating folders organized by date for the storage of files
        call this function before any file created to set the dependencies
        --this function can be enhanced to look at the name for slashes so as to create subdirectories automatically
        */
        $today = null;

        if ($useDate){
            $today = date('Y-m-d'); //setting the date
            $folder = "Gateway/".$today.'/'.$folderName; // setting the folder name
        }else{
            $folder = "Gateway/";
        }

        if (!is_dir(storage_path($folder)))
        {
            mkdir(storage_path($folder), 0777, true); //creating the folder docs if it does not already exist
        }
        if (!is_dir(storage_path($folder)))
        {
            //creating folder based on day if it does not exist. If it does, it is not created
            if (!mkdir(storage_path($folder), 0777, true)) {
                die('Failed to create folders...'); // Die if the function mkdir cannot run
            }
            return $folder;

        } elseif (is_dir(storage_path($folder))){ //check if the folder is created and return it
            //return $folder.'/'.$today;
            return $folder;
        } else {
            // return $folder.'/'.$today;				// Return the folder if its already created in the file system

            return $folder;
        }

    }

    /**
     * GZIPs a file on disk (appending .gz to the name)
     *
     * From http://stackoverflow.com/questions/6073397/how-do-you-create-a-gz-file-using-php
     * Based on function by Kioob at:
     * http://www.php.net/manual/en/function.gzwrite.php#34955
     *
     * @param string $source Path to file that should be compressed
     * @param integer $level GZIP compression level (default: 9)
     * @param bool $fileFormat
     * @return string New filename (with .gz appended) if success, or false if operation fails
     * @internal param bool|string $format
     */
    public static function gzCompressFile($source, $level = 9, $fileFormat = false){
        if ($fileFormat){
            $destination = $source . '.gz';
        }else{
            $destination = $source;
        }
        $mode = 'wb' . $level;
        $error = false;
        if ($fp_out = gzopen($destination, $mode)) {
            if ($fp_in = fopen($source,'rb')) {
                while (!feof($fp_in))
                    gzwrite($fp_out, fread($fp_in, 1024 * 512));
                fclose($fp_in);
            } else {
                $error = true;
            }
            gzclose($fp_out);
        } else {
            $error = true;
        }
        if ($error)
            return false;
        else
            return $destination;
    }

    public static function generateRandomId(){
        $time = time();
        $currentTime = $time;
        $random1= rand(0,99999);
        $random2 = mt_rand();
        $random = $random1 * $random2;
        $a= ($currentTime + $random);
        $un=  uniqid();
        $conct = $a . $un  . md5($a);
        $cashflowRandomId = sha1($conct.$un);
        return $cashflowRandomId;
    }

    public static function saveToFile($fileName,$directoryName,$payload = []){

        $dir = storage_path(self::createStorage($directoryName,false));
        $path = $dir.$fileName;
        $bytes = File::put($path,json_encode($payload,JSON_PRETTY_PRINT));
        if ($bytes === false){
            return 'could not write to file';
        }
        return array_merge((array)$payload,['file'=>$path]);
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