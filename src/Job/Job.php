<?php

namespace BrighteCapital\QueueClient\Job;

use BrighteCapital\QueueClient\Strategies\Retry;
use Interop\Queue\Message;

class Job
{
    protected $message;
    protected $success = false;
    protected $retry = null;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param mixed $success
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return Retry|null
     */
    public function getRetry(): ?Retry
    {
        return $this->retry;
    }

    /**
     * @param Retry $retry
     */
    public function setRetry(Retry $retry): void
    {
        $this->retry = $retry;
    }
}
