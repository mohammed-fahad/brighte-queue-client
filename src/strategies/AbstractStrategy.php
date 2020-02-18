<?php

namespace BrighteCapital\QueueClient\strategies;

use BrighteCapital\QueueClient\container\Container;
use BrighteCapital\QueueClient\queue\QueueClientInterface;
use Interop\Queue\Message;

abstract class AbstractStrategy implements StrategyInterface
{
    /** @var QueueClientInterface */
    protected $client;

    /** @var Retry */
    protected $retry;

    /**
     * AbstractStrategy constructor.
     * @param Retry $retry
     * @throws \Exception
     */
    public function __construct(Retry $retry)
    {
        $this->client = Container::instance()->get('QueueClient');
        $this->retry = $retry;
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
