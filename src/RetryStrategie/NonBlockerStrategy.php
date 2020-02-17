<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

class NonBlockerStrategy extends AbstractStrategy
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
