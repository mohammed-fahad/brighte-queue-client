<?php

namespace App\queue\factories;

use App\queue\QueueClientInterface;
use App\queue\sqs\SqsClient;
use App\queue\sqs\SqsConfig;

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
                return new SqsClient(new SqsConfig($config));
        }

        throw new \Exception(sprintf("Failed to create Queue Client %s", $provider));
    }
}
