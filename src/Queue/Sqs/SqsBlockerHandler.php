<?php

namespace BrighteCapital\QueueClient\Queue\Sqs;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Queue\BlockerHandlerInterface;
use BrighteCapital\QueueClient\Queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;

class SqsBlockerHandler implements BlockerHandlerInterface
{
    /** @var QueueClientInterface */
    private $client;
    /** @var \BrighteCapital\QueueClient\Storage\MessageStorageInterface  */
    private $storage;
    /** @var int */
    private $delay;

    /**
     * BlockerChecker constructor.
     * @param QueueClientInterface $client
     * @param MessageStorageInterface|null $storage
     * @param int $delay
     * @throws \Exception
     */
    public function __construct(QueueClientInterface $client, int $delay = 0, MessageStorageInterface $storage = null)
    {
        $this->client = $client;
        $this->delay = $delay;
        $this->storage = $storage;
    }

    /**
     * @param Job $job
     * @return bool
     * @throws \Exception
     */
    public function checkAndHandle(Job $job): bool
    {
        $message = $job->getMessage();

        if ($message->getProperty('ApproximateReceiveCount') < $job->getRetry()->getMaxRetryCount()) {
            return false;
        }

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

            return;
        }

        $currentReciveCount = $message->getProperty('ApproximateReceiveCount');
        $maxRetry = $job->getRetry()->getMaxRetryCount();

        if ($oldEntity !== false) {
            $oldEntity->setMessageHandle($entity->getMessageHandle());
            $oldEntity->setAlertCount($currentReciveCount - $maxRetry);
            $this->storage->update($oldEntity);
        }
    }
}
