<?php declare(strict_types=1);
namespace RateLimit\Tests;
include_once "../vendor/autoload.php";

use RateLimit\TokenBucket;
use RateLimit\Storage\FileStorage;

class TestTokenBucket 
{
    private $fileStore = NULL;
    public function __construct()
    {
        $this->fileStore = new FileStorage('./file.log'); 
    }

    public function testGetSingleToken() 
    {
        $tokenBucket = new TokenBucket([
            'identifier' => 'test_get',
            'token_num' => 1,
            'secondes' => 0,
        ], $this->fileStore);
        print_r(['xxxx', $tokenBucket->getTokens()]);
    }

    public function testConsumeManyTokens() 
    {
        $tokenBucket = new TokenBucket([
            'identifier' => 'test_consume_many',
            'token_num' => 10000,
            'secondes' => 60,
        ], $this->fileStore);

        list($consumed, $timeUntilReady) = $tokenBucket->consume(10000);
        // $this->assertTrue(is_bool($consumed));
        // $this->assertTrue(is_numeric($timeUntilReady));
        // $this->assertTrue($consumed, "Didn't consume a token.");
        // $this->assertSame(0, $timeUntilReady, "Wasn't ready after consume");
        // $this->assertEquals(0, $bucket->getTokens());
        print_r(['token', $tokenBucket->getTokens()]);


        list($consumed, $timeUntilReady) = $tokenBucket->consume(10000);
        // $this->assertTrue(is_bool($consumed));
        // $this->assertTrue(is_numeric($timeUntilReady));
        // $this->assertFalse($consumed, "Consumed a token.");
        // $this->assertEquals(60, $timeUntilReady, "Incorrect ready time");
        // $this->assertEquals(0, $bucket->getTokens());
        print_r(['getTokens', $tokenBucket->getTokens()]);
    }

    public function testFailureToConsume() 
    {
        $tokenBucket = new TokenBucket([
            'identifier' => 'test_fail_consume',
            'token_num' => 0,
            'secondes' => 0,
        ], $this->fileStore);

        list($consumed, $timeUntilReady) = $tokenBucket->consume(1);
        print_r(['testFailureToConsume', $consumed, $timeUntilReady]);
    }

    public function testTokenRegeneration() 
    {
        $tokenBucket = new TokenBucket([
            'identifier' => 'test_token_regen',
            'token_num' => 1,
            'secondes' => 1,
        ], $this->fileStore);

        list($consumed, $timeUntilReady) = $tokenBucket->consume(1);
        print_r(['testTokenRegeneration_1', $consumed, $timeUntilReady]);

        list($consumed, $timeUntilReady) = $tokenBucket->consume(1);
        print_r(['testTokenRegeneration_2', $consumed, $timeUntilReady]);

        $tokenBucket->setOffset(1);
        print_r(['token', $tokenBucket->getTokens()]); 
    }

    public function testUpdatedTokensCalculated() 
    {

        $tokenBucket = new TokenBucket([
            'identifier' => 'test_updated_tokens_calculated',
            'token_num' => 10,
            'secondes' => 10,
        ], $this->fileStore);

        list($consumed, $timeUntilReady) = $tokenBucket->consume(10);
        $tokenBucket->setOffset(5);

        list($consumed, $timeUntilReady) = $tokenBucket->consume(10);
        // print_r(['testUpdatedTokensCalculated_1', $consumed, $timeUntilReady]);

        // $tokenBucket->setOffset(6);
        // list($consumed, $timeUntilReady) = $tokenBucket->consume(5);
        // print_r(['testUpdatedTokensCalculated_2', $consumed, $timeUntilReady]);
    }
}

(new TestTokenBucket())->testUpdatedTokensCalculated();
