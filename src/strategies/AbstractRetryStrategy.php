<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\queue\QueueClient;
use Enqueue\Sqs\SqsMessage;
use Interop\Queue\Message;

abstract class AbstractRetryStrategy implements RetryStrategyInterface
{
    /** @var QueueClient */
    protected $client;

    /** @var Retry */
    protected $retry;

    public function _construct(array $data)
    {
        $this->client = $data['queueClient'];
        $this->retry = $data['retry'];
    }

    public function handle(Message $message): void
    {
        $attemptCount = (int) $message->getAttribute('ApproximateReceiveCount');

        if ($attemptCount >= $this->retry->getRetryCount()) {
            $this->onMaxRetryReached($message);
        }

        if ($attemptCount < $this->retry->getRetryCount()) {
            $this->client->delay($message, $this->retry->getDelay());
        }
    }

    abstract protected function onMaxRetryReached(Message $message): void;
}
