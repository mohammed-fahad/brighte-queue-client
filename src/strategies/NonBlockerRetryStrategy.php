<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

class NonBlockerRetryStrategy extends AbstractRetryRetryStrategy
{
    /**
     * @param Message $message
     * @throws \Exception
     */
    protected function onMaxRetryReached(Message $message): void
    {
        $this->client->reject($message);
    }
}
