<?php

namespace BrighteCapital\QueueClient\strategies;

class StrategyA extends AbstractRetryStrategy
{
    public function handle(): bool
    {
        echo "Saving to DB \n";

        return true;
    }
}
