<?php


namespace App\strategies;


class StrategyA implements RetryStrategyInterface
{
    public function handle(): bool
    {
        echo "Saving to DB";

        return true;
    }
}
