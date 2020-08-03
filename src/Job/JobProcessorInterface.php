<?php

declare(strict_types=1);

namespace BrighteCapital\QueueClient\Job;

use BrighteCapital\QueueClient\Job\Job;

interface JobProcessorInterface
{
    public function process(Job $job): void;
}
