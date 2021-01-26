<?php

namespace App\Test\Job;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Job\JobDeciderInterface;
use BrighteCapital\QueueClient\Job\JobManager;
use BrighteCapital\QueueClient\Job\JobProcessorInterface;
use Enqueue\Sqs\SqsMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class JobManagerTest extends TestCase
{
    protected $logger;
    protected $jobManager;

    protected function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->jobManager = new JobManager($this->logger);
    }

    public function testProcessUnsuccessful()
    {
        $message = new SqsMessage('{"type":"test","data":123}');
        $job = $this->jobManager->create($message);

        $this->assertInstanceOf(Job::class, $job);

        $processor = $this->createMock(JobProcessorInterface::class);
        $processor->expects($this->once())->method('process')->with($job)
            ->willReturnCallback(function () use ($job) {
                $job->setResult('something went wrong');
            });
        $this->jobManager->setProcessor('test', $processor);

        $result = $this->jobManager->process($job);
        $this->assertSame($job, $result);
        $this->assertGreaterThan(0, $job->getMaxRetryCount());
    }

    public function testProcessUnsuccessfulWithoutRetry()
    {
        $message = new SqsMessage('{"type":"test","data":123}');
        $job = $this->jobManager->create($message);

        $this->assertInstanceOf(Job::class, $job);

        $processor = $this->createMock(JobProcessorInterface::class);
        $processor->expects($this->once())->method('process')->with($job)
            ->willReturnCallback(function () use ($job) {
                $job->setResult('something went wrong');
            });
        $this->jobManager->setProcessor('test', $processor);

        $decider = $this->createMock(JobDeciderInterface::class);
        $decider->expects($this->once())->method('shouldRetry')->with($job)
            ->willReturn(false);
        $this->jobManager->setDecider('test', $decider);

        $result = $this->jobManager->process($job);
        $this->assertSame($job, $result);
        $this->assertEquals(0, $job->getMaxRetryCount());
    }

    public function testProcessWithoutProcessor()
    {
        $message = new SqsMessage('{"type":"test","data":123}');
        $job = $this->jobManager->create($message);

        $this->assertInstanceOf(Job::class, $job);

        $this->logger->expects($this->once())->method('error')
            ->with(
                'BrighteCapital\QueueClient\Job\JobManager::process: unknown type',
                ['message' => '{"type":"test","data":123}']
            );

        $this->jobManager->process($job);
        $this->assertTrue($job->getSuccess());
    }

    public function testProcessWithException()
    {
        $message = new SqsMessage('{"type":"test","data":123}');
        $job = $this->jobManager->create($message);

        $this->assertInstanceOf(Job::class, $job);

        $processor = $this->createMock(JobProcessorInterface::class);
        $processor->expects($this->once())->method('process')->with($job)
            ->willThrowException(new \Exception('something went wrong'));
        $this->jobManager->setProcessor('test', $processor);

        $this->logger->expects($this->once())->method('error')
            ->with(
                'BrighteCapital\QueueClient\Job\JobManager::process: error processing message',
                ['message' => '{"type":"test","data":123}']
            );

        $this->jobManager->process($job);
    }
}
