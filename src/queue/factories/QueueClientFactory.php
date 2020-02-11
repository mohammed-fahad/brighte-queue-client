<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\queue\sqs\SqsClient;
use BrighteCapital\QueueClient\queue\sqs\SqsConnectionFactory;

class QueueClientFactory
{
    public const PROVIDERS_SQS = 'sqs';
    public const PROVIDERS_KAFKA = 'kafka';
    public const PROVIDERS_RABBIT_MQ = 'rabbit_mq';

    /**
     * @param array $config config
     * @return SqsClient
     * @throws \Exception
     */
    public static function create(array $config): QueueClientInterface
    {
        $provider = $config['provider'] ?? 'undefined';

        if (!isset($config['queue'])) {
            throw new \Exception('Please provide queue name');
        }

        switch ($provider) {
            case self::PROVIDERS_SQS:
                $sqsConnectFactory = new SqsConnectionFactory($config);

                return new SqsClient($config['queue'] ?? '', $sqsConnectFactory->createContext());
        }

        throw new \Exception(sprintf('Failed to create Queue Client %s', $provider));
    }
}
