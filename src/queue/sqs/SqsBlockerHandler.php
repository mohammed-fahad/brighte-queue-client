<?php
namespace BrighteCapital\QueueClient\queue;

use BrighteCapital\QueueClient\container\Container;
use BrighteCapital\QueueClient\Storage\MessageEntity;
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
     * @throws \Exception
     */
    public function __construct(QueueClientInterface $client)
    {
        $this->client = $client;
        try {
            $this->storage = Container::instance()->get('Storage');
        } catch (\Exception $e) {
            // Do nothing
        }
    }

    /**
     * @param Message $message
     * @return bool
     */
    public function checkAndHandle(Message $message) : bool
    {
        if (empty($this->storage)) {
            return false;
        }

        $entity = new MessageEntity($message);

        /** @var MessageEntity $oldEntity */
        $oldEntity = $this->storage->messageExist($entity);

        if ($oldEntity === false) {
            return false;
        }

        $entity->setId($oldEntity->getId());
        $entity->setAlertCount($oldEntity->getAlertCount() + 1);
        $this->storage->update($entity);

        $this->client->delay($message, StorageRetryStrategy::DEFAULT_DELAY_FOR_STORED_MESSAGE);

        return true;
    }
}