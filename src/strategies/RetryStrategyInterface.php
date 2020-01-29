<?php


namespace App\strategies;


interface RetryStrategyInterface
{
    public function handle(FailedMessageInterface $message): bool;

}
