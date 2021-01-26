<?php

namespace App\Test\Strategies;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Storage\NullStorage;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use Enqueue\Sqs\SqsDestination;
use Enqueue\Sqs\SqsMessage;
use Psr\Log\NullLogger;

class BlockerStorageRetryStrategyTest extends BaseTestCase
{
    protected const MAX_DELAY = 2;
    protected $client;
    protected $job;
    protected $logger;
    protected $storage;
    protected $notification;
    protected $message;
    /** @var BlockerStorageRetryStrategy */
    protected $strategy;

    protected function setUp()
    {
        parent::setUp();
        $this->client = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();
        $this->job = $this->getMockBuilder(Job::class)->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(NullLogger::class)->disableOriginalConstructor()->getMock();
        $this->storage = $this->getMockBuilder(NullStorage::class)->disableOriginalConstructor()->getMock();
        $this->notification = $this
            ->getMockBuilder(NullNotificationChannel::class)->disableOriginalConstructor()->getMock();
        $this->message = $this->getMockBuilder(SqsMessage::class)->disableOriginalConstructor()->getMock();
        $this->strategy = new BlockerStorageRetryStrategy(
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
        $destination = $this->getMockBuilder(SqsDestination::class)->disableOriginalConstructor()->getMock();
        $destination->expects($this->once())->method('getQueueName')->willReturn('testQueue');

        $this->client->expects($this->once())->method('delay')->with($this->message, self::MAX_DELAY);
        $this->client->expects($this->once())->method('getDestination')->willReturn($destination);
        $this->job->expects($this->once())->method('getErrorMessage')->willReturn('failedMessage');
        $this->storage->expects($this->once())->method('save')->willThrowException(new \Exception('testFailed'));
        $this->logger->expects($this->once())->method('alert');

        $this->invokeHiddenMethod($this->strategy, 'onMaxRetryReached', [$this->message]);
    }
}
