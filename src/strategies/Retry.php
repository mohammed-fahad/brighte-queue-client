<?php

namespace BrighteCapital\QueueClient\strategies;

class Retry
{
    protected $delay;

    protected $maxRetryCount;

    private $strategy;

    public function __construct(int $delays, int $maxRetryCount, string $strategy)
    {
        $this->delay = $delays;
        $this->maxRetryCount = $maxRetryCount;
        $this->strategy = $strategy;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function getMaxRetryCount(): int
    {
        return $this->maxRetryCount;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }
}
