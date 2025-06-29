# Toolbox

A package containing helper methods and classes that can be used in any project.

## Installation

You can install the package via composer:

```bash
composer require rndwiga/toolbox
```

## Requirements

- PHP 8.1 or higher
- Monolog 2.0 or higher
- PHPDotEnv 5.0 or higher

## Features

The Toolbox package provides several utility classes and helper functions:

### Helper Functions

The package includes several helper functions that can be used throughout your application:

- `storagePath($path)`: Get the storage path for a given file or directory
- `env($variable)`: Get an environment variable value
- `str_slug($string)`: Convert a string to a URL-friendly slug

### AppLogger

The `AppLogger` class provides a simple interface for logging application data using Monolog.

```php
use Rndwiga\Toolbox\Infrastructure\Services\AppLogger;

// Create a logger instance
$logger = new AppLogger('myFolder', 'myLogFile');

// Log data at different levels
$logger->logDebug(['message' => 'Debug message']);
$logger->logInfo(['message' => 'Info message']);
$logger->logWarning(['message' => 'Warning message']);
$logger->logError(['message' => 'Error message']);

// Or use the generic log method
$logger->log(['message' => 'Custom message'], 'notice');
```

### AppJsonManager

The `AppJsonManager` class provides methods for managing JSON data and files.

```php
use Rndwiga\Toolbox\Infrastructure\Services\AppJsonManager;

// Save data to a JSON file
AppJsonManager::saveToFile('data.json', 'myFolder', ['key' => 'value']);

// Save data to a specific file path
AppJsonManager::saveToFilePath('/path/to/file.json', ['key' => 'value']);

// Read a JSON file
$data = AppJsonManager::readJsonFile('/path/to/file.json', true); // true to get as array

// Add data to an existing JSON file
AppJsonManager::addDataToJsonFile('/path/to/file.json', ['new' => 'data']);

// Validate JSON data
$result = AppJsonManager::validateJsonData('{"key": "value"}');
if ($result['status'] === 'success') {
    // JSON is valid
}
```

### AppStorage

The `AppStorage` class provides methods for managing storage paths and operations.

```php
use Rndwiga\Toolbox\Infrastructure\Services\AppStorage;

// Create a storage instance
$storage = new AppStorage('myRootFolder');
$storage->setLogFolder('myLogFolder');

// Create a storage path
$path = $storage->createStorage();

// Compress a file using gzip
AppStorage::gzCompressFile('/path/to/file.txt', 9, true);

// Generate a random ID
$id = AppStorage::generateRandomId();
```

## Laravel Integration

The package can be used with Laravel, but it doesn't require it. If you're using Laravel, the package will use Laravel's `storage_path()` function for determining storage paths.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.