<?php


namespace App\queue\sqs;


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

    public function convertMessage(array $sqsMessage): SqsMessage
    {
        $message = $this->context->createMessage();

        $message->setBody($sqsMessage['Body']);
        $message->setReceiptHandle($sqsMessage['ReceiptHandle']);

        if (isset($sqsMessage['Attributes']['ApproximateReceiveCount'])) {
            $message->setRedelivered(((int)$sqsMessage['Attributes']['ApproximateReceiveCount']) > 1);
        }

        if (isset($sqsMessage['MessageAttributes'])) {
            foreach ($sqsMessage['MessageAttributes'] as $key => $value) {
                if ($key === 'Headers') {
                    $headers = json_decode($value['StringValue'], true);
                    $message->setHeaders($headers[0]);
                } else {
                    foreach ($value as $propertyKey => $propertyValue) {
                        $message->setProperty($propertyKey, $propertyValue);
                    }
                }
            }
        }

        return $message;
    }
}
