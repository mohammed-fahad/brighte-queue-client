<?php

namespace BrighteCapital\QueueClient\queue;

use BrighteCapital\QueueClient\queue\factories\QueueClientFactory;
use BrighteCapital\QueueClient\queue\factories\StrategyFactory;
use BrighteCapital\QueueClient\strategies\RetryAbleInterface;
use Interop\Queue\Message;

class BrighteQueueClient
{
    /**
     * @var \BrighteCapital\QueueClient\queue\QueueClientInterface
     */
    protected $client;

    /** @var array */
    protected $config;

    /**
     * BrighteQueueClient constructor.
     * @param array $config
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        $this->client = QueueClientFactory::create($config);
        $this->config = $config;
    }

    /**
     * @param int $timeout timeout
     * @return \Interop\Queue\Message
     */
    public function receive($timeout = 0): Message
    {
        return $this->client->receive($timeout);
    }

    /**
     * @param string $body body
     * @param array $properties properties
     * @param array $headers headers
     * @return \Interop\Queue\Message
     */
    public function createMessage(string $body, array $properties = [], array $headers = []): Message
    {
        return $this->client->createMessage($body, $properties, $headers);
    }

    /**
     * @param \Interop\Queue\Message $message message
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\Exception\InvalidDestinationException
     * @throws \Interop\Queue\Exception\InvalidMessageException
     */
    public function send(Message $message): void
    {
        $this->client->send($message);
    }

    /**
     * @param \Interop\Queue\Message $message message
     */
    public function acknowledge(Message $message): void
    {
        $this->client->acknowledge($message);
    }

    /**
     * @param \Interop\Queue\Message $message message
     * @param \BrighteCapital\QueueClient\strategies\RetryAbleInterface $retryAble
     * @throws \ReflectionException
     */
    public function reject(Message $message, RetryAbleInterface $retryAble = null): void
    {
        $strategy = StrategyFactory::create($retryAble, $this->client, $this->config);
        
        $strategy->handle($message);
    }
}
