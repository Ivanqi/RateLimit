<?php declare(strict_types=1);
namespace RateLimit\Storage;
use RateLimit\Adapt\Storage;

class FileStorage implements Storage 
{
    private $filePath = '/tmp/file_storage';

    public function __construct($filePath = '')
    {
        if (!empty($filePath)) {
            $this->filePath = $filePath;
        }
    }

    public function get($key)
    {
        $fileConfig = $this->getFIle();
        return array_key_exists($key, $fileConfig) ? $fileConfig[$key] : Storage::MISS;
    }

    public function set($key, $value, $expirationTime = 0)
    {
        $fileConfig = $this->getFile();
        $fileConfig[$key] = $value;
        $this->setFile($fileConfig);
    }

    private function getFile()
    {
        if (\file_exists($this->filePath)) {
            return unserialize(\file_get_contents($this->filePath));
        } else {
            return [];
        }
    }

    private function setFile($config) 
    {
        \file_put_contents($this->filePath, serialize($config), LOCK_EX);
        return false;
    }
}