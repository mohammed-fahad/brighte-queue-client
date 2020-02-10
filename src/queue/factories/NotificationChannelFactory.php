<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\notifications\Channels\SlackNotificationChannel;

class NotificationChannelFactory
{
    public static function create($config)
    {
        $url = 'https://hooks.slack.com/services/sdklfdjlskdfj';
        return new SlackNotificationChannel($url);
    }
}
