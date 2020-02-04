<?php

namespace BrighteCapital\QueueClient\strategies;

use App\strategies\Retry;
use Interop\Queue\Message;

class DefaultRetryStrategyDriver implements RetryStrategyInterface
{
    public function handle(Message $message): bool
    {
        $failedMessage = new Retry($this->message, self::DEFAULT_DELAY_IN_SECONDS, self::DEFAULT_RETRY_COUNT);
        $strategy = new StrategyB($failedMessage);

        return $strategy->handle();
    }
}
