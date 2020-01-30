<?php


namespace App\strategies;


class StrategyB extends AbstractRetryStrategy
{
    public function __construct(FailedMessageInterface $failedMessage)
    {
        parent::__construct($failedMessage);
    }

    public function handle(): bool
    {
        echo "Brighte Default Strategy";

        return true;
    }
}
