<?php

namespace BrighteCapital\QueueClient\Example;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Job\JobManagerInterface;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
use BrighteCapital\QueueClient\Strategies\Retry;
use Interop\Queue\Message;

class JobManager implements JobManagerInterface
{
    /**
     * Create Job and assign retry to it.
     * @param Message $message
     * @return Job
     */
    public function create(Message $message): Job
    {
        /** @var \Interop\Queue\Message $message*/
        $retry = new Retry(5, 5, BlockerStorageRetryStrategy::class);
        $job = new Job($message, $retry);
        $job->setRetry($retry);
        return $job;
    }

    /**
     * Process jobs based on type.
     * @param Job $job
     * @return Job
     * @throws \Exception
     */
    public function process(Job $job): Job
    {
        $message = $job->getMessage();
        $type = $message->getProperty('type');
        switch ($type) {
            case 'failed':
                $result = false;
                break;
            case 'success':
                $result = true;
                break;
            default:
                $result = true;
                break;
        }
        $job->setSuccess(!empty($result));

        return $job;
    }
}
