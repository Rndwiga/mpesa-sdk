<?php
/**
 * MpesaHelpers
 *
 * Helper functions for the Mpesa package.
 *
 * @package Rndwiga\Mpesa\Utils
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Utils;

/**
 * Get an environment variable value
 *
 * @param string $variable The name of the environment variable
 * @return string|false The value of the environment variable or false if not found
 */
function mpesa_env(string $variable)
{
    // Use Laravel's env function if available, otherwise use getenv
    if (function_exists('env')) {
        return env($variable);
    }
    return getenv($variable);
}

/**
 * Convert a string to a URL-friendly slug
 *
 * @param string $string The string to convert
 * @return string The slugified string
 */
function mpesa_str_slug(string $string): string
{
    // Use Laravel's Str::slug if available, otherwise use our implementation
    if (class_exists('Illuminate\Support\Str')) {
        return \Illuminate\Support\Str::slug($string);
    }
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
}
