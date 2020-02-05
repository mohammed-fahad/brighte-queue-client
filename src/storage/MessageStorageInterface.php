<?php

namespace BrighteCapital\QueueClient\storage;

interface MessageStorageInterface
{
    public function store($data): void;

    public function update($data): void;
}
