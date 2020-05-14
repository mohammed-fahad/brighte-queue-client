<?php

namespace BrighteCapital\QueueClient\Strategies;

use BrighteCapital\QueueClient\Notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\Queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use Exception;
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
        MessageStorageInterface $storage
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
                'retry' => $this->retry
            ]);
            $this->client->delay($message, $this->retry->getDelay());

            return;
        }

        if ($attemptCount >= $this->retry->getMaxRetryCount()) {
            $issue = sprintf(
                '[%s][%s] Message have reached maximum retry and need attention',
                $this->client->getDestination()->getQueueName(),
                ($attemptCount - $this->retry->getMaxRetryCount())//level
            );
            $info = [
                'class' => static::class,
                'messageId' => $message->getMessageId(),
                'retryCount' => $attemptCount,
                'body' => $message->getBody(),
                'lastError' => $this->retry->getErrorMessage(),
                'messageHandle' => $message->getReceiptHandle(),
            ];

            $this->logger->critical($issue, $info);
            try {
                $this->notification->send(['title' => $issue] + $info);
            } catch (\Exception $e) {
                $this->logger->error(__METHOD__ . ': failed to notify on maximum retry reached', [
                    'exception' => $e,
                ]);
            }

            $this->onMaxRetryReached($message);
        }
    }

    abstract protected function onMaxRetryReached(Message $message): void;

    public function storeMessage(Message $message): void
    {
        $messageEntity = new MessageEntity($message);
        $messageEntity->setLastErrorMessage($this->retry->getErrorMessage());
        $messageEntity->setQueueName($this->client->getDestination()->getQueueName());
        try {
            /** @var MessageStorageInterface $storage */
            $this->storage->save($messageEntity);
            $this->logger->debug('On Max Retry Reached, Message is stored.', [
                'messageId' => $message->getMessageId(),
                'delayInSecond' => $this->delay
            ]);
        } catch (Exception $e) {
            $this->logger->alert('On Max Retry Reached, Storing failed.', [
                'exception' => $e->getMessage(),
                'messageId' => $message->getMessageId(),
            ]);
        }
    }
}
