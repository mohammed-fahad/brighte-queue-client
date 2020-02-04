<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

class StrategyA extends AbstractRetryStrategy
{
    public function handle(Message $message): void
    {
        echo "Saving to DB \n";
    }
}
