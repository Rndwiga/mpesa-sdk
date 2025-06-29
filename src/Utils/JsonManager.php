<?php
/**
 * JsonManager
 *
 * A class for managing JSON data and files.
 *
 * @package Rndwiga\Mpesa\Utils
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Utils;

class JsonManager
{
    /**
     * Save data to a JSON file in the specified directory
     *
     * @param string $fileName The name of the file
     * @param string $directoryName The directory name
     * @param array $payload The data to save
     * @return array|string The payload with file path or error message
     */
    public static function saveToFile($fileName, $directoryName, $payload = [])
    {
        try {
            $storage = (new Storage())->setLogFolder($directoryName);
            $dir = $storage->storagePath($storage->createStorage());
            $path = $dir . '/' . $fileName;

            $bytes = file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT));
            if ($bytes === false) {
                return 'could not write to file';
            }

            return array_merge((array)$payload, ['file' => $path]);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Save data to a JSON file at the specified path
     *
     * @param string $filePath The full path to the file
     * @param array $payload The data to save
     * @return array|string The payload with file path or error message
     */
    public static function saveToFilePath(string $filePath, array $payload)
    {
        try {
            $bytes = file_put_contents($filePath, json_encode($payload, JSON_PRETTY_PRINT));
            if ($bytes === false) {
                return 'could not write to file';
            }

            return array_merge((array)$payload, ['file' => $filePath]);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Read a JSON file and decode its contents
     *
     * @param string $file The path to the JSON file
     * @param bool $toArray Whether to convert to array (true) or object (false)
     * @return mixed|null The decoded JSON data or null on error
     */
    public static function readJsonFile($file, $toArray = false)
    {
        try {
            if (!file_exists($file)) {
                return null;
            }

            $jsonData = file_get_contents($file);
            if ($jsonData === false) {
                return null;
            }

            return json_decode($jsonData, $toArray);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Add data to an existing JSON file
     *
     * @param string $file The path to the JSON file
     * @param array $dataToAdd The data to add
     * @return string|bool The file path on success or false on error
     */
    public static function addDataToJsonFile($file, $dataToAdd = [])
    {
        try {
            if (!file_exists($file)) {
                return false;
            }

            $jsonData = file_get_contents($file);
            if ($jsonData === false) {
                return false;
            }

            $arrayData = json_decode($jsonData, true);
            if ($arrayData === null) {
                $arrayData = [];
            }

            array_push($arrayData, (array)$dataToAdd);
            $backToJson = json_encode($arrayData, JSON_PRETTY_PRINT);

            $bytes = file_put_contents($file, $backToJson);
            if ($bytes === false) {
                return false;
            }

            return $file;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate JSON data
     *
     * @param string $string The JSON string to validate
     * @return array The validation result with status and response
     */
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
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occurred.';
                break;
        }

        if ($error !== '') {
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