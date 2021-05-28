<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 3/27/18
 * Time: 3:29 PM
 */

namespace Rndwiga\Toolbox\Infrastructure\Services;


class AppStorage
{

    private $rootFolder;
    private $logFolder;
    private $useDate;

    /**
     * AppStorage constructor.
     */
    public function __construct()
    {
        $this->setRootFolder();
    }


    /**
     * @return mixed
     */
    public function getRootFolder():string
    {
        return $this->rootFolder;
    }

    /**
     * @param mixed $rootFolder
     * @return AppStorage
     */
    public function setRootFolder($rootFolder = 'appLogs')
    {
        $this->rootFolder = $rootFolder;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogFolder():string
    {
        return $this->logFolder;
    }

    /**
     * @param mixed $logFolder
     * @return AppStorage
     */
    public function setLogFolder($logFolder)
    {
        $this->logFolder = $logFolder;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUseDate():bool
    {
        return $this->useDate;
    }

    /**
     * @param mixed $useDate
     * @return AppStorage
     */
    public function setUseDate($useDate)
    {
        $this->useDate = $useDate;
        return $this;
    }

    public function mockStorage(){
        $this->setLogFolder("data")->createStorage();
    }

    public function createStorage()
    {
        $folder = '/storage/'. date('Y').'/'.date('M').'/'."{$this->getRootFolder()}/".date('Y-m-d').'/'.$this->getLogFolder(); // setting the folder name

        if (!is_dir(storagePath($folder)))
        {
            mkdir(storagePath($folder), 0777, true); //creating the folder docs if it does not already exist
        }
        if (!is_dir(storagePath($folder)))
        {
            //creating folder based on day if it does not exist. If it does, it is not created
            if (!mkdir(storagePath($folder), 0777, true)) {
                die('Failed to create folders...'); // Die if the function mkdir cannot run
            }
            return $folder;

        } elseif (is_dir(storagePath($folder))){ //check if the folder is created and return it
            return $folder;
        } else {
            return $folder;
        }
    }

    /**
     * GZIPs a file on disk (appending .gz to the name)
     *
     * From http://stackoverflow.com/questions/6073397/how-do-you-create-a-gz-file-using-php
     * Based on function by Kioob at:
     * http://www.php.net/manual/en/function.gzwrite.php#34955
     *
     * @param string $source Path to file that should be compressed
     * @param integer $level GZIP compression level (default: 9)
     * @param bool $fileFormat
     * @return string New filename (with .gz appended) if success, or false if operation fails
     * @internal param bool|string $format
     */
    public static function gzCompressFile($source, $level = 9, $fileFormat = false){
        if ($fileFormat){
            $destination = $source . '.gz';
        }else{
            $destination = $source;
        }
        $mode = 'wb' . $level;
        $error = false;
        if ($fp_out = gzopen($destination, $mode)) {
            if ($fp_in = fopen($source,'rb')) {
                while (!feof($fp_in))
                    gzwrite($fp_out, fread($fp_in, 1024 * 512));
                fclose($fp_in);
            } else {
                $error = true;
            }
            gzclose($fp_out);
        } else {
            $error = true;
        }
        if ($error)
            return false;
        else
            return $destination;
    }

    public static function generateRandomId(){
        $time = time();
        $currentTime = $time;
        $random1= rand(0,99999);
        $random2 = mt_rand();
        $random = $random1 * $random2;
        $a= ($currentTime + $random);
        $un=  uniqid();
        $conct = $a . $un  . md5($a);
        $cashflowRandomId = sha1($conct.$un);
        return $cashflowRandomId;
    }
}