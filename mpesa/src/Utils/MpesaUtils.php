<?php
/**
 * MpesaUtils
 *
 * Utility methods for the Mpesa API.
 *
 * @package Rndwiga\Mpesa\Utils
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Utils;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

class MpesaUtils
{
    /**
     * Process and format a date/time string from Mpesa API
     *
     * @param string $timeDetails The date/time string to process
     * @return string The formatted date/time string (ISO 8601 format)
     * @throws \Exception If the date/time string is invalid
     */
    public static function processCompletedTime(string $timeDetails): string
    {
        return (new DateTime($timeDetails))->format(DateTimeInterface::ATOM);
    }

    /**
     * Validate a phone number to ensure it's in the correct format
     *
     * @param string|int $phoneNumber The phone number to validate
     * @return string The validated phone number
     * @throws InvalidArgumentException If the phone number is invalid
     */
    public static function validatePhoneNumber($phoneNumber): string
    {
        $phoneNumber = (string) $phoneNumber;

        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if the phone number is valid
        if (strlen($phoneNumber) < 9) {
            throw new InvalidArgumentException('Phone number is too short');
        }

        // Ensure the phone number starts with 254 (Kenya)
        if (substr($phoneNumber, 0, 3) !== '254') {
            if (substr($phoneNumber, 0, 1) === '0') {
                // Convert 07... to 2547...
                $phoneNumber = '254' . substr($phoneNumber, 1);
            } elseif (substr($phoneNumber, 0, 1) === '7' || substr($phoneNumber, 0, 1) === '1') {
                // Convert 7... to 2547...
                $phoneNumber = '254' . $phoneNumber;
            }
        }

        return $phoneNumber;
    }

    /**
     * Validate an amount to ensure it's a positive number
     *
     * @param int|float $amount The amount to validate
     * @return int The validated amount as an integer
     * @throws InvalidArgumentException If the amount is invalid
     */
    public static function validateAmount($amount): int
    {
        $amount = (int) $amount;

        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero');
        }

        return $amount;
    }

    /**
     * Generate a unique transaction reference
     *
     * @param string $prefix Optional prefix for the reference
     * @return string The generated reference
     */
    public static function generateTransactionReference(string $prefix = ''): string
    {
        $timestamp = time();
        $random = mt_rand(1000, 9999);

        return $prefix . $timestamp . $random;
    }

    /**
     * Process the response from the Mpesa API
     *
     * @param string $response The response from the API
     * @return mixed The processed response
     */
    public static function processApiResponse(string $response)
    {
        $decoded = json_decode($response);

        if (isset($decoded->errorCode)) {
            $errorCode = $decoded->errorCode;
            $errorDescriptions = [
                "400.002.01" => "Invalid Access Token",
                "400.002.02" => "Bad Request",
                "400.002.05" => "Invalid Request Payload",
                "401.002.01" => "Error Occurred - Invalid Access Token",
                "404.001.01" => "Resource not found",
                "404.002.01" => "Resource not found",
                "404.001.03" => "Invalid Access Token",
                "404.001.04" => "Invalid Authentication Header",
                "500.001.1001" => "Server Error",
                "500.002.1001" => "Server Error",
                "500.002.02" => "Error Occurred: Spike Arrest Violation",
                "500.002.03" => "Error Occurred: Quota Violation"
            ];

            $description = isset($errorDescriptions[$errorCode]) ? $errorDescriptions[$errorCode] : "Unknown Error";

            return [
                'success' => false,
                'errorCode' => $errorCode,
                'errorRequestId' => $decoded->requestId ?? null,
                'errorDescription' => $description,
                'errorMessage' => $decoded->errorMessage ?? null
            ];
        } elseif (isset($decoded->ConversationID)) {
            return [
                'success' => true,
                'data' => $decoded
            ];
        }

        return [
            'success' => true,
            'data' => $decoded
        ];
    }
}