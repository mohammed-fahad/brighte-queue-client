<?php

namespace BrighteCapital\QueueClient\notifications\Channels;

use Interop\Queue\Message;

interface NotificationChannelInterface
{
    /**
     * @param \Interop\Queue\Message $message message
     * @return bool
     * @throws \Exception
     */
    public function send(Message $message): bool;
}
