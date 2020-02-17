<?php

namespace BrighteCapital\QueueClient\queue;

interface BlockerHandlerInterface
{
    public function checkAndHandle(Job $job): bool;
}
