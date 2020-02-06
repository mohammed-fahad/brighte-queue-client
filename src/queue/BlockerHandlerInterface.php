<?php
namespace BrighteCapital\QueueClient\queue;

use Interop\Queue\Message;

interface BlockerHandlerInterface
{
    public function checkAndHandle(Message $message) : bool;
}