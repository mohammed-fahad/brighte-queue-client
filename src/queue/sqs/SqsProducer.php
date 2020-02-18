<?php

namespace BrighteCapital\QueueClient\queue\sqs;

use Enqueue\Sqs\SqsContext;
use Enqueue\Sqs\SqsDestination;
use Enqueue\Sqs\SqsMessage;
use Interop\Queue\Destination;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;
use Interop\Queue\Message;

class SqsProducer extends \Enqueue\Sqs\SqsProducer
{

    /**
     * @var SqsContext
     */
    private $context;

    public function __construct(SqsContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param Destination $destination
     * @param Message $message
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     */
    public function send(Destination $destination, Message $message): void
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, SqsDestination::class);
        InvalidMessageException::assertMessageInstanceOf($message, SqsMessage::class);

        $body = $message->getBody();

        if ($body === '') {
            throw new InvalidMessageException('The message body must be a non-empty string.');
        }

        $arguments = [
            '@region' => $destination->getRegion(),
            'MessageBody' => $body,
            'QueueUrl' => $this->context->getQueueUrl($destination),
        ];

        if ($message->getDelaySeconds()) {
            $arguments['DelaySeconds'] = $message->getDelaySeconds();
        }

        if ($message->getHeaders()) {
            $arguments['MessageAttributes']['Headers'] = [
                'DataType' => 'String',
                'StringValue' => json_encode([$message->getHeaders()]),
            ];
        }

        foreach ($message->getProperties() as $name => $value) {
            $arguments['MessageAttributes'][$name] = ['DataType' => 'String', 'StringValue' => $value];
        }

        if (substr($destination->getQueueName(), -5) === '.fifo') {
            if ($message->getMessageDeduplicationId()) {
                $arguments['MessageDeduplicationId'] = $message->getMessageDeduplicationId();
            }

            if ($message->getMessageGroupId()) {
                $arguments['MessageGroupId'] = $message->getMessageGroupId();
            }
        }

        $result = $this->context->getSqsClient()->sendMessage($arguments);

        if ($result->hasKey('MessageId') === false) {
            throw new \RuntimeException('Message was not sent');
        }

        $message->setMessageId($result['MessageId']);
    }
}
