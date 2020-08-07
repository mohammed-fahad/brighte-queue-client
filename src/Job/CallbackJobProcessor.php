<?php

declare(strict_types=1);

namespace BrighteCapital\QueueClient\Job;

use BrighteCapital\QueueClient\Job\Job;

class CallbackJobProcessor implements JobProcessorInterface
{
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function process(Job $job): void
    {
        call_user_func($this->callback, $job);
    }
}
