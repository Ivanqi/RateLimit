<?php declare(strict_types=1);
namespace RateLimit\Adapt;
interface Storage
{
    const MISS = NULL;

    /**
     * 获取键值
     * @param mixed $key
     */
    public function get($key);

    /**
     * 设置键值
     * @param mixed $key
     * @param mixed $value
     * @param mixed $expirationTime
     */
    public function set($key, $value, $expirationTime = 0);
}