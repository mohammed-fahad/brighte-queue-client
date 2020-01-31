<?php


namespace BrighteCapital\QueueClient\strategies;


interface RetryStrategyInterface
{
    /**
     * @return bool
     */
    public function handle(): bool;
}
