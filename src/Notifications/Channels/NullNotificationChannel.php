<?php

namespace BrighteCapital\QueueClient\Notifications\Channels;

/**
 * This Notification can be used to avoid conditional Notification calls.
 *
 * Notification can be optional, and if no notification channel is provided to your library creating
 * a NullNotificationChannel instance to have something to notify at is a good way
 * to avoid littering your code with `if ($this->notificationChannel) { }`
 * blocks.
 */
class NullNotificationChannel implements NotificationChannelInterface
{
    /**
     * @param array $data
     * @return void
     */
    public function send(array $data): void
    {
        // Do nothing
    }
}