<?php

declare(strict_types=1);

namespace BrighteCapital\QueueClient\Job;

use BrighteCapital\QueueClient\Job\Job;

interface JobDeciderInterface
{
    public function shouldRetry(Job $job): bool;
}
