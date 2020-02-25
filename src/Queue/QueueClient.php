<?php

namespace BrighteCapital\QueueClient\Queue;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Job\JobManagerInterface;
use BrighteCapital\QueueClient\Notifications\Channels\NotificationChannelInterface;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Factories\BlockerHandlerFactory;
use BrighteCapital\QueueClient\Queue\Factories\QueueClientFactory;
use BrighteCapital\QueueClient\Queue\Factories\StrategyFactory;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use BrighteCapital\QueueClient\Storage\NullStorage;
use BrighteCapital\QueueClient\Strategies\Retry;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class QueueClient
{
    /**
     * @var \BrighteCapital\QueueClient\Queue\QueueClientInterface
     */
    protected $client;

    /** @var array */
    protected $config;

    /** @var BlockerHandlerInterface */
    protected $blockerHandler;

    /** @var LoggerInterface */
    protected $logger;

    /** @var MessageStorageInterface */
    protected $storage;

    /** @var NotificationChannelInterface */
    protected $notification;

    /** @var int */
    protected $defaultDelay;
    /**
     * QueueClient constructor.
     * @param array $config
     * @param LoggerInterface $logger
     * @param NotificationChannelInterface|null $notification
     * @param MessageStorageInterface|null $storage
     * @throws \Exception
     */
    public function __construct(
        array $config,
        LoggerInterface $logger = null,
        NotificationChannelInterface $notification = null,
        MessageStorageInterface $storage = null
    ) {
        $clientFactor = new QueueClientFactory();
        $this->client = $clientFactor->create($config);

        $this->storage = $storage ?: new NullStorage();
        $this->logger = $logger ?: new NullLogger();
        $this->notification = $notification ?: new NullNotificationChannel();

        $blockerHandlerFactory = new BlockerHandlerFactory($this->client, $this->storage);
        $this->blockerHandler = $blockerHandlerFactory->create($config);
    }

    /**
     * @param JobManagerInterface $jobManager
     * @param int $timeout timeout
     * @return mixed
     * @throws \Exception
     */
    public function processMessage(JobManagerInterface $jobManager, $timeout = 0): void
    {
        $message = $this->receive($timeout);

        /** @var Job $job */
        $job = $jobManager->create($message);

        if ($this->blockerHandler->checkAndHandle($job) === true) {
            return;
        }

        $job = $jobManager->process($job);

        if ($job->getSuccess() === true) {
            $this->acknowledge($message);

            return;
        }

        $this->reject($message, $job->getRetry());
    }

    /**
     * @param int $timeout timeout
     * @return \Interop\Queue\Message
     * @throws \Exception
     */
    public function receive($timeout = 0): Message
    {
        $message = $this->client->receive($timeout);

        return $message;
    }

    /**
     * @param string $body body
     * @param array $properties properties
     * @param array $headers headers
     * @return \Interop\Queue\Message
     */
    public function createMessage(string $body, array $properties = [], array $headers = []): Message
    {
        return $this->client->createMessage($body, $properties, $headers);
    }

    /**
     * @param \Interop\Queue\Message $message message
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\Exception\InvalidDestinationException
     * @throws \Interop\Queue\Exception\InvalidMessageException
     */
    public function send(Message $message): void
    {
        $this->client->send($message);
    }

    /**
     * @param \Interop\Queue\Message $message message
     */
    public function acknowledge(Message $message): void
    {
        $this->client->acknowledge($message);
    }

    /**
     * @param \Interop\Queue\Message $message message
     * @param \BrighteCapital\QueueClient\Strategies\Retry|null $retry
     * @throws \Exception
     */
    public function reject(Message $message, Retry $retry): void
    {
        $strategyFactory = new StrategyFactory($this->client, $retry, $this->storage);
        $strategy = $strategyFactory->create();
        $strategy->handle($message);
    }
}
