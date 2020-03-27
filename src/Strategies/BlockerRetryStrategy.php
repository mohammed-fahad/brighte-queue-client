<?php

namespace BrighteCapital\QueueClient\Strategies;

use Interop\Queue\Message;

class BlockerRetryStrategy extends AbstractRetryStrategy
{
    /**
     * @param Message $message
     * @throws \Exception
     */
    protected function onMaxRetryReached(Message $message): void
    {
        $this->client->delay($message, $this->delay);
    }
}
