<?php

namespace BrighteCapital\QueueClient\Notifications\Channels;

interface NotificationChannelInterface
{
    /**
     * @param array $data
     * @return void
     */
    public function send(array $data): void;
}
