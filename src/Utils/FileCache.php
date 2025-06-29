<?php
/**
 * FileCache
 *
 * A simple file-based cache implementation.
 *
 * @package Rndwiga\Mpesa\Utils
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Utils;

class FileCache implements CacheInterface
{
    /**
     * The cache directory
     *
     * @var string
     */
    protected $cacheDir;
    
    /**
     * Constructor
     *
     * @param string|null $cacheDir The cache directory (defaults to system temp directory)
     */
    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/mpesa_cache';
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Get the cache file path for a key
     *
     * @param string $key The cache key
     * @return string The cache file path
     */
    protected function getCacheFilePath(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
    
    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        $file = $this->getCacheFilePath($key);
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $content = file_get_contents($file);
        $data = unserialize($content);
        
        // Check if the cache has expired
        if (isset($data['expires_at']) && $data['expires_at'] < time()) {
            $this->delete($key);
            return $default;
        }
        
        return $data['value'] ?? $default;
    }
    
    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $file = $this->getCacheFilePath($key);
        
        $data = [
            'value' => $value,
            'created_at' => time()
        ];
        
        if ($ttl !== null) {
            $data['expires_at'] = time() + $ttl;
        }
        
        $content = serialize($data);
        
        return file_put_contents($file, $content) !== false;
    }
    
    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $file = $this->getCacheFilePath($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $content = file_get_contents($file);
        $data = unserialize($content);
        
        // Check if the cache has expired
        if (isset($data['expires_at']) && $data['expires_at'] < time()) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        $file = $this->getCacheFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
}