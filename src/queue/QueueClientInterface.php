<?php

namespace BrighteCapital\QueueClient\queue;

use Interop\Queue\Message;

interface QueueClientInterface
{
    /**
     * @param int $timeout timeout
     * @return \Interop\Queue\Message
     */
    public function receive($timeout = 0): Message;

    /**
     * @param string $body body
     * @param array $properties properties
     * @param array $headers headers
     * @return \Interop\Queue\Message
     */
    public function createMessage(string $body, array $properties = [], array $headers = []): Message;

    /**
     * @param \Interop\Queue\Message $message message
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\Exception\InvalidDestinationException
     * @throws \Interop\Queue\Exception\InvalidMessageException
     */
    public function send(Message $message): void;

    /**
     * @param \Interop\Queue\Message $message message
     */
    public function acknowledge(Message $message): void;

    /**
     * @param \Interop\Queue\Message $message message
     */
    public function reject(Message $message): void;

    public function delay(Message $message, int $seconds = 0): void;
}
