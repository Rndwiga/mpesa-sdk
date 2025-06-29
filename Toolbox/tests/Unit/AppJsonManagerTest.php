<?php

namespace Rndwiga\Toolbox\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rndwiga\Toolbox\Infrastructure\Services\AppJsonManager;

class AppJsonManagerTest extends TestCase
{
    /**
     * @var string Temporary directory for testing
     */
    private $tempDir;
    
    /**
     * @var string Temporary file for testing
     */
    private $tempFile;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a temporary directory and file for testing
        $this->tempDir = sys_get_temp_dir() . '/toolbox_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
        
        $this->tempFile = $this->tempDir . '/test.json';
        file_put_contents($this->tempFile, json_encode(['test' => 'data']));
    }
    
    /**
     * Clean up the test environment
     */
    protected function tearDown(): void
    {
        // Remove the temporary file and directory
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
        
        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
        
        parent::tearDown();
    }
    
    /**
     * Test the readJsonFile method
     */
    public function testReadJsonFile()
    {
        // Test reading as object
        $data = AppJsonManager::readJsonFile($this->tempFile);
        $this->assertIsObject($data);
        $this->assertEquals('data', $data->test);
        
        // Test reading as array
        $data = AppJsonManager::readJsonFile($this->tempFile, true);
        $this->assertIsArray($data);
        $this->assertEquals('data', $data['test']);
        
        // Test reading a non-existent file
        $data = AppJsonManager::readJsonFile($this->tempDir . '/nonexistent.json');
        $this->assertNull($data);
    }
    
    /**
     * Test the saveToFilePath method
     */
    public function testSaveToFilePath()
    {
        $newFile = $this->tempDir . '/new.json';
        $payload = ['key' => 'value'];
        
        // Save data to a file
        $result = AppJsonManager::saveToFilePath($newFile, $payload);
        
        // Verify the result
        $this->assertIsArray($result);
        $this->assertEquals('value', $result['key']);
        $this->assertEquals($newFile, $result['file']);
        
        // Verify the file was created
        $this->assertFileExists($newFile);
        
        // Verify the file content
        $content = file_get_contents($newFile);
        $data = json_decode($content, true);
        $this->assertEquals('value', $data['key']);
        
        // Clean up
        unlink($newFile);
    }
    
    /**
     * Test the addDataToJsonFile method
     */
    public function testAddDataToJsonFile()
    {
        // Add data to the file
        $result = AppJsonManager::addDataToJsonFile($this->tempFile, ['new' => 'value']);
        
        // Verify the result
        $this->assertEquals($this->tempFile, $result);
        
        // Verify the file content
        $content = file_get_contents($this->tempFile);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertEquals('data', $data[0]['test']);
        $this->assertEquals('value', $data[1]['new']);
        
        // Test adding to a non-existent file
        $result = AppJsonManager::addDataToJsonFile($this->tempDir . '/nonexistent.json', ['new' => 'value']);
        $this->assertFalse($result);
    }
    
    /**
     * Test the validateJsonData method
     */
    public function testValidateJsonData()
    {
        // Test valid JSON
        $result = AppJsonManager::validateJsonData('{"key": "value"}');
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('value', $result['response']->key);
        
        // Test invalid JSON
        $result = AppJsonManager::validateJsonData('{key: value}');
        $this->assertEquals('fail', $result['status']);
        $this->assertStringContainsString('Syntax error', $result['response']);
    }
}