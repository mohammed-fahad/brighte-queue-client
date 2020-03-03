<?php

namespace BrighteCapital\QueueClient\Queue\Factories;

use BrighteCapital\QueueClient\Queue\QueueClientInterface;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Queue\Sqs\SqsConnectionFactory;

class QueueClientFactory
{
    public const PROVIDERS_SQS = 'sqs';
    public const PROVIDERS_KAFKA = 'kafka';
    public const PROVIDERS_RABBIT_MQ = 'rabbit_mq';

    /**
     * @param array $config config
     * @return QueueClientInterface
     * @throws \Exception
     */
    public function create(array $config): QueueClientInterface
    {
        $provider = $config['provider'] ?? 'undefined';

        if (!isset($config['queue'])) {
            throw new \Exception('Please provide Queue name');
        }

        switch ($provider) {
            case self::PROVIDERS_SQS:
                $sqsConnectFactory = new SqsConnectionFactory($config);

                return new SqsClient($config['queue'] ?? '', $sqsConnectFactory->createContext());
        }

        throw new \Exception(sprintf('Failed to create Queue Client %s', $provider));
    }
}
