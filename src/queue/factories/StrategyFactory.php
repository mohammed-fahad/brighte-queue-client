<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\strategies\AbstractRetryStrategy;
use BrighteCapital\QueueClient\strategies\RetryAbleInterface;
use ReflectionException;

class StrategyFactory
{
    /**
     * @param RetryAbleInterface $retry
     * @param QueueClientInterface $queueClient
     * @param array $config
     * @return AbstractRetryStrategy
     * @throws ReflectionException
     */
    public static function create(RetryAbleInterface $retry, QueueClientInterface $queueClient, array $config): AbstractRetryStrategy
    {
        try {
            $reflectionClass = new \ReflectionClass($retry->getStrategy());
        } catch (\ReflectionException $e) {
            throw new ReflectionException($e->getMessage());
        }

        return $reflectionClass->newInstanceArgs(['queueClient' => $queueClient, 'retry' => $retry, 'config' => $config]);
    }
}
