<?php

namespace App\Test\Queue;

use App\Test\AnonymousClasses\JobManager;
use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Factories\BlockerHandlerFactory;
use BrighteCapital\QueueClient\Queue\Factories\QueueClientFactory;
use BrighteCapital\QueueClient\Queue\Factories\StrategyFactory;
use BrighteCapital\QueueClient\Queue\QueueClient;
use BrighteCapital\QueueClient\Queue\Sqs\SqsBlockerHandler;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Storage\NullStorage;
use BrighteCapital\QueueClient\Strategies\AbstractRetryStrategy;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Enqueue\Sqs\SqsMessage;
use Interop\Queue\Exception\Exception;
use Interop\Queue\Message;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class QueueClientTest extends TestCase
{
    protected $client;
    protected $storage;
    protected $logger;
    protected $clientFactory;
    protected $strategyFactory;
    protected $blockerHandlerFactory;
    protected $sqsClient;
    protected $sqsBlockerHandler;

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

    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->sqsClient = $this->createMock(SqsClient::class);
        $this->logger = $this->getMockBuilder(NullLogger::class)->getMock();
        $this->sqsBlockerHandler = $this->getMockBuilder(SqsBlockerHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkAndHandle'])
            ->getMock();

        $this->clientFactory = $this->getMockBuilder(QueueClientFactory::class)
            ->getMock();
        $this->strategyFactory = $this->getMockBuilder(StrategyFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->blockerHandlerFactory = $this->getMockBuilder(BlockerHandlerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->sqsClient);
        $this->blockerHandlerFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->sqsBlockerHandler);

        $notification = new NullNotificationChannel();

        $this->storage = $this->createMock(NullStorage::class);

        $this->client = new QueueClient(
            $this->config,
            $this->logger,
            $notification,
            $this->storage,
            $this->strategyFactory,
            $this->clientFactory,
            $this->blockerHandlerFactory
        );
    }

    /**
     * @throws \Exception
     */
    public function testCreateMessage()
    {
        $message = $this->client->createMessage('One ring to rule them all', [], []);
        $this->assertInstanceOf(Message::class, $message);
    }

    /**
     * @throws \Exception
     */
    public function testReceive()
    {
        $received = $this->client->receive(0);
        $this->assertInstanceOf(Message::class, $received);
    }

    public function testSend()
    {
        $message = $this->client->createMessage('One ring to find them', [], []);

        $this->sqsClient
            ->expects($this->once())
            ->method('send')
            ->with($message);

        $this->client->send($message);
    }

    public function testAcknowledge()
    {
        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Queue message Deleted', $this->anything());

        $message = $this->client->createMessage('One ring to bring them all', [], []);

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

        $strategy = $this->createMock(AbstractRetryStrategy::class);
        $strategy
            ->expects($this->once())
            ->method('handle');

        $strategyFactory = $this->createMock(StrategyFactory::class);
        $strategyFactory
            ->expects($this->any())
            ->method('create')
            ->with($retry)
            ->willReturn($strategy);

        $this->client = new QueueClient(
            $this->config,
            $this->logger,
            null,
            $this->storage,
            $strategyFactory,
            $this->clientFactory
        );
        $message = $this->client->createMessage('One ring to find them', [], []);

        $this->client->reject($message, $retry);
    }

    public function testConstruct()
    {
        $client = new QueueClient(
            $this->config,
            $this->logger,
            null,
            $this->storage,
            null,
            $this->clientFactory
        );
        $this->assertInstanceOf(QueueClient::class, $client);
    }

    public function testProcessMessage()
    {
        $message = new SqsMessage('test');
        $job = new Job($message, new Retry(0, 0, NonBlockerRetryStrategy::class, 'testError'));
        $job->setSuccess(true);
        $jobManager = $this->getMockBuilder(JobManager::class)->getMock();
        $jobManager->expects($this->once())->method('create')->willReturn($job);
        $jobManager->expects($this->once())->method('process')->willReturn($job);
        $this->sqsBlockerHandler->expects($this->once())->method('checkAndHandle')->willReturn(false);
        $this->client->processMessage($jobManager);
    }

    public function testProcessMessageRetry()
    {
        $message = new SqsMessage('test');
        $job = new Job($message, new Retry(0, 0, NonBlockerRetryStrategy::class, 'testError'));
        $jobManager = $this->getMockBuilder(JobManager::class)->getMock();
        $jobManager->expects($this->once())->method('create')->willReturn($job);
        $jobManager->expects($this->once())->method('process')->willReturn($job);
        $this->sqsBlockerHandler->expects($this->once())->method('checkAndHandle')->willReturn(false);
        $this->client->processMessage($jobManager);
    }

    public function testProcessHandled()
    {
        $message = new SqsMessage('test');
        $job = new Job($message, new Retry(0, 0, NonBlockerRetryStrategy::class, 'testError'));
        $jobManager = $this->getMockBuilder(JobManager::class)->getMock();
        $jobManager->expects($this->once())->method('create')->willReturn($job);
        $this->sqsBlockerHandler->expects($this->once())->method('checkAndHandle')->willReturn(true);
        $this->client->processMessage($jobManager);
    }

    public function testProcessThrowException()
    {
        $message = new SqsMessage('test');
        $job = new Job($message, new Retry(0, 0, NonBlockerRetryStrategy::class, 'testError'));
        $jobManager = $this->getMockBuilder(JobManager::class)->getMock();
        $jobManager->expects($this->once())->method('create')->willReturn($job);
        $jobManager->expects($this->once())->method('process')->willThrowException(new Exception('processFailedJobManager'));
        $this->sqsBlockerHandler->expects($this->once())->method('checkAndHandle')->willReturn(false);
        try {
            $this->client->processMessage($jobManager);
        } catch (\Exception $exception) {
            $this->assertEquals('processFailedJobManager', $exception->getMessage());
        }
    }
}
