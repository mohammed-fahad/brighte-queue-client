<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\strategies\RetryAbleInterface;
use BrighteCapital\QueueClient\strategies\RetryStrategyInterface;

class StrategyFactory
{
    public static function create(RetryAbleInterface $retry): RetryStrategyInterface
    {
        return new $retry->getStrategyClass();
    }
}
