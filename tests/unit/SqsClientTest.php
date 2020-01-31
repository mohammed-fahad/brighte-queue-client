<?php

use BrighteCapital\QueueClient\queue\sqs\SqsClient;
use BrighteCapital\QueueClient\queue\sqs\SqsConfig;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;

class SqsClientTest extends TestCase
{

    /**
     * @var SqsClient
     */
    private $sqsClient;

    protected function setUp()
    {
        $config = [
            'key' => 'key',
            'secret' => 'secret',
            'isFifo' => true,
            "provider" => "sqs",
            'queue' => 'queue name here',
        ];

        $sqsConfig = new SqsConfig($config);
        parent::setUp();
        $this->sqsClient = new SqsClient($sqsConfig);
    }

    public function testCreateMessage()
    {
        $this->assertInstanceOf(SqsMessage::class, $this->sqsClient->createMessage("this is the bodyt"));
    }
}
