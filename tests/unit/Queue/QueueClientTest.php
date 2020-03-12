<?php

namespace tests\unit\Queue\Sqs;

use App\Log\Logger;
use BrighteCapital\QueueClient\Example\MySql;
use BrighteCapital\QueueClient\Queue\Factories\QueueClientFactory;
use BrighteCapital\QueueClient\Queue\Factories\StrategyFactory;
use BrighteCapital\QueueClient\Queue\QueueClient;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Doctrine\DBAL\DriverManager;
use Interop\Queue\Message;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;

class QueueClientTest extends TestCase
{
    protected $client;

    protected $connection;

    protected $logger;

    protected $config = [
        'key' => 'key',
        'secret' => 'secret',
        'region' => 'region',
        'queue' => 'queue',
        'provider' => 'sqs',
        'defaultMaxDelay' => 5,
    ];

    protected $dbConfig = [
            'host' => 'host',
            'user' => 'user',
            'password' => 'password',
            'dbname' => 'dbname',
    ];

    protected $factory;

    protected $SqsClient;

    protected function setUp()
    {
        parent::setUp();

        $this->factory = $this
            ->getMockBuilder(QueueClientFactory::class)
            ->setMethods(['create'])
            ->getMock();

        $this->sqsClient = $this->createMock(SqsClient::class);

        $this->factory
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->sqsClient);

        $this->connection = $this->createMock(MySql::class);
    }

    /**
     * @throws \Exception
     */
    public function testCreateMessage()
    {
        $this->client = new QueueClient($this->config, null, null, $this->connection, null, new QueueClientFactory());
        $message = $this->client->createMessage('One ring to rule them all', [], []);

        $this->assertInstanceOf(Message::class, $message);
    }

    /**
     * @throws \Exception
     */
    public function testReceive()
    {
        $this->client = new QueueClient($this->config, null, null, $this->connection, null, $this->factory);

        $received = $this->client->receive(0);
        $this->assertInstanceOf(Message::class, $received);
    }

    public function testSend()
    {
        $this->client = new QueueClient($this->config, null, null, $this->connection, null, $this->factory);
        $message = $this->client->createMessage('One ring to rule them all', [], []);

        $this->sqsClient
            ->expects($this->once())
            ->method('send')
            ->with($message);

        $this->client->send($message);
    }

    public function testAcknowledge()
    {
        $this->logger = $this->createMock(NullLogger::class);
        $this->logger->expects($this->once())->method('debug')->with('Queue message Deleted', $this->anything());

        $this->client = new QueueClient($this->config, $this->logger, null, $this->connection, null, $this->factory);
        $message = $this->client->createMessage('One ring to rule them all', [], []);

        $this->sqsClient
            ->expects($this->once())
            ->method('acknowledge')
            ->with($message);

        $this->client->acknowledge($message);
    }

    public function testReject()
    {

        /** @var \Interop\Queue\Message $message*/
        $retry = new Retry(5, 5, BlockerStorageRetryStrategy::class);

        $strategyFactory = $this->createMock(StrategyFactory::class);
        $strategyFactory
            ->expects($this->any())
            ->method('create')
            ->with($retry)
            ->willReturn('XXXXXXXXXX');

        $this->client = new QueueClient($this->config, $this->logger, null, $this->connection, $strategyFactory, $this->factory);



    }

    public function testProcessMessage()
    {

    }
}