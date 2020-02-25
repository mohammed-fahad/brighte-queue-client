<?php

namespace BrighteCapital\QueueClient\Strategies;

use Interop\Queue\Message;

interface RetryStrategyInterface
{
    public function handle(Message $message): void;
}
