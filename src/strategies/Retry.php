<?php

namespace BrighteCapital\QueueClient\strategies;

class Retry
{
    protected $delay;

    protected $maxRetryCount;

    protected $strategy;

    protected $errorMessage;

    public function __construct(int $delays, int $maxRetryCount, string $strategy, string $errorMessage = null)
    {
        $this->delay = $delays;
        $this->maxRetryCount = $maxRetryCount;
        $this->strategy = $strategy;
        $this->errorMessage = $errorMessage ?? '';
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

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
