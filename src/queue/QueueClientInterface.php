<?php

namespace App\queue;

use App\strategies\RetryStrategyInterface;
use Interop\Queue\Message;

interface QueueClientInterface
{
    public function receive($timeout = 0): Message;

    public function createMessage(string $body, array $properties = [], array $headers = []): Message;

    public function send(Message $message): void;

    public function acknowledge(Message $message): void;

    public function reject(Message $message, RetryStrategyInterface $retry = null): void;
}
