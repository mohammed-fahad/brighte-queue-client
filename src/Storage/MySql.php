<?php

namespace BrighteCapital\QueueClient\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Interop\Queue\Message;

class MySql implements StorageInterface
{
    /** @var Connection */
    protected $connection;

    /**
     * @param array $config
     * @throws \Doctrine\DBAL\DBALException
     */
    public function init(array $config): void
    {
        $connectionParams = [
            'dbname' => $config['dbname'],
            'user' => $config['user'],
            'password' => $config['password'],
            'host' => $config['host'],
            'driver' => 'pdo_mysql'
        ];
        $this->connection = DriverManager::getConnection($connectionParams);
    }

    public function storeMessage(Message $message): bool
    {
        // TODO: Implement storeMessage() method.
    }

    public function updateMessage(Message $message): bool
    {
        // TODO: Implement updateMessage() method.
    }
}