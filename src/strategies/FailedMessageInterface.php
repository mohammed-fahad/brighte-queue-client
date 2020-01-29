<?php


namespace App\strategies;


interface FailedMessageInterface
{
    public function getDelay(): int;

    public function getMaxRetries(): int;

    public function getHandler(): RetryStrategyInterface;
}
