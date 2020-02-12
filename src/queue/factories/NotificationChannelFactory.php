<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\notifications\Channels\SlackNotificationChannel;

class NotificationChannelFactory
{
    public static function create($config)
    {
        /** ---notification format----
         * 'notification' => [
         * 'channel' => 'slack',
         * 'driverClass' => SlackNotificationChannel::class,
         * 'params' => [
         * 'url' => url,
         * 'maxBodyCharactersToSend' => 200,
         * ....
         * ]
         * ];
         * */
        if (!isset($config['notification'])) {
            throw new \Exception("Notification Channel not provided");
        }

        if (!isset($config['notification']['channel'])) {
            throw new \Exception("Channel name not provided");
        }

        $channel = $config['notification']['channel'];

        switch (strtolower($channel)) {
            case 'slack':
                return (new NotificationChannelFactory())->getSlackChannel($config['notification']);
        }

        throw new \Exception("Failed to create Notification channel. Channel name not implemented " . $channel);
    }

    public function getSlackChannel($slackConfig)
    {

        if (!isset($slackConfig['driverClass'])) {
            throw new \Exception("DriverClass must be provided");
        }

        $driverClass = $slackConfig['driverClass'];

        if (is_object($driverClass) && $this->isValidInstance($driverClass)) {
            return $driverClass;
        }

        if ($driverClass == SlackNotificationChannel::class) {
            if (!isset($slackConfig['params']) && !isset($slackConfig['params']['url'])) {
                throw new \Exception("Slack WebHook URL must be provided");
            }

            return new SlackNotificationChannel($slackConfig['params']['url']);
        }

        /**its not slack channel configuration but its the users own implementation of channel*/

        try {
            $reflectionClass = new \ReflectionClass($driverClass);
        } catch (\ReflectionException $e) {
            throw new \Exception("Failed to create channel $driverClass: " . $e->getMessage());
        }

        $class = $reflectionClass->newInstance(...array_values($slackConfig['params']));

        return self::isValidInstance($class);
    }

    public function isValidInstance($driverClass): NotificationChannelInterface
    {
        if ($driverClass instanceof NotificationChannelInterface) {
            return $driverClass;
        }
        throw new \Exception("Invalid driver class must be instance of " . NotificationChannelInterface::class);
    }
}
