<?php

namespace App\Test\Strategies;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Strategies\BlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Enqueue\Sqs\SqsMessage;
use Psr\Log\NullLogger;

class BlockerRetryStrategyTest extends BaseTestCase
{
    protected const MAX_DELAY = 2;
    protected $client;
    protected $retry;
    protected $logger;
    protected $notification;
    protected $message;
    /** @var BlockerRetryStrategy */
    protected $strategy;

    protected function setUp()
    {
        parent::setUp();
        $this->client = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();
        $this->retry = $this->getMockBuilder(Retry::class)->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(NullLogger::class)->disableOriginalConstructor()->getMock();
        $this->notification = $this
            ->getMockBuilder(NullNotificationChannel::class)->disableOriginalConstructor()->getMock();
        $this->message = $this->getMockBuilder(SqsMessage::class)->disableOriginalConstructor()->getMock();
        $this->strategy = new BlockerRetryStrategy(
            $this->retry,
            $this->client,
            self::MAX_DELAY,
            $this->logger,
            $this->notification
        );
    }

    public function testOn()
    {
        $this->client->expects($this->once())->method('delay')->with($this->message, self::MAX_DELAY);
        $this->invokeHiddenMethod($this->strategy, 'onMaxRetryReached', [$this->message]);
    }
}
