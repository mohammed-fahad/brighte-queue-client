<?php


namespace App\strategies;


use Interop\Queue\Message;

class FailedMessage implements FailedMessageInterface
{
    /**
     * @var \Enqueue\strategies\RetryStrategyInterface
     */
    protected $retryStrategy;
    protected $delay;
    protected $retries;
    private $message;

    public function __construct(RetryStrategyInterface $retryStrategy, Message $message, $delays, $retries)
    {
        if ($retryStrategy === null) {
            $retryStrategy = new StrategyA();
        }
        $this->message = $message;
        $this->retryStrategy = $retryStrategy;
        $this->delay = $delays;
        $this->retries = $retries;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function getMaxRetries(): int
    {
        return $this->retries;
    }

    public function getStrategy(): RetryStrategyInterface
    {
        return $this->retryStrategy;
    }
}
