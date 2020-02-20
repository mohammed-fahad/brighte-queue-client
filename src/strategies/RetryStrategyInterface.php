<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

interface RetryStrategyInterface
{
    public function handle(Message $message): void;
}
