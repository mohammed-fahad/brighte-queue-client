<?php


namespace App\strategies;


interface RetryStrategyInterface
{
    public function handle(): bool;
}
