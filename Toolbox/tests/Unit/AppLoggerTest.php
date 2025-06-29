<?php

namespace Rndwiga\Toolbox\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rndwiga\Toolbox\Infrastructure\Services\AppLogger;

class AppLoggerTest extends TestCase
{
    /**
     * @var string Temporary directory for testing
     */
    private $tempDir;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a temporary directory for testing
        $this->tempDir = sys_get_temp_dir() . '/toolbox_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
        
        // Mock the storagePath function if it doesn't exist
        if (!function_exists('storagePath')) {
            eval('function storagePath($path = null) { return "' . $this->tempDir . '" . ($path ? $path : ""); }');
        }
    }
    
    /**
     * Clean up the test environment
     */
    protected function tearDown(): void
    {
        // Remove the temporary directory and its contents
        $this->removeDirectory($this->tempDir);
        
        parent::tearDown();
    }
    
    /**
     * Recursively remove a directory and its contents
     *
     * @param string $dir The directory to remove
     */
    private function removeDirectory($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->removeDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
    
    /**
     * Test the constructor and getters/setters
     */
    public function testConstructorAndGettersSetters()
    {
        // Create a logger instance
        $logger = new AppLogger('testFolder', 'testFile');
        
        // Test getFileName and setFileName
        $this->assertEquals('testFile', $logger->getFileName());
        $logger->setFileName('newFile');
        $this->assertEquals('newFile', $logger->getFileName());
        
        // Test getMaxNumberOfLines and setMaxNumberOfLines
        $this->assertEquals(10000, $logger->getMaxNumberOfLines());
        $logger->setMaxNumberOfLines(5000);
        $this->assertEquals(5000, $logger->getMaxNumberOfLines());
    }
    
    /**
     * Test the log methods
     */
    public function testLogMethods()
    {
        // Create a logger instance
        $logger = new AppLogger('testFolder', 'testFile');
        
        // Test logDebug
        $result = $logger->logDebug(['message' => 'Debug message']);
        $this->assertTrue($result);
        
        // Test logInfo
        $result = $logger->logInfo(['message' => 'Info message']);
        $this->assertTrue($result);
        
        // Test logWarning
        $result = $logger->logWarning(['message' => 'Warning message']);
        $this->assertTrue($result);
        
        // Test logError
        $result = $logger->logError(['message' => 'Error message']);
        $this->assertTrue($result);
        
        // Test generic log method
        $result = $logger->log(['message' => 'Notice message'], 'notice');
        $this->assertTrue($result);
    }
    
    /**
     * Test the getLogFile method
     */
    public function testGetLogFile()
    {
        // Create a logger instance
        $logger = new AppLogger('testFolder', 'testFile');
        
        // Test getting a JSON log file
        $jsonFile = $logger->getLogFile();
        $this->assertStringEndsWith('.json', $jsonFile);
        $this->assertFileExists($jsonFile);
        
        // Test getting a log file with a custom extension
        $logFile = $logger->getLogFile('log');
        $this->assertStringEndsWith('.log', $logFile);
        $this->assertFileExists($logFile);
    }
}