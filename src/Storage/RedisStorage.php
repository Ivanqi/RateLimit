<?php declare(strict_types=1);
namespace RateLimit\Storage;
use RateLimit\Adapt\Storage;
class RedisStorage implements Storage
{
    private $redis;
    public function __construct($config)
    {
        if (extension_loaded('redis')) {
            if (empty($config)) {
                throw new \Exception("Reids 配置不存在！");
            }
            $config['password'] = $config['passwd'];            
            $this->redis = new \Redis();
            if (!$this->redis->connect($config['host'], $config['port'])) {
                throw new \Exception("连接失败");
            }
            if(!empty($config['passwd'])) {
                $this->redis->auth($config['passwd']);
            }
            if(!$this->redis->ping()){
                throw new \Exception("连接失败");
            } else {
                if(!empty($config['database'])) {
                    $this->redis->select($config['database']);
                }
            }
            return $this;
        } else {
            throw new \Exception('Reids 拓展没有安装！');
        }
    }
    public function get($key)
    {
        $value = $this->redis->get($key);
        return $value ? unserialize($value) : Storage::MISS;
    }

    public function set($key, $value, $expirationTime = 0)
    {
        return $this->redis->set($key, \serialize($value));
    }
}