<?php declare(strict_types=1);
namespace RateLimit;

use RateLimit\Service\TokenRate;
use RateLimit\Service\StoredBucket;
use RateLimit\Adapt\Storage;

/***
 * 数据存储
 *  1. 文件模式
 *  2. redis.(redis 账号)
 *  3. mem (mem 账号)
 */
class TokenBucket 
{
    // $config = [
    //     'identifier' => '存储标标识',
    //     'token_num' => 'token总数量',
    //     'secondes' => 'token限制描述符',
    //     'rate_mode' => 'token|calculate'
    // ];

    private $rate = NULL;
    private $prefix = 'rate_limit_';
    private $identifier = '';
    private $token = 0;
    private $secondes = 0;
    private $offset = 0;
    private $storage = NULL;
    private $micoretimeId = '';

    public function __construct($config = [], $storage)
    {
        $this->identifier = $this->prefix . self::array_get($config, 'identifier', 'default');
        $this->micoretimeId = $this->getMicoretimename($this->identifier);
        $tokens = self::array_get($config, 'token_num', 1000);
        $secondes = self::array_get($config, 'secondes', 1);
        $this->rate = new TokenRate($tokens, $secondes);
        $this->storage = $storage;
    }

    public function getMicoretimename($identifier)
    {
        return $identifier . '_micoretime';
    }

    /**
     * 令牌消费
     * @param mixed $amount 需要消费的令牌数量
     * @return array
     */
    public function consume($amount): array
    {
        if (!is_numeric($amount) || $amount < 0) {
            throw new InvalidArgumentException("amount 必须是一个正整数");
        }

        $storedBucket = $this->getStoredBucket();
        $tokens = $this->calculateCurrentTokens($storedBucket);
        $updatedTokens = $tokens - $amount;
        if ($updatedTokens < 0) {
            return [false, $this->readyTime($amount, $storedBucket)];
        }

        $newBucket = new StoredBucket($updatedTokens, $this->microtime());
        $this->storeBucket($newBucket);
        return [true, 0];
    }

    /**
     * 获取桶存储对象
     * @return StoredBucket
     */
    private function getStoredBucket()
    {
        $storedBucket = $this->storage->get($this->identifier);
        if ($storedBucket == Storage::MISS) {
            $storedBucket = new StoredBucket($this->rate->getTokens(), $this->microtime());
        }
        return $storedBucket;
    }

    /**
     * 重建计算生成token
     * @param StoredBucket $storedBucket 桶存储对象
     * @return mixed
     */
    private function calculateCurrentTokens(StoredBucket $storedBucket)
    {
        $tokens = $storedBucket->getTokens();
        $lastConsume = $storedBucket->getLastConsume();
        $microtime = $this->microtime();
        $timeDiff = $microtime - $lastConsume;
        $tokens += $timeDiff * $this->rate->getRate();
        // print_r(['calculateCurrentTokens', $microtime, $lastConsume, $timeDiff, $tokens, $this->rate->getTokens()]);
        return min($this->rate->getTokens(), $tokens);
    }

     /**
     * 返回令牌可用的秒数
     * @param mixed $consumeAmount 消费令牌的数量
     * @param StoredBucket $storedBucket
     * @return mixed
     */
    public function readyTime($consumeAmount, StoredBucket $storedBucket)
    {
        if (!is_int($consumeAmount)) {
            throw new InvalidArgumentException("consumeAmount 必须是整数");
        }
        // 无法再生成令牌，返回空
        if ($consumeAmount > $this->rate->getTokens() || $this->rate->getRate() <= 0) {
            return NULL;
        }
        // 重新计算，返回需要多少令牌
        $tokens = $this->calculateCurrentTokens($storedBucket);
        return ($consumeAmount - $tokens) / $this->rate->getRate();
    }

    /**
     * @return mixed $tokens 当前桶中令牌数
     */
    public function getTokens()
    {
        $storedBucket = $this->getStoredBucket();
        $updatedTokens = $this->calculateCurrentTokens($storedBucket);
        return $updatedTokens;
    }

    /**
     * 存储当前桶
     * @param StoredBucket $storedBucket
     * @return void
     */
    private function storeBucket(StoredBucket $storedBucket): void
    {
        $readyTime = $this->readyTime($this->rate->getTokens(), $storedBucket);
        $this->storage->set($this->identifier, $storedBucket, $readyTime);
    }

    private static function array_get($config, $key, $default = '')
    {
        if (array_key_exists($key, $config)) {
            if ($config[$key] == 0 || !empty($config[$key])){
                return $config[$key];
            }
        }
        return $default;
    }

    protected function microtime(): float
    {  
        $microtime = $this->getMicrotime();
        if (!$microtime) {
            $microtime = microtime(true);
            $this->saveMicrotime($microtime);
        }
        
        if ($microtime + $this->offset == $microtime) {
            return $microtime;
        }
        $newMicrotime = $microtime + $this->offset;
        $this->saveMicrotime($newMicrotime);
        return $newMicrotime;
    }

    private function getMicrotime()
    {
        return (float) $this->storage->get($this->micoretimeId);
    }

    private function saveMicrotime($microtime)
    {
        $this->storage->set($this->micoretimeId, $microtime);
    }

    public function setOffset($offset)
    {
        if (!is_numeric($offset)) {
            throw InvalidArgumentException("offset needs to be numeric");
        }
        $this->offset = $offset;
    }
}