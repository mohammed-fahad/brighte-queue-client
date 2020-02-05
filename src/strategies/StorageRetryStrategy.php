<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\queue\factories\StorageFactory;
use BrighteCapital\QueueClient\Storage\StorageInterface;
use Exception;
use Interop\Queue\Message;

class StorageRetryStrategy extends AbstractRetryStrategy
{
    /** @var StorageInterface */
    protected $storage;

    /** @var array */
    protected $config;

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @throws Exception
     */
    public function setup()
    {
        $this->storage = StorageFactory::create($this->config);
    }

    function onMaxRetryReached(Message $message): void
    {
        $this->storage->storeMessage($message);
    }
}
