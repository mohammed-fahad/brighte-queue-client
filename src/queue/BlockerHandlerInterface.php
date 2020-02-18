<?php

namespace BrighteCapital\QueueClient\queue;

use BrighteCapital\QueueClient\Job\Job;

interface BlockerHandlerInterface
{
    public function checkAndHandle(Job $job): bool;
}
