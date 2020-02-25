<?php

namespace BrighteCapital\QueueClient\Queue\Sqs;

use Enqueue\Sqs\SqsContext;
use Enqueue\Sqs\SqsDestination;
use Enqueue\Sqs\SqsMessage;

class SqsConsumer extends \Enqueue\Sqs\SqsConsumer
{
    private $context;

    public function __construct(SqsContext $context, SqsDestination $queue)
    {
        parent::__construct($context, $queue);
        $this->context = $context;
    }

    protected function convertMessage(array $sqsMessage): SqsMessage
    {
        $message = $this->context->createMessage();

        $message->setBody($sqsMessage['Body']);
        $message->setReceiptHandle($sqsMessage['ReceiptHandle']);
        $message->setMessageId($sqsMessage['MessageId']);

        if (isset($sqsMessage['Attributes']['ApproximateReceiveCount'])) {
            $message->setRedelivered(((int)$sqsMessage['Attributes']['ApproximateReceiveCount']) > 1);
        }

        if (isset($sqsMessage['MessageAttributes'])) {
            foreach ($sqsMessage['MessageAttributes'] as $key => $value) {
                if ($key === 'Headers') {
                    $headers = json_decode($value['StringValue'], true);
                    $message->setHeaders($headers[0]);
                } else {
                    $message->setProperty($key, $value['StringValue']);
                }
            }
        }

        if (isset($sqsMessage['Attributes'])) {
            foreach ($sqsMessage['Attributes'] as $key => $value) {
                $message->setProperty($key, $value);
            }
        }

        return $message;
    }
}
