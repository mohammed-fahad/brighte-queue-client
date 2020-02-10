<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\strategies\AbstractRetryStrategy;
use BrighteCapital\QueueClient\strategies\DefaultRetryStrategy;
use BrighteCapital\QueueClient\strategies\Retry;
use BrighteCapital\QueueClient\strategies\StorageRetryStrategy;

class StrategyFactory
{
    /**
     * @param Retry|null $retry
     * @return AbstractRetryStrategy
     * @throws \Exception
     */
    public static function create(Retry $retry = null): AbstractRetryStrategy
    {
        if (!$retry) {
            $retry = new Retry(0, 0, DefaultRetryStrategy::class);
        }

        switch ($retry->getStrategy()) {
            case DefaultRetryStrategy::class:
                return new DefaultRetryStrategy($retry);

            case StorageRetryStrategy::class:
                $storage = new StorageRetryStrategy($retry);
                return $storage;
        }
    }
}
