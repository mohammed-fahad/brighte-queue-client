<?php

namespace App\Test\Queue\Sqs;

use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Queue\Sqs\SqsConsumer;
use BrighteCapital\QueueClient\Queue\Sqs\SqsContext;
use BrighteCapital\QueueClient\Queue\Sqs\SqsProducer;
use Enqueue\Sqs\SqsDestination;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;

class SqsClientTest extends TestCase
{

    /**
     * @var SqsClient
     */
    private $sqsClient;
    /**
     * @var \BrighteCapital\QueueClient\Queue\Sqs\SqsContext|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sqsContext;
    /**
     * @var \BrighteCapital\QueueClient\Queue\Sqs\SqsProducer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $producer;
    /**
     * @var \BrighteCapital\QueueClient\Queue\Sqs\SqsConsumer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $consumer;
    /**
     * @var \Enqueue\Sqs\SqsDestination|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sqsDestination;

    protected function setUp(): void
    {
        $config = [
            'key' => 'key',
            'secret' => 'secret',
            'region' => 'ap-east-2',
            "provider" => "Sqs",
            'Queue' => 'Queue name here',
        ];

        parent::setUp();

        $this->sqsContext = $this->createMock(SqsContext::class);
        $this->producer = $this->createMock(SqsProducer::class);
        $this->consumer = $this->createMock(SqsConsumer::class);
        $this->sqsDestination = $this->createMock(SqsDestination::class);

        $this->sqsContext
            ->expects($this->once())
            ->method('createQueue')
            ->willReturn($this->createMock(SqsDestination::class));

        $this->sqsClient = new SqsClient($config['Queue'], $this->sqsContext);
    }

    public function testReceiveMessage()
    {

        $this->sqsContext
            ->expects($this->once())
            ->method('createConsumer')
            ->willReturn($this->consumer);

        $this->consumer->expects($this->once())
            ->method('receive')
            ->willReturn(new SqsMessage());
        $this->assertInstanceOf(SqsMessage::class, $this->sqsClient->receive());
    }

    public function testCreateMessage()
    {
        $this->sqsContext->expects($this->once())
            ->method('createMessage')
            ->willReturn(new SqsMessage());
        $this->assertInstanceOf(SqsMessage::class, $this->sqsClient->createMessage("this is the bodyt"));
    }

    public function testSend()
    {
        $this->sqsContext->expects($this->once())
            ->method('createProducer')
            ->willReturn(new SqsProducer($this->sqsContext));
        $msg = new SqsMessage("First message");
        $this->sqsClient->send($msg);
        $this->sqsClient->send($msg);
    }

    public function testAcknowledge()
    {
        $this->sqsContext
            ->expects($this->once())
            ->method('createConsumer')
            ->willReturn($this->consumer);
        $msg = new SqsMessage();
        $this->consumer->expects($this->once())->method('acknowledge')->with($msg);
        $this->sqsClient->acknowledge($msg);
    }


    public function testReject()
    {
        $this->sqsContext
            ->expects($this->once())
            ->method('createConsumer')
            ->willReturn($this->consumer);
        $msg = new SqsMessage();
        $this->consumer->expects($this->once())->method('reject')->with($msg);
        $this->sqsClient->reject($msg);
    }

    public function testDelay()
    {
        $delayCount = 1000;
        $msg = $this->createMock(SqsMessage::class);
        $msg->expects($this->once())->method('setRequeueVisibilityTimeout')->with($delayCount);

        $this->sqsContext
            ->expects($this->once())
            ->method('createConsumer')
            ->willReturn($this->consumer);
        $this->consumer->expects($this->once())->method('reject')->withConsecutive([$msg, true]);
        $this->sqsClient->delay($msg, $delayCount);
    }

    public function testGetConsumer()
    {
        $this->sqsContext
            ->expects($this->once())
            ->method('createConsumer')
            ->willReturn($this->consumer);

        $this->assertEquals($this->consumer, $this->sqsClient->getConsumer());
    }

    public function testGetProducer()
    {
        $this->sqsContext
            ->expects($this->once())
            ->method('createproducer')
            ->willReturn($this->producer);
        $this->assertEquals($this->producer, $this->sqsClient->getProducer());
    }

    public function testGetSqsDestination()
    {
        $this->assertEquals($this->sqsDestination, $this->sqsClient->getDestination());
    }

    public function testGetContext()
    {
        $this->assertEquals($this->sqsContext, $this->sqsClient->getContext());
    }
}
