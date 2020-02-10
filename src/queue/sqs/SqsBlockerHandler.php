<?php
namespace BrighteCapital\QueueClient\queue;

use BrighteCapital\QueueClient\queue\factories\StorageFactory;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use BrighteCapital\QueueClient\strategies\StorageRetryStrategy;
use Enqueue\Sqs\SqsMessage;
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
        $entity = new MessageEntity($message);
        /** @var MessageEntity $oldEntity */
        $oldEntity = $this->storage->messageExist($entity);

        if ($oldEntity === false) {
            return false;
        }

        $oldEntity->setAlertCount($oldEntity->getAlertCount() + 1);
        $this->storage->update($oldEntity);

        $this->client->delay($message, StorageRetryStrategy::DEFAULT_DELAY_FOR_STORED_MESSAGE);

        return true;
    }
}