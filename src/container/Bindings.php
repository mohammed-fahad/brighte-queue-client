<?php

namespace BrighteCapital\QueueClient\container;

use BrighteCapital\QueueClient\queue\factories\BlockerHandlerFactory;
use BrighteCapital\QueueClient\queue\factories\QueueClientFactory;
use BrighteCapital\QueueClient\queue\factories\StorageFactory;
use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\StorageInterface;

class Bindings
{
    public static function register(array $config)
    {
        Container::instance()->reset();

        Container::instance()->bind('Config', function () use ($config) {
            return $config;
        });

        Container::instance()->bind('Storage', function () use ($config) {
            /** @var StorageInterface $storage */
            $storage = StorageFactory::create($config['database']);

            if (!$storage->messageTableExist()) {
                $storage->createMessageTable();
            }

            return $storage;
        });

        Container::instance()->bind('QueueClient', function () use ($config) {
            return QueueClientFactory::create($config);
        });

        Container::instance()->bind('BlockerHandler', function () use ($config) {
            /** @var QueueClientInterface $client */
            $client = Container::instance()->get('QueueClient');

            return BlockerHandlerFactory::create($client, $config);
        });
    }
}
