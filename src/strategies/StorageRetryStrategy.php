<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\queue\factories\StorageFactory;
use BrighteCapital\QueueClient\Storage\StorageInterface;
use Exception;
use Interop\Queue\Message;

class StorageRetryStrategy extends AbstractRetryStrategy
{
    const DEFAULT_DELAY_FOR_STORED_MESSAGE = '43200';
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
//        $this->client->delay($message, self::DEFAULT_DELAY_FOR_STORED_MESSAGE);
        $this->storage->store($message);
    }
}
