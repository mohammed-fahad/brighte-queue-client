<?php

namespace App\Test\Strategies;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use BrighteCapital\QueueClient\Strategies\AbstractRetryStrategy;
use Enqueue\Sqs\SqsMessage;
use Exception;
use Interop\Queue\Message;
use Interop\Queue\Queue;
use Psr\Log\NullLogger;

class AbstractRetryStrategyTest extends BaseTestCase
{
    protected $client;
    protected $job;
    protected $logger;
    protected $notification;
    protected $message;
    /** @var AbstractRetryStrategy */
    protected $strategy;

    protected function setUp()
    {
        parent::setUp();
        $this->queue = $this->createMock(Queue::class);
        $this->queue->method('getQueueName')->willReturn('test');
        $this->client = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();
        $this->client->method('getDestination')->willReturn($this->queue);
        $this->job = $this->getMockBuilder(Job::class)->disableOriginalConstructor()->getMock();
        $this->job->method('getErrorMessage')->willReturn('something went wrong');
        $this->job->method('shouldNotify')->willReturn(true);
        $this->logger = $this->getMockBuilder(NullLogger::class)->disableOriginalConstructor()->getMock();
        $this->notification = $this
            ->getMockBuilder(NullNotificationChannel::class)->disableOriginalConstructor()->getMock();
        $this->storage = $this->createMock(MessageStorageInterface::class);
        $this->message = $this->getMockBuilder(SqsMessage::class)->disableOriginalConstructor()->getMock();

        $anonymousClass = new class (
            $this->job,
            $this->client,
            1,
            $this->logger,
            $this->notification,
            $this->storage
        ) extends AbstractRetryStrategy {
            public function getClass()
            {
                return $this;
            }

            protected function onMaxRetryReached(Message $message): void
            {
                //Do Nothing
            }
        };
        $this->strategy = $anonymousClass->getClass();
    }

    public function testHandle()
    {
        $this->message->expects($this->once())->method('getProperty')->willReturn(1);
        $this->job->expects($this->once())->method('getMaxRetryCount')->willReturn(2);
        $this->client->expects($this->once())->method('delay');
        $this->strategy->handle($this->message);
    }

    public function testHandleReachMax()
    {
        $this->message->expects($this->once())->method('getProperty')->willReturn(2);
        $this->job->expects($this->atLeast(2))->method('getMaxRetryCount')->willReturn(1);
        $this->client->expects($this->never())->method('delay');
        $this->notification->expects($this->once())->method('send');
        $this->logger->expects($this->once())->method('critical');
        $this->strategy->handle($this->message);
    }

    public function testStoreMessage()
    {
        $this->storage->expects($this->once())->method('save');
        $this->logger->expects($this->once())->method('debug');
        $this->strategy->storeMessage($this->message);
    }

    public function testStoreMessageWithError()
    {
        $this->storage->expects($this->once())->method('save')->willThrowException(new Exception('oops'));
        $this->logger->expects($this->once())->method('alert');
        $this->strategy->storeMessage($this->message);
    }
}
