<?php

namespace BrighteCapital\QueueClient\Strategies;

use Interop\Queue\Message;

class BlockerRetryStrategy extends AbstractRetryStrategy
{
    /**
     * @param Message $message
     * @throws \Exception
     */
    protected function onMaxRetryReached(Message $message): void
    {
        $this->logger->debug(printf('%s: Message blocked & delayed.', __METHOD__), [
            'messageId' => $message->getMessageId(),
            'delayInSecond' => $this->delay
        ]);
        $this->client->delay($message, $this->delay);
    }
}
