<?php

namespace BrighteCapital\QueueClient\Job;

use BrighteCapital\QueueClient\Strategies\Retry;
use Interop\Queue\Message;
use stdClass;

class Job
{
    protected $message;
    protected $success = false;
    protected $retry = null;
    protected $result = null;
    protected $json = null;

    public function __construct(Message $message, Retry $retry)
    {
        $this->message = $message;
        $this->retry = $retry;
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

    /**
     * @param mixed $result
     * @return void
     */
    public function setResult($result): void
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    public function getJson(): ?stdClass
    {
        if ($this->json === null && $this->message) {
            $this->json = \json_decode($this->message->getBody());
        }

        return $this->json;
    }
}
