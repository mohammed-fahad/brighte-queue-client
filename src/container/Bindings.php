<?php


namespace BrighteCapital\QueueClient\container;


use BrighteCapital\QueueClient\notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\queue\factories\NotificationChannelFactory;
use BrighteCapital\QueueClient\queue\factories\QueueClientFactory;
use BrighteCapital\QueueClient\queue\QueueClientInterface;

class Bindings
{
    public static function register(array $config)
    {
        Container::instance()->reset();
        /* SqsClient*/
        /*DB connection*/
        /*NotificationChannel*/

        Container::instance()->bind('QueueClient', function () use ($config): QueueClientInterface {
            return QueueClientFactory::create($config);
        });

        /*        Container::instance()->bind('StorageConnection', function () use ($config) {
                    return QueueClientFactory::create($config);
                });*/

        Container::instance()->bind('NotificationChannel', function () use ($config) : NotificationChannelInterface {
            return NotificationChannelFactory::create($config);
        });
    }
}
