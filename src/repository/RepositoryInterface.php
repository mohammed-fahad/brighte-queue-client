<?php


namespace BrighteCapital\QueueClient\repository;


interface RepositoryInterface
{
    public function has($id): bool;

    public function update($data): bool;

    public function store($data): bool;

    public function delete($id): bool;
}
