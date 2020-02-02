<?php

namespace BrighteCapital\QueueClient\queue\sqs;

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

    public function createConsumer(Destination $destination): Consumer
    {
        return new SqsConsumer($this, $destination);
    }
}
