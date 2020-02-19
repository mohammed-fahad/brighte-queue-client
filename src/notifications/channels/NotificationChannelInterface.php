<?php

namespace BrighteCapital\QueueClient\notifications\Channels;

interface NotificationChannelInterface
{
    /**
     * @param array $data
     * @return void
     */
    public function send(array $data): void;
}
