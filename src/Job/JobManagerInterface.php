<?php

namespace BrighteCapital\QueueClient\Job;

use Interop\Queue\Message;

interface JobManagerInterface
{
    public function create(Message $message): Job;
    public function process(Job $job): Job;
}
