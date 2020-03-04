<?php

namespace BrighteCapital\QueueClient\Strategies;

use BrighteCapital\QueueClient\Storage\MessageEntity;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use Exception;
use Interop\Queue\Message;

class BlockerStorageRetryStrategy extends BlockerRetryStrategy
{
    /**
     * @param Message $message
     * @throws Exception
     */
    protected function onMaxRetryReached(Message $message): void
    {
        parent::onMaxRetryReached($message);

        $messageEntity = new MessageEntity($message);
        $messageEntity->setLastErrorMessage($this->retry->getErrorMessage());
        $messageEntity->setQueueName($this->client->getDestination()->getQueueName());

        try {
            /** @var MessageStorageInterface $storage */
            $storage->store($messageEntity);
        } catch (Exception $e) {
            // log critical
        }
    }
}
