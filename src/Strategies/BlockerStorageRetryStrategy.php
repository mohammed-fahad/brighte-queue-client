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
            $this->logger->debug('On Max Retry Reached, Message is being stored.', [
                'messageId' => $message->getMessageId(),
                'delayInSecond' => $this->delay
            ]);
            /** @var MessageStorageInterface $storage */
            $this->storage->store($messageEntity);
        } catch (Exception $e) {
            $this->logger->alert('On Max Retry Reached, Storing failed.', [
                'messageId' => $message->getMessageId(), 'exception' => $e->getMessage()
            ]);
        }
    }
}
