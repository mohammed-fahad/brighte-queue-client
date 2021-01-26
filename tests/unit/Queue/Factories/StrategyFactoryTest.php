<?php

namespace App\Test\Queue\Factories;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Factories\BlockerHandlerFactory;
use BrighteCapital\QueueClient\Queue\Factories\StrategyFactory;
use BrighteCapital\QueueClient\Queue\Sqs\SqsBlockerHandler;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Storage\NullStorage;
use BrighteCapital\QueueClient\Strategies\BlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use Enqueue\Sqs\SqsMessage;
use Psr\Log\NullLogger;

class StrategyFactoryTest extends BaseTestCase
{
    /** @var Retry */
    protected $job;
    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $client = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();
        $storage = new NullStorage();
        $logger = new NullLogger();
        $notification = new NullNotificationChannel();
        $this->factory = new StrategyFactory($client, $storage, $logger, $notification);
        $this->job = new Job(new SqsMessage('test'), 0, 0, NonBlockerRetryStrategy::class);
    }

    public function testCreateNonBlocker()
    {
        $this->job->setStrategy(NonBlockerRetryStrategy::class);
        $this->assertInstanceOf(
            NonBlockerRetryStrategy::class,
            $this->factory->create($this->job)
        );
    }

    public function testCreateBlocker()
    {
        $this->job->setStrategy(BlockerRetryStrategy::class);
        $this->assertInstanceOf(
            BlockerRetryStrategy::class,
            $this->factory->create($this->job)
        );
    }

    public function testCreateBlockerStorage()
    {
        $this->job->setStrategy(BlockerStorageRetryStrategy::class);
        $this->assertInstanceOf(
            BlockerStorageRetryStrategy::class,
            $this->factory->create($this->job)
        );
    }

    public function testCreateFailed()
    {
        $this->job->setStrategy('test');
        try {
            $this->factory->create($this->job);
        } catch (\Exception $e) {
            $this->assertEquals('Given Strategy is not defined : test', $e->getMessage());
        }
    }
}
