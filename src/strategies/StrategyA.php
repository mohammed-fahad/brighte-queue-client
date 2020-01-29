<?php


namespace App\strategies;


class StrategyA implements RetryStrategyInterface
{

    public function handle(FailedMessageInterface $message): bool
    {
        echo "Saving to DB";
    }
}
