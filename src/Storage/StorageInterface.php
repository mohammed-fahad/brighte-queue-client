<?php
namespace BrighteCapital\QueueClient\Storage;

interface StorageInterface
{
    public function __construct(array $config);

    public function store(EntityInterface $message): void;

    public function update(EntityInterface $message): void;

    public function messageExist(EntityInterface $message);
}