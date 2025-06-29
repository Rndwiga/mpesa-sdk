<?php

namespace Rndwiga\Toolbox\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rndwiga\Toolbox\Infrastructure\Services\AppStorage;

class AppStorageTest extends TestCase
{
    /**
     * Test the constructor and getters/setters
     */
    public function testConstructorAndGettersSetters()
    {
        // Test constructor with default value
        $storage = new AppStorage();
        $this->assertEquals('appLogs', $storage->getRootFolder());
        
        // Test constructor with custom value
        $storage = new AppStorage('customRoot');
        $this->assertEquals('customRoot', $storage->getRootFolder());
        
        // Test setRootFolder
        $storage->setRootFolder('newRoot');
        $this->assertEquals('newRoot', $storage->getRootFolder());
        
        // Test setLogFolder and getLogFolder
        $storage->setLogFolder('logFolder');
        $this->assertEquals('logFolder', $storage->getLogFolder());
        
        // Test setUseDate and getUseDate
        $storage->setUseDate(false);
        $this->assertFalse($storage->getUseDate());
        
        $storage->setUseDate(true);
        $this->assertTrue($storage->getUseDate());
    }
    
    /**
     * Test the generateRandomId method
     */
    public function testGenerateRandomId()
    {
        // Generate two random IDs
        $id1 = AppStorage::generateRandomId();
        $id2 = AppStorage::generateRandomId();
        
        // Verify they are strings
        $this->assertIsString($id1);
        $this->assertIsString($id2);
        
        // Verify they are different
        $this->assertNotEquals($id1, $id2);
        
        // Verify they are 40 characters long (SHA-1 hash)
        $this->assertEquals(40, strlen($id1));
        $this->assertEquals(40, strlen($id2));
    }
    
    /**
     * Test the createStorage method
     */
    public function testCreateStorage()
    {
        // Create a storage instance with useDate = false to make the test more predictable
        $storage = new AppStorage('testRoot');
        $storage->setLogFolder('testLog');
        $storage->setUseDate(false);
        
        // Get the storage path
        $path = $storage->createStorage();
        
        // Verify the path format
        $this->assertStringContainsString('/storage/testRoot/testLog', $path);
    }
    
    /**
     * Test the mockStorage method
     */
    public function testMockStorage()
    {
        // Create a storage instance
        $storage = new AppStorage('testRoot');
        
        // Call mockStorage
        $path = $storage->mockStorage();
        
        // Verify the path contains 'data'
        $this->assertStringContainsString('data', $path);
    }
}