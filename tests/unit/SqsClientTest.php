<?php

use App\queue\sqs\SqsClient;
use App\queue\sqs\SqsConfig;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;

class SqsClientTest extends TestCase
{

    /**
     * @var \App\queue\sqs\SqsClient
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
