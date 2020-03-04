<?php

namespace BrighteCapital\QueueClient\Storage;

interface MessageStorageInterface
{
    public function store(MessageEntity $entity): void;

    public function update(MessageEntity $entity): void;

    public function messageExist(MessageEntity $entity);
}
