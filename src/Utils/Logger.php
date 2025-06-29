<?php
/**
 * Logger
 *
 * A class for logging application data using Monolog.
 *
 * @package Rndwiga\Mpesa\Utils
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */
namespace Rndwiga\Mpesa\Utils;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    /**
     * The name of the log file
     *
     * @var string
     */
    private $fileName;

    /**
     * The maximum number of lines to keep in the log file
     *
     * @var int
     */
    private $maxNumberOfLines = 10000;

    /**
     * The log level
     *
     * @var string
     */
    private $logLevel = 'debug';

    /**
     * The Monolog logger instance
     *
     * @var MonologLogger
     */
    private $logger;

    /**
     * The log storage path
     *
     * @var string
     */
    private $logStorage;

    /**
     * Logger constructor.
     * 
     * @param string $folderName The folder name for storing logs
     * @param string $fileName The name of the log file (without extension)
     */
    public function __construct(string $folderName, string $fileName)
    {
        $this->logger = $this->getMonologLogger();
        $this->logStorage = (new Storage())->setLogFolder($folderName)->createStorage();
        $this->setFileName($fileName);
    }

    /**
     * Get the file name
     * 
     * @return string The file name
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set the file name
     * 
     * @param string $fileName The file name
     * @return Logger
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * Get the maximum number of lines
     * 
     * @return int The maximum number of lines
     */
    public function getMaxNumberOfLines()
    {
        return $this->maxNumberOfLines;
    }

    /**
     * Set the maximum number of lines
     * 
     * @param int $maxNumberOfLines The maximum number of lines
     * @return Logger
     */
    public function setMaxNumberOfLines($maxNumberOfLines)
    {
        $this->maxNumberOfLines = $maxNumberOfLines;
        return $this;
    }

    /**
     * Get the log level as a Monolog constant
     * 
     * @return int The log level constant
     */
    public function getLogLevel()
    {
        switch ($this->logLevel) {
            case 'info':
                return MonologLogger::INFO;
            case 'notice':
                return MonologLogger::NOTICE;
            case 'warning':
                return MonologLogger::WARNING;
            case 'error':
                return MonologLogger::ERROR;
            case 'critical':
                return MonologLogger::CRITICAL;
            case 'alert':
                return MonologLogger::ALERT;
            case 'emergency':
                return MonologLogger::EMERGENCY;
            default:
                return MonologLogger::DEBUG;
        }
    }

    /**
     * Set the log level
     * 
     * @param string $logLevel The log level (debug, info, notice, warning, error, critical, alert, emergency)
     * @return Logger
     */
    public function setLogLevel(string $logLevel)
    {
        $validLevels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

        if (!in_array($logLevel, $validLevels)) {
            $logLevel = 'debug';
        }

        $this->logLevel = $logLevel;
        return $this;
    }

    /**
     * Get the logger instance
     * 
     * @param string $loggerName The name of the logger
     * @return MonologLogger The logger instance
     */
    public function getMonologLogger(string $loggerName = 'mpesa.logger'): MonologLogger
    {
        return new MonologLogger($loggerName);
    }

    /**
     * Log data at the specified level
     * 
     * @param array $dataToLog The data to log
     * @param string $level The log level (debug, info, notice, warning, error, critical, alert, emergency)
     * @return bool True if logging was successful, false otherwise
     */
    public function logData(array $dataToLog, string $level = 'debug')
    {
        $this->setLogLevel($level);

        // Trim log file to a max length
        $storage = new Storage();
        $path = $storage->storagePath("{$this->logStorage}/{$this->getFileName()}.log");

        try {
            if (!file_exists($path)) {
                $file = fopen($path, "w");
                if ($file === false) {
                    return false;
                }
                fclose($file);
            }

            $lines = file($path);
            if ($lines !== false && count($lines) >= $this->getMaxNumberOfLines()) {
                $result = file_put_contents($path, implode('', array_slice($lines, -($this->getMaxNumberOfLines()), $this->getMaxNumberOfLines())));
                if ($result === false) {
                    return false;
                }
            }

            // Define custom Monolog handler
            $handler = new StreamHandler($path, $this->getLogLevel());
            $handler->setFormatter(new LineFormatter(null, null, true, true));

            // Set defined handler and log the message
            $this->logger->setHandlers([$handler]);

            // Log the message at the appropriate level
            switch ($level) {
                case 'info':
                    $this->logger->info(json_encode($dataToLog));
                    break;
                case 'notice':
                    $this->logger->notice(json_encode($dataToLog));
                    break;
                case 'warning':
                    $this->logger->warning(json_encode($dataToLog));
                    break;
                case 'error':
                    $this->logger->error(json_encode($dataToLog));
                    break;
                case 'critical':
                    $this->logger->critical(json_encode($dataToLog));
                    break;
                case 'alert':
                    $this->logger->alert(json_encode($dataToLog));
                    break;
                case 'emergency':
                    $this->logger->emergency(json_encode($dataToLog));
                    break;
                default:
                    $this->logger->debug(json_encode($dataToLog));
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Log data at the debug level
     * 
     * @param array $dataToLog The data to log
     * @return bool True if logging was successful, false otherwise
     */
    public function logDebugData(array $dataToLog)
    {
        return $this->logData($dataToLog, 'debug');
    }

    /**
     * Log data at the info level
     * 
     * @param array $dataToLog The data to log
     * @return bool True if logging was successful, false otherwise
     */
    public function logInfoData(array $dataToLog)
    {
        return $this->logData($dataToLog, 'info');
    }

    /**
     * Log data at the warning level
     * 
     * @param array $dataToLog The data to log
     * @return bool True if logging was successful, false otherwise
     */
    public function logWarningData(array $dataToLog)
    {
        return $this->logData($dataToLog, 'warning');
    }

    /**
     * Log data at the error level
     * 
     * @param array $dataToLog The data to log
     * @return bool True if logging was successful, false otherwise
     */
    public function logErrorData(array $dataToLog)
    {
        return $this->logData($dataToLog, 'error');
    }

    /**
     * Get the log file path
     * 
     * @param string $extension The file extension (default: json)
     * @return string The log file path
     */
    public function getLogFile(string $extension = 'json')
    {
        $storage = new Storage();
        $path = $storage->storagePath("{$this->logStorage}/{$this->getFileName()}.{$extension}");

        try {
            if (!file_exists($path)) {
                $file = fopen($path, "w");
                if ($file !== false) {
                    fclose($file);

                    if ($extension === 'json') {
                        file_put_contents($path, json_encode([]));
                    }
                }
            } else if ($extension === 'json') {
                $lines = file($path);
                if ($lines !== false && count($lines) == 0) {
                    file_put_contents($path, json_encode([]));
                }
            }
        } catch (\Exception $e) {
            // Silently fail and return the path anyway
        }

        return $path;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->logData(array_merge(['message' => $message], $context), 'emergency');
    }

    /**
     * Action must be taken immediately.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->logData(array_merge(['message' => $message], $context), 'alert');
    }

    /**
     * Critical conditions.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->logData(array_merge(['message' => $message], $context), 'critical');
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->logData(array_merge(['message' => $message], $context), 'error');
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->logData(array_merge(['message' => $message], $context), 'warning');
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->logData(array_merge(['message' => $message], $context), 'notice');
    }

    /**
     * Interesting events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->logData(array_merge(['message' => $message], $context), 'info');
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->logData(array_merge(['message' => $message], $context), 'debug');
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logData(array_merge(['message' => $message], $context), $level);
    }
}