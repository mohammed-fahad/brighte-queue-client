<?php

namespace BrighteCapital\QueueClient\Storage;

interface StorageInterface
{
    public function store(MessageEntity $entity): void;

    public function update(MessageEntity $entity): void;

    public function messageExist(MessageEntity $entity);

    public function createMessageTable(): void;

    public function messageTableExist(): bool;
}
