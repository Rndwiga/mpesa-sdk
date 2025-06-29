<?php
/**
 * AppStorage Class
 *
 * A class for managing storage paths and operations.
 *
 * @package Rndwiga\Toolbox\Infrastructure\Services
 * @author Raphael Ndwiga <raphndwi@gmail.com>
 */

namespace Rndwiga\Toolbox\Infrastructure\Services;

class AppStorage
{
    /**
     * The root folder name
     *
     * @var string
     */
    private $rootFolder;

    /**
     * The log folder name
     *
     * @var string
     */
    private $logFolder;

    /**
     * Whether to use date in the path
     *
     * @var bool
     */
    private $useDate = true;

    /**
     * AppStorage constructor.
     *
     * @param string $rootFolder The root folder name
     */
    public function __construct($rootFolder = 'appLogs')
    {
        $this->setRootFolder($rootFolder);
    }

    /**
     * Get the root folder name
     *
     * @return string The root folder name
     */
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * Set the root folder name
     *
     * @param string $rootFolder The root folder name
     * @return AppStorage
     */
    public function setRootFolder($rootFolder = 'appLogs')
    {
        $this->rootFolder = $rootFolder;
        return $this;
    }

    /**
     * Get the log folder name
     *
     * @return string The log folder name
     */
    public function getLogFolder()
    {
        return $this->logFolder;
    }

    /**
     * Set the log folder name
     *
     * @param string $logFolder The log folder name
     * @return AppStorage
     */
    public function setLogFolder($logFolder)
    {
        $this->logFolder = $logFolder;
        return $this;
    }

    /**
     * Get whether to use date in the path
     *
     * @return bool Whether to use date in the path
     */
    public function getUseDate()
    {
        return $this->useDate;
    }

    /**
     * Set whether to use date in the path
     *
     * @param bool $useDate Whether to use date in the path
     * @return AppStorage
     */
    public function setUseDate($useDate)
    {
        $this->useDate = (bool)$useDate;
        return $this;
    }

    /**
     * Create a storage path for testing
     *
     * @return string The created storage path
     */
    public function mockStorage()
    {
        return $this->setLogFolder("data")->createStorage();
    }

    /**
     * Create a storage path based on the configured settings
     *
     * @return string The created storage path
     * @throws \RuntimeException If the directory cannot be created
     */
    public function createStorage()
    {
        // Build the folder path
        $folder = '/storage/';

        if ($this->getUseDate()) {
            $folder .= date('Y') . '/' . date('M') . '/';
        }

        $folder .= $this->getRootFolder() . '/';

        if ($this->getUseDate()) {
            $folder .= date('Y-m-d') . '/';
        }

        $folder .= $this->getLogFolder();

        // Create the directory if it doesn't exist
        $fullPath = storagePath($folder);

        if (!is_dir($fullPath)) {
            if (!mkdir($fullPath, 0777, true)) {
                throw new \RuntimeException('Failed to create directory: ' . $fullPath);
            }
        }

        return $folder;
    }

    /**
     * Compress a file using gzip
     *
     * @param string $source Path to file that should be compressed
     * @param int $level GZIP compression level (default: 9)
     * @param bool $appendExtension Whether to append .gz to the filename
     * @return string|false New filename if success, or false if operation fails
     */
    public static function gzCompressFile($source, $level = 9, $appendExtension = false)
    {
        if (!file_exists($source)) {
            return false;
        }

        $destination = $appendExtension ? $source . '.gz' : $source;
        $mode = 'wb' . $level;

        try {
            $fpOut = gzopen($destination, $mode);
            if ($fpOut === false) {
                return false;
            }

            $fpIn = fopen($source, 'rb');
            if ($fpIn === false) {
                gzclose($fpOut);
                return false;
            }

            while (!feof($fpIn)) {
                $data = fread($fpIn, 1024 * 512);
                if ($data === false) {
                    fclose($fpIn);
                    gzclose($fpOut);
                    return false;
                }

                gzwrite($fpOut, $data);
            }

            fclose($fpIn);
            gzclose($fpOut);

            return $destination;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate a random ID
     *
     * @return string The generated random ID
     */
    public static function generateRandomId()
    {
        $time = time();
        $random = rand(0, 99999) * mt_rand();
        $uniqueId = uniqid();
        $combined = ($time + $random) . $uniqueId . md5($time + $random);

        return sha1($combined . $uniqueId);
    }
}
