<?php declare(strict_types=1);
namespace RateLimit\Service;

class TokenOffset
{
    private $storage = NULL;
    private $key;

    public function __construct($storage, $key)
    {
        $this->storage = $storage;
        $this->key = $this->getOffsetName($key);
    }

    private function getOffsetName($identifier)
    {
        return $identifier . '_offset';
    }

    public function getOffset()
    {
        return (float) $this->storage->get($this->key);
    }

    public function saveOffset($offset)
    {
        $this->storage->set($this->key, $offset);
    }
}