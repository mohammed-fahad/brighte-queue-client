<?php

namespace BrighteCapital\QueueClient\strategies;

interface RetryAbleInterface
{
    public function getDelay(): int;

    public function getRetryCount(): int;

    public function getStrategyClass(): String;
}
