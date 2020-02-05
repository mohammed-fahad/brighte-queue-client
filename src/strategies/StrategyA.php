<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

class StrategyA extends AbstractRetryStrategy
{
    public function handle(Message $message): void
    {
        echo "Saving to DB \n";
    }

    protected function onMaxRetryReached(Message $message): void
    {
        // TODO: Implement onMaxRetryReached() method.
    }
}
