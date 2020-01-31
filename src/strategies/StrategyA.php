<?php


namespace BrighteCapital\QueueClient\strategies;


class StrategyA extends AbstractRetryStrategy
{
    public function handle(): bool
    {
        echo "Saving to DB \n";

        echo "body  = " . $this->failedMessage->getMessage()->getBody();
        return true;

    }
}
