<?php
/**
 * CacheInterface
 *
 * Interface for cache implementations.
 *
 * @package Rndwiga\Mpesa\Utils
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Utils;

interface CacheInterface
{
    /**
     * Get a value from the cache
     *
     * @param string $key The cache key
     * @param mixed $default The default value to return if the key doesn't exist
     * @return mixed The cached value or default
     */
    public function get(string $key, $default = null);
    
    /**
     * Set a value in the cache
     *
     * @param string $key The cache key
     * @param mixed $value The value to cache
     * @param int|null $ttl Time to live in seconds
     * @return bool True on success, false on failure
     */
    public function set(string $key, $value, ?int $ttl = null): bool;
    
    /**
     * Check if a key exists in the cache
     *
     * @param string $key The cache key
     * @return bool True if the key exists, false otherwise
     */
    public function has(string $key): bool;
    
    /**
     * Delete a value from the cache
     *
     * @param string $key The cache key
     * @return bool True on success, false on failure
     */
    public function delete(string $key): bool;
}