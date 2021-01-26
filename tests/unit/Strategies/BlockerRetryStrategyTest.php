<?php

namespace App\Test\Strategies;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use BrighteCapital\QueueClient\Strategies\BlockerRetryStrategy;
use Enqueue\Sqs\SqsMessage;
use Psr\Log\NullLogger;

class BlockerRetryStrategyTest extends BaseTestCase
{
    protected const MAX_DELAY = 2;
    protected $client;
    protected $job;
    protected $logger;
    protected $notification;
    protected $storage;
    protected $message;
    /** @var BlockerRetryStrategy */
    protected $strategy;

    protected function setUp()
    {
        parent::setUp();
        $this->client = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();
        $this->job = $this->getMockBuilder(Job::class)->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(NullLogger::class)->disableOriginalConstructor()->getMock();
        $this->notification = $this
            ->getMockBuilder(NullNotificationChannel::class)->disableOriginalConstructor()->getMock();
        $this->storage = $this->createMock(MessageStorageInterface::class);
        $this->message = $this->getMockBuilder(SqsMessage::class)->disableOriginalConstructor()->getMock();
        $this->strategy = new BlockerRetryStrategy(
            $this->job,
            $this->client,
            self::MAX_DELAY,
            $this->logger,
            $this->notification,
            $this->storage
        );
    }

    public function testOn()
    {
        $this->client->expects($this->once())->method('delay')->with($this->message, self::MAX_DELAY);
        $this->invokeHiddenMethod($this->strategy, 'onMaxRetryReached', [$this->message]);
    }
}
