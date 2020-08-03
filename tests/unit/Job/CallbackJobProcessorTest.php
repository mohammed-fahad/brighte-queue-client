<?php

namespace App\Test\Job;

use BrighteCapital\QueueClient\Job\CallbackJobProcessor;
use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;

class CallbackJobProcessorTest extends TestCase
{
    public function test()
    {
        $processor = new CallbackJobProcessor(function (Job $job) {
            $job->setSuccess(true);
            $job->setResult(['id' => 1]);
        });

        $job = new Job(
            new SqsMessage('test'),
            new Retry(60, 3, NonBlockerRetryStrategy::class)
        );

        $processor->process($job);
        $this->assertTrue($job->getSuccess());
        $this->assertEquals(['id' => 1], $job->getResult());
    }
}
