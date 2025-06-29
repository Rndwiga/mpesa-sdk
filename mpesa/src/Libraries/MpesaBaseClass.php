<?php
/**
 * MpesaBaseClass
 *
 * Base class for Mpesa API implementations with common utility methods.
 *
 * @package Rndwiga\Mpesa\Libraries
 * @author Raphael Ndwiga <raphndwi@gmail.com>
 */

namespace Rndwiga\Mpesa\Libraries;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

class MpesaBaseClass
{
    /**
     * Process and format a date/time string from Mpesa API
     *
     * @param string $timeDetails The date/time string to process
     * @return string The formatted date/time string (Y-m-d H:i:s)
     * @throws \Exception If the date/time string is invalid
     */
    public function processCompletedTime(string $timeDetails): string
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
    public function validatePhoneNumber($phoneNumber): string
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
    public function validateAmount($amount): int
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
    public function generateTransactionReference(string $prefix = ''): string
    {
        $timestamp = time();
        $random = mt_rand(1000, 9999);

        return $prefix . $timestamp . $random;
    }
}
