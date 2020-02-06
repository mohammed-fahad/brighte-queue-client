<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\strategies\AbstractRetryStrategy;
use BrighteCapital\QueueClient\strategies\DefaultRetryStrategy;
use BrighteCapital\QueueClient\strategies\Retry;
use BrighteCapital\QueueClient\strategies\StorageRetryStrategy;

class StrategyFactory
{
    /**
     * @param Retry|null $retry
     * @param QueueClientInterface $queueClient
     * @param array $config
     * @return AbstractRetryStrategy
     * @throws \Exception
     */
    public static function create(Retry $retry = null, QueueClientInterface $queueClient, array $config): AbstractRetryStrategy
    {
        if (!$retry) {
            $retry = new Retry(0, 0, DefaultRetryStrategy::class);
        }

        switch ($retry->getStrategy()) {
            case DefaultRetryStrategy::class:
                return new DefaultRetryStrategy($queueClient, $retry);

            case StorageRetryStrategy::class:
                $storage =  new StorageRetryStrategy($queueClient, $retry);
                $storage->setConfig($config['database']);
                $storage->setup();
                return $storage;
        }
    }
}
