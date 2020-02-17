<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

interface StrategyInterface
{
    public function handle(Message $message): void;
}
