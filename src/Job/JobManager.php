<?php

declare(strict_types=1);

namespace BrighteCapital\QueueClient\Job;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;

class JobManager implements \BrighteCapital\QueueClient\Job\JobManagerInterface
{
    protected $logger;

    protected $processors = [];

    protected $deciders = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function createRetry(Message $message): Retry
    {
        return new Retry(30, 3, NonBlockerRetryStrategy::class);
    }

    public function create(Message $message): Job
    {
        return new Job(
            $message,
            $this->createRetry($message)
        );
    }

    public function process(Job $job): Job
    {
        $type = strtolower($job->getJson()->type ?? 'invalid');
        $processor = $this->processors[$type] ?? null;

        if (!$processor) {
            $this->logger->error(__METHOD__ . ': unknown type', [
                'message' => $job->getMessage()->getBody()
            ]);
            $job->setSuccess(true);

            return $job;
        }

        try {
            $processor->process($job);
        } catch (\Exception $e) {
            $this->logger->error(__METHOD__ . ': error processing message', [
                'message' => $job->getMessage()->getBody()
            ]);
        }

        if (!$job->getSuccess()) {
            $decider = $this->deciders[$type] ?? null;

            if ($decider && !$decider->shouldRetry($job)) {
                $job->getRetry()->setMaxRetryCount(0);
                $job->getRetry()->setStrategy(NonBlockerRetryStrategy::class);
            }
        }

        return $job;
    }

    public function setProcessor(string $type, JobProcessorInterface $processor): void
    {
        $this->processors[$type] = $processor;
    }

    public function setDecider(string $type, JobDeciderInterface $decider): void
    {
        $this->deciders[$type] = $decider;
    }
}
