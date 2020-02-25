<?php

namespace BrighteCapital\QueueClient\Queue\Factories;

use BrighteCapital\QueueClient\Queue\BlockerHandlerInterface;
use BrighteCapital\QueueClient\Queue\QueueClientInterface;
use BrighteCapital\QueueClient\Queue\Sqs\SqsBlockerHandler;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;

class BlockerHandlerFactory
{
    /** @var QueueClientInterface */
    protected $client;
    /** @var MessageStorageInterface */
    protected $storage;

    public function __construct(QueueClientInterface $client, MessageStorageInterface $storage)
    {
        $this->client = $client;
        $this->storage = $storage;
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
                return new SqsBlockerHandler($this->client, $config['defaultMaxDelay'], $this->storage);
        }

        throw new \Exception(sprintf('Failed to create blocker handler %s', $provider));
    }
}
