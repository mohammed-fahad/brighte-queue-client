<?php

namespace App\Test\AnonymousClasses;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Job\JobManagerInterface;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Enqueue\Sqs\SqsMessage;
use Interop\Queue\Message;
use PHPUnit\Framework\TestCase;

class JobManager extends TestCase implements JobManagerInterface
{

    public function create(Message $message): Job
    {
        $message = new SqsMessage('testMessage');
        $retry = new Retry(0, 0, NonBlockerRetryStrategy::class);
        return new Job($message, $retry);
    }

    public function process(Job $job): Job
    {
        return $job;
    }
}
