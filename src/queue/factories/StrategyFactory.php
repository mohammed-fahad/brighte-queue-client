<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\strategies\AbstractStrategy;
use BrighteCapital\QueueClient\strategies\BlockerStrategy;
use BrighteCapital\QueueClient\strategies\NonBlockerStrategy;
use BrighteCapital\QueueClient\strategies\Retry;
use BrighteCapital\QueueClient\strategies\BlockerStorageStrategy;

class StrategyFactory
{
    /**
     * @param Retry|null $retry
     * @return AbstractStrategy
     * @throws \Exception
     */
    public static function create(Retry $retry = null): AbstractStrategy
    {
        if (!$retry) {
            $retry = new Retry(0, 0, BlockerStrategy::class);
        }

        switch ($retry->getStrategy()) {
            case BlockerStrategy::class:
                return new BlockerStrategy($retry);

            case BlockerStorageStrategy::class:
                return new BlockerStorageStrategy($retry);

            case NonBlockerStrategy::class:
                return new NonBlockerStrategy($retry);
        }
    }
}
