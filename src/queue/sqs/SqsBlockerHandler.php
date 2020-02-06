<?php
namespace BrighteCapital\QueueClient\queue;

use BrighteCapital\QueueClient\queue\factories\StorageFactory;
use BrighteCapital\QueueClient\strategies\StorageRetryStrategy;
use Interop\Queue\Message;

class SqsBlockerHandler implements BlockerHandlerInterface
{
    /** @var QueueClientInterface */
    private $client;
    /** @var \BrighteCapital\QueueClient\Storage\StorageInterface  */
    private $storage;

    /**
     * BlockerChecker constructor.
     * @param QueueClientInterface $client
     * @param array $config
     * @throws \Exception
     */
    public function __construct(QueueClientInterface $client, array $config)
    {
        $this->client = $client;
        $this->storage = StorageFactory::create($config);
    }

    /**
     * @param Message $message
     * @return bool
     */
    public function checkAndHandle(Message $message) : bool
    {
        $oldMessage = $this->storage->messageExist($message);
        if (!$oldMessage) {
            return false;
        }
        $this->storage->update($message);
        $this->client->delay($message, StorageRetryStrategy::DEFAULT_DELAY_FOR_STORED_MESSAGE);
        return true;
    }
}