<?php

namespace BrighteCapital\QueueClient\queue\sqs;

use BrighteCapital\QueueClient\queue\factories\SqsConnectionFactory;
use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\strategies\AbstractRetryStrategy;
use BrighteCapital\QueueClient\strategies\DefaultRetryStrategyDriver;
use Interop\Queue\Message;

class SqsClient implements QueueClientInterface
{
    /**
     * @var \BrighteCapital\QueueClient\queue\sqs\SqsContext
     */
    protected $context;
    /**
     * @var \Enqueue\Sqs\SqsDestination
     */
    protected $sqsDestination;
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
     * @param \BrighteCapital\QueueClient\queue\sqs\SqsConfig $config config
     */
    public function __construct(SqsConfig $config)
    {
        $factory = new SqsConnectionFactory($config->toArray());
        $this->context = $factory->createContext();
        $this->sqsDestination = $this->context->createQueue($config->getQueue());

        $this->producer = $this->context->createProducer();
        $this->consumer = $this->context->createConsumer($this->sqsDestination);
    }


    /**
     * @param int $timeout timeout
     * @return \Interop\Queue\Message
     */
    public function receive($timeout = 0): Message
    {
        // TODO: Implement receive() method.
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
        $this->producer->send($this->sqsDestination, $message);
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
     * @param \BrighteCapital\QueueClient\strategies\AbstractRetryStrategy|null $retryStrategy
     * @throws \Exception
     */
    public function reject(Message $message, AbstractRetryStrategy $retryStrategy = null): void
    {
        if (is_null($retryStrategy)) {
            $retryStrategy = new DefaultRetryStrategyDriver($message);
        }
        $result = $retryStrategy->handle($message);

        if ($result === false) {
            throw new \Exception("Failed to reject message " . $message);
        }
        $this->consumer->reject($message);
    }

    /**
     * @return \Enqueue\Sqs\SqsConsumer|\Interop\Queue\Consumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }

    /**
     * @return \Enqueue\Sqs\SqsProducer|\Interop\Queue\Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * @return \Enqueue\Sqs\SqsDestination|\Interop\Queue\Queue
     */
    public function getSqsDestination()
    {
        return $this->sqsDestination;
    }

    /**
     * @return \Enqueue\Sqs\SqsContext|\Interop\Queue\Context
     */
    public function getContext()
    {
        return $this->context;
    }
}
