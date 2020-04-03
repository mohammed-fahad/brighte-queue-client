<?php

namespace BrighteCapital\QueueClient\Strategies;

use Interop\Queue\Message;

class NonBlockerRetryStrategy extends AbstractRetryStrategy
{
    /**
     * @param Message $message
     * @throws \Exception
     */
    protected function onMaxRetryReached(Message $message): void
    {
        $this->logger->debug('On Max Retry Reached, Message deleted.', [
            'messageId' => $message->getMessageId()
        ]);
        $this->client->reject($message);
    }
}
