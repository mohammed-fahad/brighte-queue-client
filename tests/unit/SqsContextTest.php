<?php

namespace tests\unit;

use BrighteCapital\QueueClient\queue\sqs\SqsConsumer;
use BrighteCapital\QueueClient\queue\sqs\SqsContext;
use BrighteCapital\QueueClient\queue\sqs\SqsProducer;
use Enqueue\Sqs\SqsDestination;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;

class SqsContextTest extends TestCase
{

    /**
     * @var \BrighteCapital\QueueClient\queue\sqs\SqsContext|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->getMockBuilder(SqsContext::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['createMessage', 'createProducer', 'createConsumer'])
            ->getMock();
    }

    public function testCreateMessage()
    {
        $msg = $this->context->createMessage("test body");
        $this->assertInstanceOf(SqsMessage::class, $msg);
        $this->assertEquals("test body", $msg->getBody());
    }

    public function createProducer()
    {
        $this->assertInstanceOf(SqsProducer::class, $this->context->createProducer());
    }

    public function testCreateConsumer()
    {
        $this->assertInstanceOf(
            SqsConsumer::class,
            $this->context->createConsumer(new SqsDestination("some destination"))
        );
    }
}
