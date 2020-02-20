<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\container\Container;
use BrighteCapital\QueueClient\queue\BlockerHandlerInterface;
use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\queue\sqs\SqsBlockerHandler;

class BlockerHandlerFactory
{
    /**
     * @param QueueClientInterface $client
     * @param array $config
     * @return BlockerHandlerInterface
     * @throws \Exception
     */
    public static function create(QueueClientInterface $client, array $config): BlockerHandlerInterface
    {
        $provider = $config['provider'] ?? 'undefined';
        $storage = Container::instance()->get('Storage');

        switch ($provider) {
            case QueueClientFactory::PROVIDERS_SQS:
                return new SqsBlockerHandler($client, $config['retryStrategy']['storedMessageRetryDelay'], $storage);
        }

        throw new \Exception(sprintf('Failed to create blocker handler %s', $provider));
    }
}
