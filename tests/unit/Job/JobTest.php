<?php

namespace App\Test\Job;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    public function testGettersSetters()
    {
        $message = new SqsMessage('test');
        $retry = new Retry(0, 0, NonBlockerRetryStrategy::class, 'error');
        $job = new Job($message, $retry);

        $this->assertEquals('test', $job->getMessage()->getBody());
        $this->assertEquals(NonBlockerRetryStrategy::class, $job->getRetry()->getStrategy());
        $this->assertFalse($job->getSuccess());

        $message->setBody('test2');
        $retry->setStrategy(BlockerStorageRetryStrategy::class);
        $job->setMessage($message);
        $job->setRetry($retry);
        $job->setSuccess(true);
        $job->setResult('record updated');

        $this->assertEquals('test2', $job->getMessage()->getBody());
        $this->assertEquals(BlockerStorageRetryStrategy::class, $job->getRetry()->getStrategy());
        $this->assertTrue($job->getSuccess());
        $this->assertEquals('record updated', $job->getResult());
    }
}
