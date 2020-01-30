<?php


namespace App\strategies;


use Interop\Queue\Message;

interface FailedMessageInterface
{
    public function getDelay(): int;

    public function getMaxRetries(): int;

    public function getMessage(): Message;
}
