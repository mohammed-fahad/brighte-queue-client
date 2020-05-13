<?php

namespace BrighteCapital\QueueClient\Storage;

use BrighteCapital\QueueClient\Storage\MessageEntity;

/**
 * This Logger can be used to avoid conditional storage calls.
 *
 * Storage is optional, and if no storage is provided to your library creating a NullStorage
 * instance to have something to store at. It is a good way to avoid
 * littering your code with `if ($this->storage) { } blocks.
 */
class NullStorage implements MessageStorageInterface
{
    public function get(string $id): ?MessageEntity
    {
        return null;
    }

    public function save(MessageEntity $entity): void
    {
        // Do Nothing
    }

    public function delete(string $messageId): void
    {
        // Do Nothing
    }

    public function findByStatus(string $status, int $limit = 1): array
    {
        return [];
    }
}
