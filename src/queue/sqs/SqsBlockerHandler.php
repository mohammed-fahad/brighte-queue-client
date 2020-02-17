<?php

namespace BrighteCapital\QueueClient\queue\sqs;

use BrighteCapital\QueueClient\container\Container;
use BrighteCapital\QueueClient\queue\BlockerHandlerInterface;
use BrighteCapital\QueueClient\queue\Job;
use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use Interop\Queue\Message;

class SqsBlockerHandler implements BlockerHandlerInterface
{
    /** @var QueueClientInterface */
    private $client;
    /** @var \BrighteCapital\QueueClient\Storage\StorageInterface  */
    private $storage;
    /** @var array */
    private $config;

    /**
     * BlockerChecker constructor.
     * @param QueueClientInterface $client
     * @throws \Exception
     */
    public function __construct(QueueClientInterface $client)
    {
        $this->client = $client;
        $this->config = Container::instance()->get('Config');

        try {
            $this->storage = Container::instance()->get('Storage');
        } catch (\Exception $e) {
            // Do nothing
        }
    }

    /**
     * @param Job $job
     * @return bool
     * @throws \Exception
     */
    public function checkAndHandle(Job $job): bool
    {
        $message = $job->getMessage();

        if ($message->getProperty('ApproximateReceiveCount') <= $job->getMaxRetry()) {
            return false;
        }

        $this->client->delay($message, $this->config['retryStrategy']['storedMessageRetryDelay']);

        if (!empty($this->storage)) {
            $this->handleStorage($message);
        }

        return true;
    }

    /**
     * @param $message
     */
    private function handleStorage($message)
    {
        $entity = new MessageEntity($message);

        /** @var MessageEntity $oldEntity */
        $oldEntity = $this->storage->messageExist($entity);

        if ($oldEntity === false) {
            $entity->setQueueName($this->config['queue']);
            $this->storage->store($entity);
        }

        if ($oldEntity !== false) {
            $entity->setId($oldEntity->getId());
            $entity->setAlertCount($oldEntity->getAlertCount() + 1);
            $this->storage->update($entity);
        }
    }
}
