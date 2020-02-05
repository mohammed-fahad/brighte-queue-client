<?php

namespace tests\unit;

use BrighteCapital\QueueClient\queue\sqs\SqsClient;
use BrighteCapital\QueueClient\queue\sqs\SqsConsumer;
use BrighteCapital\QueueClient\queue\sqs\SqsContext;
use BrighteCapital\QueueClient\queue\sqs\SqsProducer;
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
     * @var \BrighteCapital\QueueClient\queue\sqs\SqsContext|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sqsContext;
    /**
     * @var \BrighteCapital\QueueClient\queue\sqs\SqsProducer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $producer;
    /**
     * @var \BrighteCapital\QueueClient\queue\sqs\SqsConsumer|\PHPUnit\Framework\MockObject\MockObject
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
            "provider" => "sqs",
            'queue' => 'queue name here',
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

        $this->sqsClient = new SqsClient($config['queue'], $this->sqsContext);
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
        /*TODO*/
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
