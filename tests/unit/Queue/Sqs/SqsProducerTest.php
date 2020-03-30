<?php

namespace App\Test\Queue\Sqs;

use Aws\Result;
use BrighteCapital\QueueClient\Queue\Sqs\SqsContext;
use BrighteCapital\QueueClient\Queue\Sqs\SqsProducer;
use Enqueue\Sqs\SqsClient;
use Enqueue\Sqs\SqsDestination;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;

class SqsProducerTest extends TestCase
{
    /**
     * @var \BrighteCapital\QueueClient\Queue\Sqs\SqsContext|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;
    /**
     * @var \BrighteCapital\QueueClient\Queue\Sqs\SqsProducer
     */
    private $producer;
    /**
     * @var \Enqueue\Sqs\SqsDestination|\PHPUnit\Framework\MockObject\MockObject
     */
    private $destination;
    /**
     * @var \Enqueue\Sqs\SqsDestination|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sqsClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->createMock(SqsContext::class);
        $this->producer = new SqsProducer($this->context);
        $this->destination = $this->createMock(SqsDestination::class);
        $this->sqsClient = $this->createMock(SqsClient::class);
    }

    public function testSend()
    {
        $msg = $this->createMock(SqsMessage::class);
        $region = 'ap-east-2';
        $delay = 10;
        $queueName = 'Queue.name.fifo';
        $queueUrl = 'Queue.url.abc.com.amazon';
        $deDupId = '1';
        $groupId = '1';
        $messageBody = 'this is the messageBody';

        $msg->expects($this->once())
            ->method('getBody')
            ->willReturn($messageBody);

        $this->destination->expects($this->once())
            ->method('getRegion')
            ->willReturn($region);

        $msg->expects($this->exactly(2))
            ->method('getDelaySeconds')
            ->willReturn($delay);

        $msg->expects($this->once())
            ->method('getHeaders')
            ->willReturn([]);

        $properties = [
            'service' => 'salesforce',
            'method' => 'createAccount'
        ];
        $msg->expects($this->once())
            ->method('getProperties')
            ->willReturn($properties);

        $this->destination->expects($this->once())
            ->method('getQueueName')
            ->willReturn($queueName);

        $msg->expects($this->exactly(2))
            ->method('getMessageDeduplicationId')
            ->willReturn($deDupId);

        $msg->expects($this->exactly(2))
            ->method('getMessageGroupId')
            ->willReturn($groupId);


        $this->context->expects($this->once())
            ->method('getQueueUrl')
            ->willReturn($queueUrl);

        $this->context->expects($this->once())->method('getSqsClient')->willReturn($this->sqsClient);

        $expectedArgumentFormat = [
            '@region' => $region,
            'MessageBody' => $messageBody,
            'QueueUrl' => $queueUrl,
            'DelaySeconds' => $delay,
            'MessageAttributes' => [
                'service' => ['DataType' => 'String', 'StringValue' => 'salesforce'],
                'method' => ['DataType' => 'String', 'StringValue' => 'createAccount'],

            ],
            'MessageDeduplicationId' => $deDupId,
            'MessageGroupId' => $groupId,
        ];

        $this->sqsClient->expects($this->once())->method('sendMessage')->with($expectedArgumentFormat);
        $this->producer->send($this->destination, $msg);
    }

    public function testSendBodyEmpty()
    {
        $msg = $this->createMock(SqsMessage::class);
        $msg->expects($this->once())->method('getBody')->willReturn('');
        try {
            $this->producer->send($this->destination, $msg);
        } catch (\Exception $e) {
            $this->assertEquals('The message body must be a non-empty string.', $e->getMessage());
        }
    }

    public function testSendMessageNotSent()
    {
        $msg = $this->createMock(SqsMessage::class);
        $msg->expects($this->once())->method('getBody')->willReturn('test');
        $this->context->expects($this->once())->method('getSqsClient')->willReturn($this->sqsClient);
        $result = new Result(['test' => 'test']);
        $this->sqsClient->expects($this->once())->method('sendMessage')->willReturn($result);

        try {
            $this->producer->send($this->destination, $msg);
        } catch (\Exception $e) {
            $this->assertEquals('Message was not sent', $e->getMessage());
        }
    }
}
