<?php
namespace BrighteCapital\QueueClient\Storage;

use Interop\Queue\Message;

interface StorageInterface
{
    public function __construct(array $config);

    public function store(Message $message): void;

    public function update(Message $message): void;

    public function messageExist(Message $message): array;

}