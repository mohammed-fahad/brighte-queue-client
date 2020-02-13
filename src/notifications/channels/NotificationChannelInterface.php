<?php

namespace BrighteCapital\QueueClient\notifications\Channels;

interface NotificationChannelInterface
{
    /**
     * @param array $data
     */
    public function send(array $data): void;
}
