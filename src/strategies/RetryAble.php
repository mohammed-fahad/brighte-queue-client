<?php

namespace App\strategies;

use BrighteCapital\QueueClient\strategies\RetryAbleInterface;

class RetryAble implements RetryAbleInterface
{
    protected $delay;

    protected $retryCount;

    private $strategy;

    public function __construct(int $delays, int $retryCount, string $strategy)
    {
        $this->delay = $delays;
        $this->retryCount = $retryCount;
        $this->strategy = $strategy;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function getStrategyClass(): string
    {
        return $this->strategy;
    }
}
