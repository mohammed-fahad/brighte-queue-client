<?php

namespace BrighteCapital\QueueClient\Strategies;

use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use Interop\Queue\Message;

class NonBlockerStorageRetryStrategy extends NonBlockerRetryStrategy
{
    /**
     * @param Message $message
     * @throws \Exception
     */
    protected function onMaxRetryReached(Message $message): void
    {
        parent::onMaxRetryReached($message);

        $this->storeMessage($message);
    }
}
