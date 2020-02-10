<?php

namespace BrighteCapital\QueueClient\container;

use BrighteCapital\QueueClient\queue\factories\BlockerHandlerFactory;
use BrighteCapital\QueueClient\queue\factories\QueueClientFactory;
use BrighteCapital\QueueClient\queue\factories\StorageFactory;
use BrighteCapital\QueueClient\queue\QueueClientInterface;

class Bindings
{
    public static function register(array $config)
    {
        Container::instance()->reset();

        Container::instance()->bind('Config', function () use ($config) {
            return $config;
        });

        Container::instance()->bind('Storage', function () use ($config) {
            return StorageFactory::create($config['database']);
        });

        Container::instance()->bind('QueueClient', function () use ($config) {
            return QueueClientFactory::create($config);
        });

        Container::instance()->bind('BlockerHandler', function ()  use ($config) {
            /** @var QueueClientInterface $client */
            $client = Container::instance()->get('QueueClient');

            return BlockerHandlerFactory::create($client, $config);
        });
    }
}

