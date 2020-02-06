<?php


namespace BrighteCapital\QueueClient\container;


use BrighteCapital\QueueClient\queue\factories\QueueClientFactory;

class Bindings
{
    public static function register(array $config)
    {
        Container::instance()->reset();

        /* SqsClient*/
        /*DB connection*/
        /*NotificationChannel*/

        Container::instance()->bind('QueueClient', function () use ($config) {
            return QueueClientFactory::create($config);
        });

        Container::instance()->bind('StorageConnection', function () use ($config) {
            return QueueClientFactory::create($config);
        });

        Container::instance()->bind('notificationChannel', function () use ($config) {
            return QueueClientFactory::create($config);
        });
    }
}
