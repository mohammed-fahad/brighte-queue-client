<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\storage\MessageStorageInterface;
use Interop\Queue\Message;

class StorageRetryStrategy extends AbstractRetryStrategy
{
    /**
     * @var \BrighteCapital\QueueClient\strategies\StorageInterface
     */
    protected $storage;

    public function __construct(Retry $retry, QueueClientInterface $queueClient, MessageStorageInterface $storage)
    {
        parent::__construct($retry, $queueClient);
        $this->storage = $storage;
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
