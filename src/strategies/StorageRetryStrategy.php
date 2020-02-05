<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\queue\QueueClientInterface;
use Interop\Queue\Message;

class StorageRetryStrategy extends AbstractRetryStrategy
{
    public function __construct(Retry $retry, QueueClientInterface $queueClient)
    {
        parent::__construct($retry, $queueClient);
    }

    public function handle(Message $message): void
    {
        $this->onMaxRetryReached($message);
    }

    protected function onMaxRetryReached(Message $message): void
    {
        //do the heavey lifting here
    }
}
