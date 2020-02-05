<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

class DefaultRetryStrategy extends AbstractRetryStrategy
{
    protected function onMaxRetryReached(Message $message): void
    {
        // TODO: Delete the message & Notify
    }
}
