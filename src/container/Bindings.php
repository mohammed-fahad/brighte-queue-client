<?php


namespace BrighteCapital\QueueClient\container;


use BrighteCapital\QueueClient\queue\factories\NotificationChannelFactory;
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

        /*        Container::instance()->bind('StorageConnection', function () use ($config) {
                    return QueueClientFactory::create($config);
                });*/

        Container::instance()->bind('NotificationChannel', function () use ($config) {
            // Need to check if the passed config is instance of NotificationChannelInterface otherwise
            // else construct one from the config. Should either be slack or mail.

            return NotificationChannelFactory::create($config);
        });
    }
}
