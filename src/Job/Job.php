<?php

namespace BrighteCapital\QueueClient\Job;

use DateTime;
use Interop\Queue\Message;
use stdClass;

class Job
{
    protected $message;
    protected $success = false;
    protected $result = null;
    protected $errorMessage = '';
    protected $json = null;
    protected $delay;
    protected $maxRetryCount;
    protected $strategy;
    protected $notify;

    public function __construct(
        Message $message,
        int $delays,
        int $maxRetryCount,
        string $strategy,
        bool $notify = true
    ) {
        $this->message = $message;
        $this->delay = $delays;
        $this->maxRetryCount = $maxRetryCount;
        $this->strategy = $strategy;
        $this->notify = $notify;
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


    public function getDelay(): int
    {
        return $this->delay;
    }

    public function getMaxRetryCount(): int
    {
        return $this->maxRetryCount;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function shouldNotify()
    {
        return $this->notify;
    }

    /**
     * @param int $delay
     */
    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    /**
     * @param int $maxRetryCount
     */
    public function setMaxRetryCount(int $maxRetryCount): void
    {
        $this->maxRetryCount = $maxRetryCount;
    }

    /**
     * @param string $strategy
     */
    public function setStrategy(string $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function setNotify(bool $notify): void
    {
        $this->notify = $notify;
    }

    /**
     * @param string $errorMessage
     * @param string $separater
     * @param boolean $withTime
     * @return void
     */
    public function pushErrorMessage(string $errorMessage, string $separater = "\n", bool $withTime = true): void
    {
        if ($this->errorMessage) {
            $this->errorMessage = $separater . $this->errorMessage;
        }

        if ($withTime) {
            $errorMessage = (new DateTime())->format(DateTime::ISO8601) . ' ' . $errorMessage;
        }

        $this->errorMessage = $errorMessage . $this->errorMessage;
    }
}
