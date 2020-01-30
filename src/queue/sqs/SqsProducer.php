<?php


namespace App\queue\sqs;


use Enqueue\Sqs\SqsContext;
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
            'MessageAttributes' => [
                'Headers' => [
                    'DataType' => 'String',
                    'StringValue' => json_encode([$message->getHeaders()]),
                ],
            ],
            'MessageBody' => $body,
            'QueueUrl' => $this->context->getQueueUrl($destination),
        ];

        foreach ($message->getProperties() as $name => $value) {
            $arguments['MessageAttributes'][$name] = $value;
        }

        if (isset($body['DelaySeconds'])) {
            $arguments['DelaySeconds'] = (int) $body['DelaySeconds'] / 1000;
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
    }
}
