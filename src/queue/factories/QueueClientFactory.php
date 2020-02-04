<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\queue\QueueClient;
use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\queue\sqs\SqsConnectionFactory;

class QueueClientFactory
{
    const PROVIDERS_SQS = 'sqs';
    const PROVIDERS_KAFKA = 'kafka';
    const PROVIDERS_RABBIT_MQ = 'rabbit_mq';

    /**
     * @param array $config config
     * @return \BrighteCapital\QueueClient\queue\QueueClientInterface
     * @throws \Exception
     */
    public static function create(array $config): QueueClientInterface
    {
        $provider = $config['provider'] ?? "undefined";

        switch ($provider) {
            case self::PROVIDERS_SQS:
                $sqsConnectFactory = new SqsConnectionFactory($config);
                return new QueueClient($config['queue'] ?? '', $sqsConnectFactory->createContext());
        }

        throw new \Exception(sprintf("Failed to create Queue Client %s", $provider));
    }
}
