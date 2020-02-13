<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\notifications\Channels\SlackNotificationChannel;
use GuzzleHttp\Client;

class NotificationChannelFactory
{
    public const ERROR_MISSING_CONFIG_KEY = '%s must be provided';

    public static function create($config)
    {
        /** ---notification format----
         * [
         * 'provider' => SlackNotificationChannel::class,
         * 'params' => [
         * 'url' => $tempEndpoint,
         * 'maxBodyCharactersToSend' => 200
         * ]
         * */
        if (!isset($config['provider'])) {
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, 'provider key'));
        }

        $factory = new NotificationChannelFactory();
        $provider = $config['provider'];

        if (is_object($provider) && $factory->isValidInstance($provider)) {
            return $provider;
        }

        switch (strtolower($provider)) {
            case 'slack':
                return $factory->getSlackChannel($config);
            case strtolower(SlackNotificationChannel::class):
                return $factory->getSlackChannel($config);
        }

        throw new \Exception(
            sprintf(
                "Failed to create Notification channel. %s does not match expected names",
                $provider
            )
        );
    }

    public function getSlackChannel($slackConfig): NotificationChannelInterface
    {

        if (!isset($slackConfig['params'])) {
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, "notification.params"));
        }
        if (!isset($slackConfig['params']['url'])) {
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, "notification.params.url"));
        }

        $defaultBodyChars = SlackNotificationChannel::DEFAULT_MAX_BODY_CHARS_TO_SEND;
        $maxChars = $slackConfig['params']['maxBodyCharactersToSend'] ?? $defaultBodyChars;

        return new SlackNotificationChannel($slackConfig['params']['url'], $maxChars, new Client());
    }

    public function isValidInstance($driverClass): NotificationChannelInterface
    {
        if ($driverClass instanceof NotificationChannelInterface) {
            return $driverClass;
        }
        throw new \Exception("Invalid driver class must be instance of " . NotificationChannelInterface::class);
    }
}
