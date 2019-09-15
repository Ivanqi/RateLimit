<?php declare(strict_types=1);

namespace RateLimit\Service;
use \InvalidArgumentException;

class TokenRate 
{
    private $rate;
    private $tokens;
    private $seconds;

    /**
     * @param int $tokens
     * @param mixed $seconds;
     */
    public function __construct($tokens, $seconds)
    {
        if (!is_int($tokens)) {
            throw new InvalidArgumentException("Tokens 必须是 int 类型");
        }

        if (!is_numeric($seconds)) {
            throw new InvalidArgumentException("Seconds 必须是 数字");
        }

        $this->tokens = $tokens;
        $this->seconds = $seconds;

        if ($this->tokens == 0 || $this->seconds == 0) {
            $this->rate = 0;
        } else {
            $this->rate = (double) $this->seconds / (double) $this->seconds;
        }
    }

    /**
     * @return mixed
     */
    public function getRate()
    {
        return $this->rate;
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
    public function getSencodes()
    {
        return $this->seconds;
    }
}