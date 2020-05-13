<?php

namespace BrighteCapital\QueueClient\Strategies;

use DateTime;

class Retry
{
    protected $delay;
    protected $maxRetryCount;

    protected $strategy;

    protected $errorMessage;

    public function __construct(int $delays, int $maxRetryCount, string $strategy, string $errorMessage = '')
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

    /**
     * @param int $delay
     */
    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    /**
     * @param int $maxRetryCount
     */
    public function setMaxRetryCount(int $maxRetryCount): void
    {
        $this->maxRetryCount = $maxRetryCount;
    }

    /**
     * @param string $strategy
     */
    public function setStrategy(string $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @param string $errorMessage
     * @param string $separater
     * @param boolean $withTime
     * @return void
     */
    public function pushErrorMessage(string $errorMessage, string $separater = "\n", bool $withTime = true): void
    {
        if ($this->errorMessage) {
            $this->errorMessage = $separater . $this->errorMessage;
        }

        if ($withTime) {
            $errorMessage = (new DateTime())->format(DateTime::ISO8601) . ' ' . $errorMessage;
        }

        $this->errorMessage = $errorMessage . $this->errorMessage;
    }
}
