<?php

namespace BrighteCapital\QueueClient\queue\sqs;

use BrighteCapital\QueueClient\queue\BlockerHandlerInterface;
use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\queue\SqsBlockerHandler;
use Enqueue\Sqs\SqsMessage;
use Interop\Queue\Consumer;
use Interop\Queue\Destination;
use Interop\Queue\Message;
use Interop\Queue\Producer;

class SqsContext extends \Enqueue\Sqs\SqsContext
{

    public function createMessage(string $body = '', array $properties = [], array $headers = []): Message
    {
        return new SqsMessage($body, $properties, $headers);
    }

    /**
     * @return \Interop\Queue\Producer
     */
    public function createProducer(): Producer
    {
        return new SqsProducer($this);
    }

    /**
     * @param Destination $destination
     * @return Consumer
     */
    public function createConsumer(Destination $destination): Consumer
    {
        return new SqsConsumer($this, $destination);
    }

    /**
     * @param QueueClientInterface $client
     * @param array $config
     * @return BlockerHandlerInterface
     * @throws \Exception
     */
    public function createBlockerChecker(QueueClientInterface $client, array $config): BlockerHandlerInterface {
        return new SqsBlockerHandler($client, $config);
    }
}
