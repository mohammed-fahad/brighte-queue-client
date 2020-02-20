<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\container\Container;
use BrighteCapital\QueueClient\queue\BrighteQueueClient;
use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\strategies\AbstractRetryRetryStrategy;
use BrighteCapital\QueueClient\strategies\BlockerRetryStrategy;
use BrighteCapital\QueueClient\strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\strategies\Retry;
use BrighteCapital\QueueClient\strategies\BlockerStorageRetryStrategy;

class StrategyFactory
{
    /**
     * @param Retry|null $retry
     * @return AbstractRetryRetryStrategy
     * @throws \Exception
     */
    public static function create(Retry $retry = null): AbstractRetryRetryStrategy
    {
        if (!$retry) {
            $retry = new Retry(0, 0, BlockerRetryStrategy::class);
        }

        /** @var QueueClientInterface $client */
        $client = Container::instance()->get('QueueClient');
        $config = Container::instance()->get('Config');
        $storage = Container::instance()->get('Storage');

        $delay = $config['retryStrategy']['storedMessageRetryDelay'];

        switch ($retry->getStrategy()) {
            case BlockerRetryStrategy::class:
                return new BlockerRetryStrategy($retry, $client);

            case BlockerStorageRetryStrategy::class:
                return new BlockerStorageRetryStrategy($retry, $client, $delay);

            case NonBlockerRetryStrategy::class:
                return new NonBlockerRetryStrategy($retry, $client, $delay, $storage);
        }
    }
}
