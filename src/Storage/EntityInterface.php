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
//    public function setMessageId(string $messageId): void;
//    public function getMessageHandle(): string;
//    public function setMessageHandle(string $messageHandle): void;
//    public function getGroupId(): string;
//    public function setGroupId(string $groupId): void;
//    public function getMessage();
//    public function setMessage(string $message): void;
//    public function getAttributes(): string;
//    public function setAttributes(string $attributes): void;
//    public function getAlertCount(): string;
//    public function setAlertCount($alertCount): void;
//    public function getLastErrorMessage();
//    public function setLastErrorMessage($lastErrorMessage): void;
}