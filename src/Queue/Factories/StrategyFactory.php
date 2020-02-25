<?php

namespace BrighteCapital\QueueClient\Queue\Factories;

use BrighteCapital\QueueClient\Queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use BrighteCapital\QueueClient\Strategies\AbstractRetryRetryStrategy;
use BrighteCapital\QueueClient\Strategies\BlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;

class StrategyFactory
{
    /** @var QueueClientInterface */
    protected $client;
    /** @var Retry */
    protected $retry;
    /** @var MessageStorageInterface */
    protected $storage;
    /** @var int */
    protected $defaultDelay;

    public function __construct(QueueClientInterface $client, Retry $retry, MessageStorageInterface $storage, $defaultDelay = 3600)
    {
        $this->client = $client;
        $this->retry = $retry;
        $this->storage = $storage;
        $this->defaultDelay = $defaultDelay;
    }

    /**
     * @return AbstractRetryRetryStrategy
     */
    public function create(): AbstractRetryRetryStrategy
    {
        switch ($this->retry->getStrategy()) {
            case BlockerRetryStrategy::class:
                return new BlockerRetryStrategy($this->retry, $this->client);

            case BlockerStorageRetryStrategy::class:
                return new BlockerStorageRetryStrategy($this->retry, $this->client, $this->defaultDelay);

            case NonBlockerRetryStrategy::class:
                return new NonBlockerRetryStrategy($this->retry, $this->client, $this->defaultDelay, $this->storage);
        }
    }
}
