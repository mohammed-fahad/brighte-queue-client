<?php

namespace BrighteCapital\QueueClient\Queue\Sqs;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\Queue\BlockerHandlerInterface;
use BrighteCapital\QueueClient\Queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use BrighteCapital\QueueClient\Strategies\BlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerStorageRetryStrategy;
use Enqueue\Sqs\SqsMessage;
use Psr\Log\LoggerInterface;

class SqsBlockerHandler implements BlockerHandlerInterface
{
    /** @var QueueClientInterface */
    protected $client;
    /** @var \BrighteCapital\QueueClient\Storage\MessageStorageInterface  */
    protected $storage;
    /** @var int */
    protected $delay;
    /** @var LoggerInterface */
    protected $logger;
    /** @var NotificationChannelInterface */
    protected $notification;

    /**
     * BlockerChecker constructor.
     * @param QueueClientInterface $client
     * @param int $delay
     * @param LoggerInterface $logger
     * @param NotificationChannelInterface $notification
     * @param MessageStorageInterface|null $storage
     */
    public function __construct(
        QueueClientInterface $client,
        int $delay,
        LoggerInterface $logger,
        NotificationChannelInterface $notification,
        MessageStorageInterface $storage = null
    ) {
        $this->client = $client;
        $this->delay = $delay;
        $this->storage = $storage;
        $this->logger = $logger;
        $this->notification = $notification;
    }

    /**
     * @param Job $job
     * @return bool
     * @throws \Exception
     */
    public function checkAndHandle(Job $job): bool
    {
        /** @var SqsMessage */
        $message = $job->getMessage();

        if (!$this->reachedMaxRetry($job)) {
            return false;
        }

        $issue = sprintf(
            '[%s][%s] Message have reached maximum retry and need attention',
            $this->client->getDestination()->getQueueName(),
            $this->getAlertCount($job) //level
        );
        $info = [
            'class' => static::class,
            'messageId' => $message->getMessageId(),
            'retryCount' => $message->getProperty('ApproximateReceiveCount'),
            'body' => $message->getBody(),
            'lastError' => $job->getErrorMessage(),
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

        $useStorage = in_array($job->getStrategy(), [
            BlockerStorageRetryStrategy::class,
            NonBlockerStorageRetryStrategy::class,
        ]);

        $isBlocker = in_array($job->getStrategy(), [
            BlockerRetryStrategy::class,
            BlockerStorageRetryStrategy::class,
        ]);

        if ($useStorage) {
            $this->handleStorage($job);
        }

        if ($isBlocker) {
            $this->client->delay($message, $this->delay);
        } else {
            $this->client->reject($message);
        }

        return true;
    }

    /**
     * @param Job $job
     */
    private function handleStorage(Job $job)
    {
        /** @var SqsMessage */
        $message = $job->getMessage();

        try {
            /** @var MessageEntity */
            $entity = $message->getMessageId()
                ? $this->storage->get($message->getMessageId())
                : null;

            if ($entity) {
                $entity->setMessageHandle($message->getReceiptHandle());
                $entity->setAlertCount($this->getAlertCount($job));
            } else {
                $entity = new MessageEntity($message);
                $entity->setQueueName($this->client->getDestination()->getQueueName());
            }

            $this->storage->save($entity);
            $this->logger->debug('Queue message stored in storage', [
                'messageId' => $entity->getMessageId()
            ]);
        } catch (\Exception $e) {
            $this->logger->error(__METHOD__ . ': failed to save message', [
                'exception' => $e,
                'messageId' => $message->getMessageId()
            ]);
        }
    }

    private function reachedMaxRetry(Job $job): bool
    {
        return $this->getAlertCount($job) > 0;
    }

    private function getAlertCount(Job $job): int
    {
        $attemptCount = $job->getMessage()->getProperty('ApproximateReceiveCount');
        $maxRetry = $job->getMaxRetryCount();

        return $attemptCount - $maxRetry;
    }
}
