<?php

namespace Rndwiga\Toolbox\Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    /**
     * Test the storagePath function
     */
    public function testStoragePath()
    {
        // Test with no parameter
        $path = storagePath();
        $this->assertIsString($path);
        
        // Test with a parameter
        $path = storagePath('/test');
        $this->assertStringEndsWith('/test', $path);
    }
    
    /**
     * Test the env function
     */
    public function testEnv()
    {
        // Set an environment variable
        putenv('TOOLBOX_TEST=value');
        
        // Test getting the environment variable
        $value = env('TOOLBOX_TEST');
        $this->assertEquals('value', $value);
        
        // Test getting a non-existent environment variable
        $value = env('TOOLBOX_TEST_NONEXISTENT');
        $this->assertFalse($value);
    }
    
    /**
     * Test the str_slug function
     */
    public function testStrSlug()
    {
        // Test basic slugification
        $slug = str_slug('Hello World');
        $this->assertEquals('hello-world', $slug);
        
        // Test with special characters
        $slug = str_slug('Hello, World!');
        $this->assertEquals('hello-world', $slug);
        
        // Test with multiple spaces and special characters
        $slug = str_slug('  Hello,  World!  ');
        $this->assertEquals('hello-world', $slug);
    }
}