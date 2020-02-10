<?php

namespace BrighteCapital\QueueClient\Storage;

interface EntityInterface
{
    public function toArray(): array;
    public function toEntity(array $data): EntityInterface;
    public function getTableName(): string;
    public function getId(): int;
    public function setId(int $id): void;
    public function getMessageId(): string;
}
