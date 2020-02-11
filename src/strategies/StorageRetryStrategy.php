<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\container\Container;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use BrighteCapital\QueueClient\Storage\StorageInterface;
use Exception;
use Interop\Queue\Message;

class StorageRetryStrategy extends AbstractRetryStrategy
{
    /**
     * @param Message $message
     * @throws Exception
     */
    protected function onMaxRetryReached(Message $message): void
    {
        $config = Container::instance()->get('Config');

        $messageEntity = new MessageEntity($message);
        $messageEntity->setLastErrorMessage($this->retry->getErrorMessage());
        $messageEntity->setQueueName($config['queue']);
        $this->client->delay($message, $config['retryStrategy']['storedMessageRetryDelay']);
        try {
            $storage = Container::instance()->get('Storage');
            /** @var StorageInterface $storage */
            $storage->store($messageEntity);
        } catch (Exception $e) {
            // log critical
        }
    }
}
