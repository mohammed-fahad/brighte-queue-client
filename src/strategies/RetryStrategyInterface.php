<?php


namespace BrighteCapital\QueueClient\strategies;


interface RetryStrategyInterface
{
    public function handle(): bool;
}
