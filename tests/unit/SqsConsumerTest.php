<?php

namespace tests\unit;

use BrighteCapital\QueueClient\queue\sqs\SqsConsumer;
use BrighteCapital\QueueClient\queue\sqs\SqsContext;
use Enqueue\Sqs\SqsDestination;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SqsConsumerTest extends TestCase
{
    public function testConvertMessageBodyAndHandle()
    {

        $messageArray = [
            'Body' => 'message body',
            'ReceiptHandle' => 'handle123',
            'Attributes' => [
                'ApproximateReceiveCount' => 2,
            ]
        ];
        $convertedMessage = $this->invokeConvertMessage($messageArray);

        $this->assertEquals('message body', $convertedMessage->getBody());
        $this->assertEquals('handle123', $convertedMessage->getReceiptHandle());
        $this->assertTrue($convertedMessage->isRedelivered());
    }

    public function testConvertMessageAttributesAndProperties()
    {
        $messageArray = [
            'Body' => 'message body',
            'ReceiptHandle' => 'handle123',
            'MessageAttributes' => [
                'service' => [
                    'DataType' => 'String',
                    'StringValue' => 'LoanManagement',
                ],
                'method' => [
                    'DataType' => 'String',
                    'StringValue' => 'createAccount',
                ]
            ]
        ];
        $convertedMessage = $this->invokeConvertMessage($messageArray);
        $expectedProperties = [
            'service' => 'LoanManagement',
            'method' => 'createAccount',
        ];
        $this->assertEquals($expectedProperties, $convertedMessage->getProperties());
        $this->assertFalse($convertedMessage->isRedelivered());
    }

    public function testConvertMessageHeaders()
    {
        $messageArray = [
            'Body' => 'message body',
            'ReceiptHandle' => 'handle123',
            'MessageAttributes' => [
                'Headers' => [
                    'StringValue' =>
                        '[{"header1":"header1Value","header2":"header2Value"},{"otherData":"otherDAta"}]'
                ]
            ]
        ];

        $convertedMessage = $this->invokeConvertMessage($messageArray);
        $expectedProperties = [
            'header1' => 'header1Value',
            'header2' => 'header2Value',
        ];
        $this->assertEquals($expectedProperties, $convertedMessage->getHeaders());
        $this->assertFalse($convertedMessage->isRedelivered());
    }

    public function invokeConvertMessage(array $sqsMessage): SqsMessage
    {
        $context = $this->createMock(SqsContext::class);
        $destination = $this->createMock(SqsDestination::class);
        $msg = new SqsMessage();

        $context->expects($this->once())->method('createMessage')->willReturn($msg);

        $sqsConsumer = new SqsConsumer($context, $destination);

        $class = new ReflectionClass($sqsConsumer);
        $property = $class->getMethod('convertMessage');
        $property->setAccessible(true);

        /**@var SqsMessage $convertedMessage */
        return $property->invokeArgs($sqsConsumer, [$sqsMessage]);
    }
}
