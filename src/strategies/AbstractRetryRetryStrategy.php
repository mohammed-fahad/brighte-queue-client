<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\container\Container;
use BrighteCapital\QueueClient\queue\QueueClientInterface;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use Interop\Queue\Message;

abstract class AbstractRetryRetryStrategy implements RetryStrategyInterface
{
    /** @var QueueClientInterface */
    protected $client;

    /** @var Retry */
    protected $retry;

    /** @var int */
    protected $delay;

    /** @var MessageStorageInterface */
    protected $storage;

    /**
     * AbstractRetryRetryStrategy constructor.
     * @param Retry $retry
     * @param QueueClientInterface $client
     * @param int $delay
     * @param MessageStorageInterface|null $storage
     */
    public function __construct(
        Retry $retry,
        QueueClientInterface $client,
        int $delay = 0,
        MessageStorageInterface $storage = null
    ) {
        $this->client = $client;
        $this->retry = $retry;
        $this->delay = $delay;
        $this->storage = $storage;
    }

    public function handle(Message $message): void
    {
        $attemptCount = $message->getProperty('ApproximateReceiveCount');
        if ($attemptCount >= $this->retry->getMaxRetryCount()) {
            $this->onMaxRetryReached($message);
        }
        if ($attemptCount < $this->retry->getMaxRetryCount()) {
            $this->client->delay($message, $this->retry->getDelay());
        }
    }

    abstract protected function onMaxRetryReached(Message $message): void;
}
