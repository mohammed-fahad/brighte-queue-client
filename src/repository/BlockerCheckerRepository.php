<?php


namespace BrighteCapital\QueueClient\repository;


class BlockerCheckerRepository implements RepositoryInterface
{
    protected $entityManager;

    public function __construct($entityManager = null)
    {
        $this->entityManager = $entityManager;
    }

    public function has($id): bool
    {
        $this->entityManager->has
    }

    public function update($data): bool
    {
        // TODO: Implement update() method.
    }

    public function store($data): bool
    {
        // TODO: Implement store() method.
    }

    public function delete($id): bool
    {
        // TODO: Implement delete() method.
    }
}
