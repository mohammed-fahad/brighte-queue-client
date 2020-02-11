<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\queue\BlockerHandlerInterface;
use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\queue\sqs\SqsBlockerHandler;

class BlockerHandlerFactory
{
    public const PROVIDERS_SQS = 'sqs';
    public const PROVIDERS_KAFKA = 'kafka';
    public const PROVIDERS_RABBIT_MQ = 'rabbit_mq';

    /**
     * @param QueueClientInterface $client
     * @param array $config
     * @return BlockerHandlerInterface
     * @throws \Exception
     */
    public static function create(QueueClientInterface $client, array $config): BlockerHandlerInterface
    {
        $provider = $config['provider'] ?? 'undefined';

        switch ($provider) {
            case self::PROVIDERS_SQS:
                return new SqsBlockerHandler($client);
        }

        throw new \Exception(sprintf('Failed to create Queue Client %s', $provider));
    }
}
