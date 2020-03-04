<?php

namespace BrighteCapital\QueueClient\Queue\Factories;

use BrighteCapital\QueueClient\Notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\Queue\BlockerHandlerInterface;
use BrighteCapital\QueueClient\Queue\QueueClientInterface;
use BrighteCapital\QueueClient\Queue\Sqs\SqsBlockerHandler;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use Psr\Log\LoggerInterface;

class BlockerHandlerFactory
{
    /** @var QueueClientInterface */
    protected $client;
    /** @var MessageStorageInterface */
    protected $storage;
    /** @var LoggerInterface */
    protected $logger;
    /** @var NotificationChannelInterface */
    protected $notification;

    public function __construct(QueueClientInterface $client, LoggerInterface $logger, NotificationChannelInterface $notification, MessageStorageInterface $storage)
    {
        $this->client = $client;
        $this->storage = $storage;
        $this->logger = $logger;
        $this->notification = $notification;
    }

    /**
     * @param array $config
     * @return BlockerHandlerInterface
     * @throws \Exception
     */
    public function create(array $config): BlockerHandlerInterface
    {
        $provider = $config['provider'] ?? 'undefined';

        switch ($provider) {
            case QueueClientFactory::PROVIDERS_SQS:
                return new SqsBlockerHandler($this->client, $config['defaultMaxDelay'], $this->logger, $this->notification, $this->storage);
        }

        throw new \Exception(sprintf('Failed to create blocker handler %s', $provider));
    }
}
