<?php

namespace App\Test\Strategies;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use BrighteCapital\QueueClient\Storage\NullStorage;
use BrighteCapital\QueueClient\Strategies\NonBlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Enqueue\Sqs\SqsDestination;
use Enqueue\Sqs\SqsMessage;
use Psr\Log\NullLogger;

class NonBlockerStorageRetryStrategyTest extends BaseTestCase
{
    protected const MAX_DELAY = 2;
    protected $client;
    protected $retry;
    protected $logger;
    protected $storage;
    protected $notification;
    protected $message;
    /** @var NonBlockerStorageRetryStrategy */
    protected $strategy;

    protected function setUp()
    {
        parent::setUp();
        $this->client = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();
        $this->retry = $this->getMockBuilder(Retry::class)->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(NullLogger::class)->disableOriginalConstructor()->getMock();
        $this->storage = $this->getMockBuilder(NullStorage::class)->disableOriginalConstructor()->getMock();
        $this->notification = $this
            ->getMockBuilder(NullNotificationChannel::class)->disableOriginalConstructor()->getMock();
        $this->storage = $this->createMock(MessageStorageInterface::class);
        $this->message = $this->getMockBuilder(SqsMessage::class)->disableOriginalConstructor()->getMock();
        $this->strategy = new NonBlockerStorageRetryStrategy(
            $this->retry,
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

        $this->client->expects($this->once())->method('reject')->with($this->message);
        $this->client->expects($this->once())->method('getDestination')->willReturn($destination);
        $this->retry->expects($this->once())->method('getErrorMessage')->willReturn('failedMessage');
        $this->storage->expects($this->once())->method('save')->willThrowException(new \Exception('testFailed'));
        $this->logger->expects($this->once())->method('alert');

        $this->invokeHiddenMethod($this->strategy, 'onMaxRetryReached', [$this->message]);
    }
}
