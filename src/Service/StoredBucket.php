<?php declare(strict_types=1);
namespace RateLimit\Service;

// 存储格式
class StoredBucket 
{
    private $token;
    private $lastConsume;

    /**
     * @param $tokens, 令牌类型为整形或双精度浮点型 
     * @param $lastConsume 最后消费时间
     */
    public function __construct($tokens, $lastConsume)
    {
        if (!is_numeric($lastConsume)) {
            throw new InvalidArgumentException("lastConsume 必须是数字"); 
        }

        if (!is_numeric($tokens) || $tokens < 0) {
            throw new InvalidArgumentException("tokens 必须是正数");
        }

        $this->tokens = $tokens;
        $this->lastConsume = $lastConsume;
    }

    /**
     * @return mixed
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @return mixed
     */
    public function getLastConsume()
    {
        return $this->lastConsume;
    }
}