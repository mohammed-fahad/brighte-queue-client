<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

class StrategyB extends AbstractRetryStrategy
{
    public function handle(Message $message): void
    {
        $this->onMaxRetryReached($message);
    }

    protected function onMaxRetryReached(Message $message): void
    {
        //do the heavey lifting here
    }
}
