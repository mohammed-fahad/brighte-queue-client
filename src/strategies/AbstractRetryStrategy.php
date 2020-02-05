<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\queue\QueueClientInterface;
use Interop\Queue\Message;

abstract class AbstractRetryStrategy implements RetryStrategyInterface
{
    /**
     * @var \BrighteCapital\QueueClient\queue\QueueClientInterface
     */
    private $queueClient;
    /**
     * @var \BrighteCapital\QueueClient\strategies\Retry
     */
    private $retry;

    public function __construct(Retry $retry, QueueClientInterface $queueClient)
    {
        $this->queueClient = $queueClient;
        $this->retry = $retry;
    }

    abstract protected function onMaxRetryReached(Message $message): void;
}
