<?php

namespace App\Test\Queue\Sqs;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Sqs\SqsBlockerHandler;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use BrighteCapital\QueueClient\Storage\NullStorage;
use BrighteCapital\QueueClient\Strategies\BlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Enqueue\Sqs\SqsMessage;
use Interop\Queue\Queue;
use Psr\Log\NullLogger;

class SqsBlockerHandlerTest extends BaseTestCase
{
    protected $queue;
    protected $sqsClient;
    protected $storage;
    protected $logger;
    protected $notification;
    protected $blockerHandler;
    protected $sqsMessage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queue = $this->createMock(Queue::class);
        $this->queue->method('getQueueName')->willReturn('test');
        $this->sqsClient = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();
        $this->sqsClient->method('getDestination')->willReturn($this->queue);
        $this->storage = $this->getMockBuilder(NullStorage::class)->getMock();
        $this->logger = $this->getMockBuilder(NullLogger::class)->getMock();
        $this->notification = new NullNotificationChannel();
        $this->sqsMessage = new SqsMessage('text', [], ['message_id' => '123']);
        $this->blockerHandler =
            new SqsBlockerHandler($this->sqsClient, 2, $this->logger, $this->notification, $this->storage);
    }

    public function testCheckAndHandler()
    {
        $this->sqsMessage->setProperty('ApproximateReceiveCount', 1);
        $job = new Job($this->sqsMessage, new Retry(0, 4, NonBlockerRetryStrategy::class));
        $this->assertFalse($this->blockerHandler->checkAndHandle($job));
    }

    public function testCheckAndHandlerHandled()
    {
        $this->sqsMessage->setProperty('ApproximateReceiveCount', 2);
        $job = new Job($this->sqsMessage, new Retry(0, 0, NonBlockerRetryStrategy::class));
        $this->sqsClient->expects($this->once())->method('reject');
        $this->storage->expects($this->never())->method('save');
        $this->assertTrue($this->blockerHandler->checkAndHandle($job));
    }

    public function testCheckAndHandlerHandledBlockerStrategy()
    {
        $this->sqsMessage->setProperty('ApproximateReceiveCount', 2);
        $job = new Job($this->sqsMessage, new Retry(0, 0, BlockerRetryStrategy::class));
        $this->sqsClient->expects($this->once())->method('delay');
        $this->storage->expects($this->never())->method('save');
        $this->assertTrue($this->blockerHandler->checkAndHandle($job));
    }

    public function testCheckAndHandlerStorageStrategy()
    {
        $this->sqsMessage->setProperty('ApproximateReceiveCount', 2);
        $job = new Job($this->sqsMessage, new Retry(0, 0, NonBlockerStorageRetryStrategy::class));
        $this->sqsClient->expects($this->once())->method('reject');
        $this->storage->expects($this->once())->method('get');
        $this->storage->expects($this->once())->method('save');
        $this->assertTrue($this->blockerHandler->checkAndHandle($job));
    }

    public function testCheckAndHandlerBlockerStorageStrategy()
    {
        $this->sqsMessage->setProperty('ApproximateReceiveCount', 2);
        $job = new Job($this->sqsMessage, new Retry(0, 0, BlockerStorageRetryStrategy::class));
        $this->sqsClient->expects($this->once())->method('delay');
        $this->storage->expects($this->once())->method('get');
        $this->storage->expects($this->once())->method('save');
        $this->assertTrue($this->blockerHandler->checkAndHandle($job));
    }

    public function testHandleStorage()
    {
        $this->sqsMessage->setReceiptHandle('test');
        $job = new Job($this->sqsMessage, new Retry(0, 0, BlockerStorageRetryStrategy::class));
        $messageEntity = new MessageEntity($this->sqsMessage);
        $messageEntity->setMessageHandle('testHandle');
        $this->storage->expects($this->once())->method('get')->willReturn($messageEntity);
        $this->logger->expects($this->once())->method('debug')
            ->with('Queue message stored in storage');
        $this->invokeHiddenMethod($this->blockerHandler, 'handleStorage', [$job]);
    }

    public function testHandleStorageWithStorageException()
    {
        $this->sqsMessage->setReceiptHandle('test');
        $job = new Job($this->sqsMessage, new Retry(0, 0, BlockerStorageRetryStrategy::class));
        $this->storage->expects($this->once())->method('get')->willThrowException(new \Exception('error'));
        $this->logger->expects($this->once())->method('error')
            ->with(SqsBlockerHandler::class . '::handleStorage: failed to save message');
        $this->invokeHiddenMethod($this->blockerHandler, 'handleStorage', [$job]);
    }
}
