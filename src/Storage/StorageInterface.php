<?php
namespace BrighteCapital\QueueClient\Storage;

use Interop\Queue\Message;

interface StorageInterface
{
    public function init(array $config): void;

    public function storeMessage(Message $message): bool;

    public function updateMessage(Message $message): bool;
}