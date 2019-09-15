<?php declare(strict_types=1);
namespace RateLimit\Tests;

use RateLimit\TokenBucket;
use RateLimit\Storage\RedisStorage;
use PHPUnit\Framework\TestCase;

class TestTokenBucketForRedis extends TestCase
{
    public function redisStorage()
    {
        return new RedisStorage([
            'database' => 0,
            'passwd' => NULL,
            'host' => 'localhost',
            'port' => 6379,
        ]);
    }

    public function testGetSingleToken() 
    {
        $tokenBucket = new TokenBucket([
            'identifier' => 'test_get',
            'token_num' => 1,
            'secondes' => 0,
        ], $this->redisStorage());

        $this->assertSame(1, $tokenBucket->getTokens());

    }

    public function testConsumeManyTokens() 
    {
        $tokenBucket = new TokenBucket([
            'identifier' => 'test_consume_many',
            'token_num' => 10000,
            'secondes' => 60,
        ], $this->redisStorage());

        list($consumed, $timeUntilReady) = $tokenBucket->consume(10000);
        $this->assertTrue(is_bool($consumed));
        $this->assertTrue(is_numeric($timeUntilReady));
        $this->assertTrue($consumed, "不能消费token");
        $this->assertSame(0, $timeUntilReady, "消费后令牌无回复");
        $this->assertEquals(0, $tokenBucket->getTokens());


        list($consumed, $timeUntilReady) = $tokenBucket->consume(10000);
        $this->assertTrue(is_bool($consumed));
        $this->assertTrue(is_numeric($timeUntilReady));
        $this->assertFalse($consumed, "消费token");
        $this->assertEquals(60, $timeUntilReady, "准备时间不准确");
        $this->assertEquals(0, $tokenBucket->getTokens());
    }

    public function testFailureToConsume() 
    {
        $tokenBucket = new TokenBucket([
            'identifier' => 'test_fail_consume',
            'token_num' => 0,
            'secondes' => 0,
        ], $this->redisStorage());

        list($consumed, $timeUntilReady) = $tokenBucket->consume(1);
        $this->assertTrue(is_bool($consumed));
        $this->assertSame(null, $timeUntilReady, "准备时间为空，因为无法重新生成token");
        $this->assertFalse($consumed, "消费失败");
    }

    public function testTokenRegeneration() 
    {
        $tokenBucket = new TokenBucket([
            'identifier' => 'test_token_regen',
            'token_num' => 1,
            'secondes' => 1,
        ], $this->redisStorage());

        list($consumed, $timeUntilReady) = $tokenBucket->consume(1);
        $this->assertTrue(is_bool($consumed));
        $this->assertTrue(is_numeric($timeUntilReady));
        $this->assertTrue($consumed, "不能消费token");
        $this->assertEquals(0, $timeUntilReady, "消费成功");
        list($consumed, $timeUntilReady) = $tokenBucket->consume(1);
        $this->assertFalse($consumed);
        $tokenBucket->setOffset(1);
        $this->assertTrue($tokenBucket->getTokens() > 0, "无法重新生成token");
    }

    public function testUpdatedTokensCalculated() 
    {

        $tokenBucket = new TokenBucket([
            'identifier' => 'test_updated_tokens_calculated',
            'token_num' => 10,
            'secondes' => 10,
        ], $this->redisStorage());

        list($consumed, $timeUntilReady) = $tokenBucket->consume(10);
        $this->assertTrue($consumed);
        $this->assertEquals(0, $timeUntilReady);
        $tokenBucket->setOffset(5);

        list($consumed, $timeUntilReady) = $tokenBucket->consume(10);
        $this->assertFalse($consumed);
        $this->assertEquals(5, $timeUntilReady);
        $tokenBucket->setOffset(6);

        list($consumed, $timeUntilReady) = $tokenBucket->consume(5);
        $this->assertTrue($consumed);
        $this->assertEquals(0, $timeUntilReady);
    }
}