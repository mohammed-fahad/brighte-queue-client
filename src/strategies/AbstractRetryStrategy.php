<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\queue\sqs\SqsClient;
use Interop\Queue\Message;

abstract class AbstractRetryStrategy implements RetryStrategyInterface
{
    /** @var SqsClient */
    protected $client;

    /** @var Retry */
    protected $retry;

    public function _construct(SqsClient $queueClient, Retry $retry)
    {
        $this->client = $queueClient;
        $this->retry = $retry;
    }

    public function handle(Message $message): void
    {
        $attemptCount = (int) $message->getAttribute('ApproximateReceiveCount');

        if ($attemptCount >= $this->retry->getMaxRetryCount()) {
            $this->onMaxRetryReached($message);
        }

        if ($attemptCount < $this->retry->getMaxRetryCount()) {
            $this->client->delay($message, $this->retry->getDelay());
        }
    }

    abstract protected function onMaxRetryReached(Message $message): void;
}
