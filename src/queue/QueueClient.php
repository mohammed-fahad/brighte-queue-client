<?php

namespace BrighteCapital\QueueClient\queue;

use Interop\Queue\Context;
use Interop\Queue\Message;

class QueueClient implements QueueClientInterface
{
    /**
     * @var \BrighteCapital\QueueClient\queue\sqs\SqsContext
     */
    protected $context;
    /**
     * @var \Enqueue\Sqs\SqsDestination
     */
    protected $destination;
    /**
     * @var \BrighteCapital\QueueClient\queue\sqs\SqsProducer
     */
    protected $producer;
    /**
     * @var \BrighteCapital\QueueClient\queue\sqs\SqsConsumer
     */
    protected $consumer;

    /**
     * SqsClient constructor.
     * @param string $queueName queueName
     * @param \Interop\Queue\Context $context context
     */
    public function __construct(string $queueName, Context $context)
    {
        $this->context = $context;
        $this->destination = $this->context->createQueue($queueName);
    }

    /**
     * @param int $timeout timeout
     * @return \Interop\Queue\Message
     */
    public function receive($timeout = 0): Message
    {
        return $this->getConsumer()->receive($timeout);
    }

    /**
     * @param string $body body
     * @param array $properties properties
     * @param array $headers headers
     * @return \Interop\Queue\Message
     */
    public function createMessage(string $body, array $properties = [], array $headers = []): Message
    {
        return $this->context->createMessage($body, $properties, $headers);
    }

    /**
     * @param \Interop\Queue\Message $message message
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\Exception\InvalidDestinationException
     * @throws \Interop\Queue\Exception\InvalidMessageException
     */
    public function send(Message $message): void
    {
        $this->getProducer()->send($this->destination, $message);
    }

    /**
     * @param \Interop\Queue\Message $message message
     */
    public function acknowledge(Message $message): void
    {
        $this->consumer->acknowledge($message);
    }

    /**
     * @param \Interop\Queue\Message $message message
     */
    public function reject(Message $message): void
    {
        $this->consumer->reject($message);
    }

    /**
     * @return \Enqueue\Sqs\SqsConsumer|\Interop\Queue\Consumer
     */
    public function getConsumer()
    {
        if ($this->consumer === null) {
            $this->consumer = $this->context->createConsumer($this->getDestination());
        }
        return $this->consumer;
    }

    /**
     * @return \Enqueue\Sqs\SqsProducer|\Interop\Queue\Producer
     */
    public function getProducer()
    {
        if ($this->producer === null) {
            $this->producer = $this->context->createProducer();
        }
        return $this->producer;
    }

    /**
     * @return \Enqueue\Sqs\SqsDestination|\Interop\Queue\Queue
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @return \Enqueue\Sqs\SqsContext|\Interop\Queue\Context
     */
    public function getContext()
    {
        return $this->context;
    }
}
