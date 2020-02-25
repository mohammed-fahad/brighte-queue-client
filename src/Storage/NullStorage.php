<?php

namespace BrighteCapital\QueueClient\Storage;

/**
 * This Logger can be used to avoid conditional storage calls.
 *
 * Storage is optional, and if no storage is provided to your library creating a NullStorage
 * instance to have something to store at. It is a good way to avoid
 * littering your code with `if ($this->storage) { } blocks.
 */
class NullStorage implements MessageStorageInterface
{
    public function store(MessageEntity $entity): void
    {
        // Do Nothing
    }

    public function update(MessageEntity $entity): void
    {
        //Do Nothing
    }

    public function messageExist(MessageEntity $entity)
    {
        return false;
    }
}
