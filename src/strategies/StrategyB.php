<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

class StrategyB extends AbstractRetryStrategy
{
    public function handle(Message $message): void
    {
        echo "Brighte Default Strategy";
    }
}

