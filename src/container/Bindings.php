<?php

namespace BrighteCapital\QueueClient\container;

use BrighteCapital\QueueClient\queue\factories\BlockerHandlerFactory;
use BrighteCapital\QueueClient\notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\queue\factories\NotificationChannelFactory;
use BrighteCapital\QueueClient\queue\factories\QueueClientFactory;
use BrighteCapital\QueueClient\queue\factories\StorageFactory;
use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;

class Bindings
{
    public static function register(array $config)
    {
        Container::instance()->reset();

        Container::instance()->bind('Config', function () use ($config) {
            return $config;
        });

        Container::instance()->bind('Storage', function () use ($config) {
            $storage = null;
            /** @var MessageStorageInterface $storage */
            try {
                $storage = StorageFactory::create($config['storage']);
                if (!$storage->messageTableExist()) {
                    $storage->createMessageTable();
                }
            } catch (\Exception $e) {
                //TODO:: logger
            }

            return $storage;
        });

        Container::instance()->bind('QueueClient', function () use ($config): QueueClientInterface {
            return QueueClientFactory::create($config);
        });

        Container::instance()->bind('BlockerHandler', function () use ($config) {
            /** @var QueueClientInterface $client */
            $client = Container::instance()->get('QueueClient');

            return BlockerHandlerFactory::create($client, $config);
        });

        Container::instance()->bind('NotificationChannel', function () use ($config): NotificationChannelInterface {
            return NotificationChannelFactory::create($config);
        });
    }
}
