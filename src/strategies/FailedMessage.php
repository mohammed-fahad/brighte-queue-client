<?php

namespace App\strategies;

use Interop\Queue\Message;

class FailedMessage implements FailedMessageInterface
{
    protected $delay;

    protected $retries;
    /**
     * @var \Interop\Queue\Message
     */
    private $message;

    public function __construct(Message $message, int $delays, int $retries)
    {
        $this->delay = $delays;
        $this->retries = $retries;
        $this->message = $message;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function getMaxRetries(): int
    {
        return $this->retries;
    }

    /**
     * @return \Interop\Queue\Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }
}
