<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\strategies\AbstractRetryStrategy;
use BrighteCapital\QueueClient\strategies\RetryAbleInterface;
use ReflectionException;

class StrategyFactory
{
    public static function create(RetryAbleInterface $retry, QueueClientInterface $queueClient): AbstractRetryStrategy
    {
        try {
            $reflectionClass = new \ReflectionClass($retry->getStrategy());
        } catch (\ReflectionException $e) {
            throw new ReflectionException($e->getMessage());
        }

        return $reflectionClass->newInstanceArgs($queueClient);
    }
}
