<?php

namespace BrighteCapital\QueueClient\strategies;

use Interop\Queue\Message;

class DefaultRetryStrategyDriver implements RetryStrategyInterface
{
    const DEFAULT_RETRY_COUNT = 5;
    const DEFAULT_DELAY_IN_SECONDS = 300;
    /**
     * @var \Interop\Queue\Message
     */
    private $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function handle(): bool
    {
        $failedMessage = new FailedMessage($this->message, self::DEFAULT_DELAY_IN_SECONDS, self::DEFAULT_RETRY_COUNT);
        $strategy = new StrategyB($failedMessage);

        return $strategy->handle();
    }
}
