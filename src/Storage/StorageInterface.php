<?php
namespace BrighteCapital\QueueClient\Storage;

use Interop\Queue\Message;

interface StorageInterface
{
    public function _construct(array $config);

    public function storeMessage(Message $message): bool;

    public function updateMessage(Message $message): bool;
}