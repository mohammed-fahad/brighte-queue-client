<?php

namespace App\Test\AnonymousClasses;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Job\JobManagerInterface;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use Enqueue\Sqs\SqsMessage;
use Interop\Queue\Message;
use PHPUnit\Framework\TestCase;

class JobManager extends TestCase implements JobManagerInterface
{

    public function create(Message $message): Job
    {
        $message = new SqsMessage('testMessage');

        return new Job($message, 0, 0, NonBlockerRetryStrategy::class);
    }

    public function process(Job $job): Job
    {
        return $job;
    }
}
