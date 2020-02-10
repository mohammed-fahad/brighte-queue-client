<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\container\Container;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use BrighteCapital\QueueClient\Storage\StorageInterface;
use Exception;
use Interop\Queue\Message;

class StorageRetryStrategy extends AbstractRetryStrategy
{
    const DEFAULT_DELAY_FOR_STORED_MESSAGE = '43200';
    /** @var StorageInterface */
    protected $storage;

    /**
     * StorageRetryStrategy constructor.
     * @param Retry $retry
     * @throws Exception
     */
    public function __construct(Retry $retry)
    {
        parent::__construct($retry);
        $this->storage = Container::instance()->get('Storage');

    }

    function onMaxRetryReached(Message $message): void
    {
        $messageEntity = new MessageEntity($message);
        $messageEntity->setLastErrorMessage($this->retry->getErrorMessage());

        //TODO: $this->client->delay($message, self::DEFAULT_DELAY_FOR_STORED_MESSAGE);
        $this->storage->store($messageEntity);
    }
}
