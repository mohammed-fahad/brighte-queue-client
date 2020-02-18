<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\container\Container;
use Interop\Queue\Message;

class BlockerStrategy extends AbstractStrategy
{
    /**
     * @param Message $message
     * @throws \Exception
     */
    protected function onMaxRetryReached(Message $message): void
    {
        $config = Container::instance()->get('Config');

        $this->client->delay($message, $config['retryStrategy']['storedMessageRetryDelay']);
    }
}
