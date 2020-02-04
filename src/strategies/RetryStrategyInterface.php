<?php

declare(strict_types=1);

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

interface RetryStrategyInterface
{
    public function handle(Message $message): bool;
}
