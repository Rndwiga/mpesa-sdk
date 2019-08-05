<?php

/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 3/27/18
 * Time: 3:29 PM
 */
namespace Rndwiga\Toolbox\Infrastructure\Services;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AppLogger
{
    private $fileName;
    private $maxNumberOfLines = 10000;
    private $logLevel;

    private $logger;

    private $logStorage;

    /**
     * AppLogger constructor.
     * @param string $folderName
     * @param string $fileName
     */
    public function __construct(string $folderName,string $fileName)
    {
        $this->logger = $this->getLogger();
        $this->logStorage = (new AppStorage())->setLogFolder($folderName)->createStorage();
        $this->setFileName($fileName);
    }

    /**
     *This is a sample implementation of the logging class
     */
    public function sampleImplementation(){
        (new AppLogger('testFolder','test_file'))->logInfo(['data']);
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param mixed $fileName
     * @return AppLogger
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxNumberOfLines()
    {
        return $this->maxNumberOfLines;
    }

    /**
     * @param mixed $maxNumberOfLines
     * @return AppLogger
     */
    public function setMaxNumberOfLines($maxNumberOfLines)
    {
        $this->maxNumberOfLines = $maxNumberOfLines;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogLevel()
    {
        switch ($this->logLevel){
            case 'debug':
                return Logger::DEBUG;
                break;
        }
    }

    /**
     * @param mixed $logLevel
     * @return AppLogger
     */
    public function setLogLevel(string $logLevel)
    {
        if (is_null($logLevel)){
            $logLevel = 'debug';
        }
        $this->logLevel = $logLevel;
        return $this;
    }

    /**
     * @param string $loggerN
     * @return Logger
     */
    public function getLogger(string $loggerN = 'app.logger'): Logger
    {
        return $this->logger = new Logger($loggerN);
    }

    /***
     * @param $dataToLog - json data to log
     */
    public function logInfo(array $dataToLog){
        // Trim log file to a max length
        $path = storagePath("{$this->logStorage}/{$this->getFileName()}.log");
        if (! file_exists($path)) {
            fopen($path, "w");
        }
        $lines = file($path);
        if (count($lines) >= $this->getMaxNumberOfLines()) {
            file_put_contents($path, implode('', array_slice($lines, -($this->getMaxNumberOfLines()), $this->getMaxNumberOfLines())));
        }
        // Define custom Monolog handler
        try {
            $handler = new StreamHandler($path, $this->getLogLevel());
        } catch (\Exception $e) {
        } //This will have both DEBUG and ERROR messages
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        // Set defined handler and log the message
        $this->logger->setHandlers([$handler]);
        $this->logger->addDebug(json_encode($dataToLog)); //addError, addInfo, addWarning --TODO::enhance this
    }

    public function getLogFile(){
        $path = storagePath("{$this->logStorage}/{$this->getFileName()}.json");
        if (! file_exists($path)) {
            fopen($path, "w");
        }
        $lines = file($path);
        if (count($lines) == 0) {
            file_put_contents($path, json_encode([]));
        }

        return $path;
    }
}
