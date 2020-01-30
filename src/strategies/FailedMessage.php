<?php

namespace App\strategies;

use Interop\Queue\Message;

class FailedMessage implements FailedMessageInterface
{
    protected $delay;

    protected $retryCount;
    /**
     * @var \Interop\Queue\Message
     */
    private $message;

    public function __construct(Message $message, int $delays, int $retryCount)
    {
        $this->delay = $delays;
        $this->retryCount = $retryCount;
        $this->message = $message;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    /**
     * @return \Interop\Queue\Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }
}
