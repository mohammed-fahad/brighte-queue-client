<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

class StrategyB extends AbstractRetryStrategy
{
    public function handle(Message $message): void
    {
        $this->onMaxRetryReached($message);
    }

    function onMaxRetryReached(Message $message)
    {
        //do the heavey lifting here
    }
}
