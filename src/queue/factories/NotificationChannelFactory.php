<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\notifications\Channels\SlackNotificationChannel;

class NotificationChannelFactory
{
    const ERROR_MISSING_CONFIG_KEY = '%s must be provided';

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
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, 'notification key'));
        }

        if (!isset($config['notification']['channel'])) {
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, 'notification.Channel'));
        }

        $channel = $config['notification']['channel'];

        switch (strtolower($channel)) {
            case 'slack':
                return (new NotificationChannelFactory())->getSlackChannel($config['notification']);
        }

        throw new \Exception(
            sprintf(
                "Failed to create Notification channel. notification.channel %s does not match expected names",
                $channel
            )
        );
    }

    private function getSlackChannel($slackConfig): NotificationChannelInterface
    {
        if (!isset($slackConfig['driverClass'])) {
            throw new \Exception("notification.DriverClass must be provided");
        }

        $driverClass = $slackConfig['driverClass'];

        if (is_object($driverClass) && $this->isValidInstance($driverClass)) {
            return $driverClass;
        }

        if ($driverClass !== SlackNotificationChannel::class) {
            throw new \Exception(
                sprintf(
                    "DriverClass must either be instance of %s (object) or %s (string)",
                    NotificationChannelInterface::class,
                    SlackNotificationChannel::class
                )
            );
        }

        // slack
        if (!isset($slackConfig['params'])) {
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, "notification.params"));
        }
        if (!isset($slackConfig['params']['url'])) {
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, "notification.params.url"));
        }

        $defaultBodyChars = SlackNotificationChannel::DEFAULT_MAX_BODY_CHARS_TO_SEND;
        $maxChars = $slackConfig['params']['maxBodyCharactersToSend'] ?? $defaultBodyChars;

        return new SlackNotificationChannel($slackConfig['params']['url'], $maxChars);
    }

    private function isValidInstance($driverClass): NotificationChannelInterface
    {
        if ($driverClass instanceof NotificationChannelInterface) {
            return $driverClass;
        }
        throw new \Exception("Invalid driver class must be instance of " . NotificationChannelInterface::class);
    }
}
