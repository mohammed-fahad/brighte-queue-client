<?php

namespace BrighteCapital\QueueClient\Storage;

interface MessageStorageInterface
{
    public function get(string $id): ?MessageEntity;

    public function save(MessageEntity $entity): void;

    public function delete(string $id): void;

    public function findByStatus(string $status, int $limit = 1): array;

    public function migrateTable(array $schema = []): void;
}
