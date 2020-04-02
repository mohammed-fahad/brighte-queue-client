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
        $this->logger->debug(printf('%s: Message deleted.', __METHOD__), [
            'messageId' => $message->getMessageId()
        ]);
        $this->client->reject($message);
    }
}
