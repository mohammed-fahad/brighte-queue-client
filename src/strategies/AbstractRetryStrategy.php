<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

abstract class AbstractRetryStrategy implements RetryStrategyInterface
{
    abstract protected function onMaxRetryReached(Message $message): void;
}
