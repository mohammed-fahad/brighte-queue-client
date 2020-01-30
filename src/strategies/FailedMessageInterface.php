<?php


namespace App\strategies;


use Interop\Queue\Message;

interface FailedMessageInterface
{
    public function getDelay(): int;

    public function getRetryCount(): int;

    public function getMessage(): Message;
}
