<?php

namespace BrighteCapital\QueueClient\notifications\Channels;

interface NotificationChannelInterface
{
    /**
     * @param array $data
     * @return bool
     */
    public function send(array $data);
}
