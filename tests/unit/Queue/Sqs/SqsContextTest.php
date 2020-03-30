<?php

namespace App\Test\Queue\Sqs;

use BrighteCapital\QueueClient\Queue\Sqs\SqsConsumer;
use BrighteCapital\QueueClient\Queue\Sqs\SqsContext;
use BrighteCapital\QueueClient\Queue\Sqs\SqsProducer;
use Enqueue\Sqs\SqsDestination;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;

class SqsContextTest extends TestCase
{

    /**
     * @var \BrighteCapital\QueueClient\Queue\Sqs\SqsContext|\PHPUnit\Framework\MockObject\MockObject
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

    public function testCreateProducer()
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
