<?php

namespace BrighteCapital\QueueClient\Queue\Factories;

use BrighteCapital\QueueClient\Notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\Queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use BrighteCapital\QueueClient\Strategies\AbstractRetryRetryStrategy;
use BrighteCapital\QueueClient\Strategies\BlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use Psr\Log\LoggerInterface;

class StrategyFactory
{
    /** @var QueueClientInterface */
    protected $client;
    /** @var MessageStorageInterface */
    protected $storage;
    /** @var int */
    protected $defaultDelay;
    /** @var LoggerInterface */
    protected $logger;
    /** @var NotificationChannelInterface */
    protected $notification;

    public function __construct(
        QueueClientInterface $client,
        MessageStorageInterface $storage,
        LoggerInterface $logger,
        NotificationChannelInterface $notification,
        $defaultDelay = 3600
    ) {
        $this->client = $client;
        $this->storage = $storage;
        $this->defaultDelay = $defaultDelay;
        $this->logger = $logger;
        $this->notification = $notification;
    }

    /**
     * @param Retry $retry
     * @return AbstractRetryRetryStrategy
     */
    public function create(Retry $retry): AbstractRetryRetryStrategy
    {
        switch ($retry->getStrategy()) {
            case BlockerRetryStrategy::class:
                return new BlockerRetryStrategy(
                    $retry,
                    $this->client,
                    $this->defaultDelay,
                    $this->logger,
                    $this->notification
                );
            case BlockerStorageRetryStrategy::class:
                return new BlockerStorageRetryStrategy(
                    $retry,
                    $this->client,
                    $this->defaultDelay,
                    $this->logger,
                    $this->notification,
                    $this->storage
                );
            case NonBlockerRetryStrategy::class:
                return new NonBlockerRetryStrategy(
                    $retry,
                    $this->client,
                    $this->defaultDelay,
                    $this->logger,
                    $this->notification
                );
        }
    }
}
