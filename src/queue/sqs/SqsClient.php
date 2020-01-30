<?php


namespace App\queue\sqs;


use App\queue\QueueClientInterface;
use App\strategies\AbstractRetryStrategy;
use App\strategies\DefaultRetryStrategyDriver;
use Enqueue\Sqs\SqsConnectionFactory;
use Interop\Queue\Message;

class SqsClient implements QueueClientInterface
{

    protected $consumer;

    protected $producer;

    protected $sqsDestination;

    protected $context;

    public function __construct(SqsConfig $config)
    {
        $factory = new SqsConnectionFactory($config->toArray());
        $this->context = $factory->createContext();
        $this->sqsDestination = $this->context->createQueue($config->getQueue());

        $this->producer = $this->context->createProducer();
        $this->consumer = $this->context->createConsumer($this->sqsDestination);
    }


    public function send(Message $message): void
    {
        $this->producer->send($this->sqsDestination, $message);
    }

    public function receive($timeout = 0): Message
    {
        return $this->consumer->receive($timeout);
    }

    public function acknowledge(Message $message): void
    {
        $this->consumer->acknowledge($message);
    }

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

    public function createMessage(string $body, array $properties = [], array $headers = []): Message
    {
        return $this->context->createMessage($body, $properties, $headers);
    }
}
