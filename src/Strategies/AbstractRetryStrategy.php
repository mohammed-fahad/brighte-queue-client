<?php

namespace BrighteCapital\QueueClient\Strategies;

use BrighteCapital\QueueClient\Notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\Queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;

abstract class AbstractRetryStrategy implements RetryStrategyInterface
{
    /** @var QueueClientInterface */
    protected $client;

    /** @var Retry */
    protected $retry;

    /** @var int */
    protected $delay;

    /** @var MessageStorageInterface */
    protected $storage;

    /** @var NotificationChannelInterface */
    protected $notification;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * AbstractRetryStrategy constructor.
     * @param Retry $retry
     * @param QueueClientInterface $client
     * @param int $delay
     * @param LoggerInterface $logger
     * @param NotificationChannelInterface $notification
     * @param MessageStorageInterface|null $storage
     */
    public function __construct(
        Retry $retry,
        QueueClientInterface $client,
        int $delay,
        LoggerInterface $logger,
        NotificationChannelInterface $notification,
        MessageStorageInterface $storage = null
    ) {
        $this->client = $client;
        $this->retry = $retry;
        $this->delay = $delay;
        $this->logger = $logger;
        $this->storage = $storage;
        $this->notification = $notification;
    }

    public function handle(Message $message): void
    {
        $attemptCount = $message->getProperty('ApproximateReceiveCount');
        if ($attemptCount < $this->retry->getMaxRetryCount()) {
            $this->logger->debug('Message Delayed', [
                'messageId' => $message->getMessageId(),
                'retry' => print_r($this->retry, true)
            ]);
            $this->client->delay($message, $this->retry->getDelay());

            return;
        }

        if ($attemptCount >= $this->retry->getMaxRetryCount()) {
            $issue = static::class . ': Message have reached maximum retry and need attention';
            $info = [
                'messageId' => $message->getMessageId(),
                'level' =>  $attemptCount - $this->retry->getMaxRetryCount(),
                'body' => $message->getBody(),
                'lastError' => $this->retry->getErrorMessage(),
                'messageHandle' => $message->getReceiptHandle()
            ];
            $this->notification->send(['issue' => $issue] + $info);

            $this->logger->critical($issue, $info);

            $this->onMaxRetryReached($message);
        }
    }

    abstract protected function onMaxRetryReached(Message $message): void;
}
