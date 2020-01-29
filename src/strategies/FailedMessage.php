<?php


namespace App\strategies;


class FailedMessage implements FailedMessageInterface
{
    /**
     * @var \Enqueue\strategies\RetryStrategyInterface
     */
    protected $retryStrategy;
    protected $delay;
    protected $retries;

    public function __construct(RetryStrategyInterface $retryStrategy, $delays, $retries)
    {
        $this->retryStrategy = $retryStrategy;
        $this->delay = $delays;
        $this->retries = $retries;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function getMaxRetries(): int
    {
        return $this->retries;
    }

    public function getHandler(): RetryStrategyInterface
    {
        return $this->getHandler();
    }
}
