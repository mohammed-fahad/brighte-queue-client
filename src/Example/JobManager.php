<?php

namespace BrighteCapital\QueueClient\Example;

use BrighteCapital\QueueClient\Job\Job;
use BrighteCapital\QueueClient\Job\JobManagerInterface;
use BrighteCapital\QueueClient\Strategies\BlockerStorageRetryStrategy;
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
        $job = new Job($message, 5, 5, BlockerStorageRetryStrategy::class);

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
