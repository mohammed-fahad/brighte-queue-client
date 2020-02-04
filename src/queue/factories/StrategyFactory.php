<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\strategies\AbstractRetryStrategy;
use BrighteCapital\QueueClient\strategies\RetryAbleInterface;

class StrategyFactory
{
    public static function create(RetryAbleInterface $retry): AbstractRetryStrategy
    {
        try {
            $reflectionClass = new \ReflectionClass($retry->getStrategy());
        } catch (\ReflectionException $e) {
            throw new ReflectionException($e->getMessage());
        }

        return $reflectionClass->newInstance();
    }
}
