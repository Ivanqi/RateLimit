<?php declare(strict_types=1);
namespace RateLimit\Service;

class TokenMicrotime 
{
    private $storage = NULL;
    private $key;

    public function __construct($storage, $key)
    {
        $this->storage = $storage;
        $this->key = $this->getMicoretimeName($key);
    }

    public function initMicoretime()
    {
        $this->saveMicrotime(microtime(true));
    }

    private function getMicoretimeName($identifier)
    {
        return $identifier . '_micoretime';
    }

    public function getMicrotime()
    {
        return (float) $this->storage->get($this->key);
    }

    public function saveMicrotime($microtime)
    {
        $this->storage->set($this->key, $microtime);
    }
}