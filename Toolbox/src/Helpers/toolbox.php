<?php
/**
 * Toolbox Helper Functions
 *
 * This file contains helper functions that can be used throughout the application.
 *
 * @package Rndwiga\Toolbox
 * @author Raphael Ndwiga <raphndwi@gmail.com>
 */

/**
 * Get the storage path for a given file or directory
 *
 * @param string|null $path The path to append to the storage path
 * @return string The full storage path
 */
if (! function_exists('storagePath')) {
    function storagePath(string $path = null): string {
        if (function_exists('storage_path')) {
            // Use Laravel's storage_path if available
            return storage_path($path);
        }
        return __DIR__ . ($path ?? '');
    }
}

/**
 * Get an environment variable value
 *
 * @param string $variable The name of the environment variable
 * @return string|false The value of the environment variable or false if not found
 */
if (! function_exists('env')) {
    function env(string $variable) {
        return getenv($variable);
    }
}

/**
 * Convert a string to a URL-friendly slug
 *
 * @param string $string The string to convert
 * @return string The slugified string
 */
if (! function_exists('str_slug')) {
    function str_slug(string $string): string {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    }
}
