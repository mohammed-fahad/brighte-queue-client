<?php

namespace App\Test\Job;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\NonBlockerRetryStrategy;
use DateTime;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{

    protected $job;
    protected $message;
    protected function setUp()
    {
        parent::setUp();
        $this->message = new SqsMessage('test');
        $this->job = new Job($this->message, 0, 0, NonBlockerRetryStrategy::class, true);
    }

    public function testGettersSetters()
    {
        $this->assertEquals('test', $this->job->getMessage()->getBody());
        $this->assertEquals(NonBlockerRetryStrategy::class, $this->job->getStrategy());
        $this->assertFalse($this->job->getSuccess());
        $this->assertEquals(0, $this->job->getDelay());
        $this->assertEquals(0, $this->job->getMaxRetryCount());
        $this->assertEquals('', $this->job->getErrorMessage());

        $this->message->setBody('test2');
        $this->job->setStrategy(BlockerStorageRetryStrategy::class);
        $this->job->setMessage($this->message);
        $this->job->setSuccess(true);
        $this->job->setResult('record updated');
        $this->job->setDelay(2);
        $this->job->setMaxRetryCount(2);
        $this->job->setStrategy('strategy');
        $this->job->setErrorMessage('error3');

        $this->assertEquals('test2', $this->job->getMessage()->getBody());
        $this->assertEquals('strategy', $this->job->getStrategy());
        $this->assertTrue($this->job->getSuccess());
        $this->assertEquals('record updated', $this->job->getResult());
        $this->assertEquals(2, $this->job->getDelay());
        $this->assertEquals(2, $this->job->getMaxRetryCount());
        $this->assertEquals('strategy', $this->job->getStrategy());
        $this->assertEquals('error3', $this->job->getErrorMessage());
    }

    public function testPushErrorMessage()
    {
        $job = new Job($this->message, 0, 0, NonBlockerRetryStrategy::class, '');
        $job->pushErrorMessage('error 1');
        $job->pushErrorMessage('error 2');
        $lines = explode("\n", $job->getErrorMessage());
        $this->assertCount(2, $lines);
        $this->assertContains((new DateTime())->format('Y-m-d'), $lines[0]);
    }
}
