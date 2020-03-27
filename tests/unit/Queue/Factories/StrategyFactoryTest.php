<?php

namespace App\Test\Queue\Factories;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Factories\BlockerHandlerFactory;
use BrighteCapital\QueueClient\Queue\Factories\StrategyFactory;
use BrighteCapital\QueueClient\Queue\Sqs\SqsBlockerHandler;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Storage\NullStorage;
use BrighteCapital\QueueClient\Strategies\BlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Psr\Log\NullLogger;

class StrategyFactoryTest extends BaseTestCase
{
    /** @var Retry */
    protected $retry;
    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $client = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();
        $storage = new NullStorage();
        $logger = new NullLogger();
        $notification = new NullNotificationChannel();
        $this->factory = new StrategyFactory($client, $storage, $logger, $notification);
        $this->retry = new Retry(0, 0, NonBlockerRetryStrategy::class);
    }

    public function testCreateNonBlocker()
    {
        $this->retry->setStrategy(NonBlockerRetryStrategy::class);
        $this->assertInstanceOf(
            NonBlockerRetryStrategy::class,
            $this->factory->create($this->retry)
        );
    }

    public function testCreateBlocker()
    {
        $this->retry->setStrategy(BlockerRetryStrategy::class);
        $this->assertInstanceOf(
            BlockerRetryStrategy::class,
            $this->factory->create($this->retry)
        );
    }

    public function testCreateBlockerStorage()
    {
        $this->retry->setStrategy(BlockerStorageRetryStrategy::class);
        $this->assertInstanceOf(
            BlockerStorageRetryStrategy::class,
            $this->factory->create($this->retry)
        );
    }

    public function testCreateFailed()
    {
        $this->retry->setStrategy('test');
        try {
            $this->factory->create($this->retry);
        } catch (\Exception $e) {
            $this->assertEquals('Given Strategy is not defined : test', $e->getMessage());
        }
    }
}
