<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

abstract class AbstractRetryStrategy implements RetryStrategyInterface
{
    abstract function onMaxRetryReached(Message $message);
}
