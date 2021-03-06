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
    public const DEFAULT_DELAY = 60;
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

    /** @var StrategyFactory */
    protected $strategyFactory;

    /**
     * QueueClient constructor.
     * @param array $config
     * @param LoggerInterface $logger
     * @param NotificationChannelInterface|null $notification
     * @param MessageStorageInterface|null $storage
     * @param StrategyFactory|null $strategyFactory
     * @param QueueClientFactory $clientFactory |null $clientFactory
     * @param BlockerHandlerFactory|null $blockerHandlerFactory
     * @throws \Exception
     */
    public function __construct(
        array $config,
        LoggerInterface $logger = null,
        NotificationChannelInterface $notification = null,
        MessageStorageInterface $storage = null,
        StrategyFactory $strategyFactory = null,
        QueueClientFactory $clientFactory = null,
        BlockerHandlerFactory $blockerHandlerFactory = null
    ) {
        $clientFactory = $clientFactory ?? new QueueClientFactory();
        $this->client = $clientFactory->create($config);

        $this->storage = $storage ?: new NullStorage();
        $this->logger = $logger ?: new NullLogger();
        $this->notification = $notification ?: new NullNotificationChannel();

        $blockerHandlerFactory = $blockerHandlerFactory ??
            new BlockerHandlerFactory($this->client, $this->logger, $this->notification, $this->storage);

        $this->blockerHandler = $blockerHandlerFactory->create($config);

        $this->defaultDelay = $config['defaultMaxDelay'] ?? self::DEFAULT_DELAY;

        $this->strategyFactory = $strategyFactory ??
            new StrategyFactory($this->client, $this->storage, $this->logger, $this->notification, $this->defaultDelay);
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

        if (!$message) {
            return;
        }

        /** @var Job $job */
        $job = $jobManager->create($message);

        $this->logger->debug('Queue message start processing', [
            'messageId' => $message->getMessageId(),
            'body' => $message->getBody(),
            'retry' => $job->getRetry(),
        ]);

        if ($this->blockerHandler->checkAndHandle($job) === true) {
            $this->logger->debug('Message has been handled and skipped processing.', [
                'messageId' => $message->getMessageId()
            ]);
            return;
        }

        try {
            $job = $jobManager->process($job);
        } catch (\Exception $e) {
            $this->logger->critical('Job manager process Failed.', [
                'exception' => $e->getMessage(),
                'messageId' => $message->getMessageId(),
            ]);
            $job->getRetry()->pushErrorMessage($e->getMessage());
            $job->setSuccess(false);
        }

        $this->logger->debug('Queue message end processing', [
            'messageId' => $message->getMessageId(),
            'retry' => $job->getRetry(),
        ]);

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
    public function receive($timeout = 0): ?Message
    {
        $message = $this->client->receive($timeout);

        if ($message) {
            $this->logger->debug('Queue message received', ['messageId' => $message->getMessageId()]);
        }

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
        $this->logger->debug('Queue message Deleted', ['messageId' => $message->getMessageId()]);
    }

    /**
     * @param \Interop\Queue\Message $message message
     * @param \BrighteCapital\QueueClient\Strategies\Retry|null $retry
     * @throws \Exception
     */
    public function reject(Message $message, Retry $retry): void
    {
        $strategy = $this->strategyFactory->create($retry);
        $strategy->handle($message);
        $this->logger->debug('Queue message rejected.', ['messageId' => $message->getMessageId()]);
    }

    /**
     * Directly remove a message from queue
     *
     * @param \Interop\Queue\Message $message
     * @return void
     */
    public function remove(Message $message): void
    {
        $this->client->reject($message);
        $this->logger->debug('Queue meesge removed.', ['messageId' => $message->getMessageId()]);
    }
}
