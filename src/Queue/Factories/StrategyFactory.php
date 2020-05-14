<?php

namespace BrighteCapital\QueueClient\Queue\Factories;

use BrighteCapital\QueueClient\Notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\Queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use BrighteCapital\QueueClient\Strategies\AbstractRetryStrategy;
use BrighteCapital\QueueClient\Strategies\BlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerStorageRetryStrategy;
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
     * @return AbstractRetryStrategy
     * @throws \Exception
     */
    public function create(Retry $retry): AbstractRetryStrategy
    {
        $strategry = $retry->getStrategy();
        switch ($strategry) {
            case BlockerRetryStrategy::class:
            case BlockerStorageRetryStrategy::class:
            case NonBlockerRetryStrategy::class:
            case NonBlockerStorageRetryStrategy::class:
                return new $strategry(
                    $retry,
                    $this->client,
                    $this->defaultDelay,
                    $this->logger,
                    $this->notification,
                    $this->storage
                );
        }

        throw new \Exception('Given Strategy is not defined : ' . $retry->getStrategy());
    }
}
