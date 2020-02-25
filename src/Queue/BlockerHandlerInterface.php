<?php

namespace BrighteCapital\QueueClient\Queue;

use BrighteCapital\QueueClient\Job\Job;

interface BlockerHandlerInterface
{
    public function checkAndHandle(Job $job): bool;
}
