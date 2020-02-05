<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

class DefaultRetryStrategy extends AbstractRetryStrategy
{
    public function handle(Message $message): void
    {
        echo "Saving to DB \n";
        $this->onMaxRetryReached($message);
    }

    protected function onMaxRetryReached(Message $message): void
    {
        // TODO: Implement onMaxRetryReached() method.
    }
}
