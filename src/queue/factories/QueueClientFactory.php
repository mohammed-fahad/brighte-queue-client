<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\queue\sqs\SqsClient;

class QueueClientFactory
{
    const PROVIDERS_SQS = 'sqs';
    const PROVIDERS_KAFKA = 'kafka';
    const PROVIDERS_RABBIT_MQ = 'rabbit_mq';

    public static function create(array $config): QueueClientInterface
    {
        $provider = $config['provider'] ?? "undefined";

        switch ($provider) {
            case self::PROVIDERS_SQS:
                return new SqsClient($config);
        }

        throw new \Exception(sprintf("Failed to create Queue Client %s", $provider));
    }
}
