<?php

namespace BrighteCapital\QueueClient\strategies;

abstract class AbstractRetryStrategy implements RetryStrategyInterface
{
    /**
     * @var \App\strategies\FailedMessageInterface
     */
    protected $failedMessage;

    public function __construct(FailedMessageInterface $failedMessage)
    {
        $this->failedMessage = $failedMessage;
    }
}
