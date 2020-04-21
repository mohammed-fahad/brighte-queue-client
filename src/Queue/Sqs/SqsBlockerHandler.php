<?php

namespace BrighteCapital\QueueClient\Queue\Sqs;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\Queue\BlockerHandlerInterface;
use BrighteCapital\QueueClient\Queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
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
        $message = $job->getMessage();

        if (!$this->reachedMaxRetry($job)) {
            return false;
        }

        $issue = sprintf(
            '[%s][%s] %s: Message have reached maximum retry and need attention',
            $this->client->getDestination()->getQueueName(),
            $this->getAlertCount($job), //level
            static::class
        );
        $info = [
            'messageId' => $message->getMessageId(),
            'retryCount' => $message->getProperty('ApproximateReceiveCount'),
            'body' => $message->getBody(),
            'lastError' => $job->getRetry()->getErrorMessage(),
            'messageHandle' => $message->getReceiptHandle(),
        ];

        $this->notification->send(['title' => $issue] + $info);

        $this->logger->critical($issue, $info);

        // If non blocker strategy is used and it has reached the maximum, then delete it.
        if ($job->getRetry()->getStrategy() === NonBlockerRetryStrategy::class) {
            $this->client->reject($message);

            return true;
        }

        $this->client->delay($message, $this->delay);

        if ($job->getRetry()->getStrategy() === BlockerStorageRetryStrategy::class) {
            $this->handleStorage($job);
        }

        return true;
    }

    /**
     * @param Job $job
     */
    private function handleStorage(Job $job)
    {
        $message = $job->getMessage();
        $entity = new MessageEntity($message);

        /** @var MessageEntity $oldEntity */
        $oldEntity = $this->storage->messageExist($entity);

        if ($oldEntity === false) {
            $entity->setQueueName($this->client->getDestination()->getQueueName());
            $this->storage->store($entity);
            $this->logger->debug('Queue message stored in storage', ['messageId' => $message->getMessageId()]);

            return;
        }

        if ($oldEntity !== false) {
            $oldEntity->setMessageHandle($entity->getMessageHandle());
            $oldEntity->setAlertCount($this->getAlertCount($job));
            $this->logger->debug('Queue message updated in storage', ['messageId' => $message->getMessageId()]);
            $this->storage->update($oldEntity);
        }
    }

    private function reachedMaxRetry(Job $job): bool
    {
        return $this->getAlertCount($job) > 0;
    }

    private function getAlertCount(Job $job): int
    {
        $attemptCount = $job->getMessage()->getProperty('ApproximateReceiveCount');
        $maxRetry = $job->getRetry()->getMaxRetryCount();

        return $attemptCount - $maxRetry;
    }
}
