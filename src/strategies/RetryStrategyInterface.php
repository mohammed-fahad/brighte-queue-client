<?php

declare(strict_types = 1);

namespace BrighteCapital\QueueClient\strategies;

interface RetryStrategyInterface
{
    public function handle(): bool;
}
