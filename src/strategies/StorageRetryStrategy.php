<?php

namespace BrighteCapital\QueueClient\strategies;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Interop\Queue\Message;

class StorageRetryStrategy extends AbstractRetryStrategy
{
    /** @var Connection */
    protected $connection;

    /**
     * @param $data
     * @throws \Doctrine\DBAL\DBALException
     */
    public function _construct($data)
    {
        parent::_construct($data);
        $connectionParams = [
            'dbname' => $data['config']['dbname'],
            'user' =>  $data['config']['user'],
            'password' =>  $data['config']['password'],
            'host' =>  $data['config']['host'],
            'driver' => 'pdo_mysql',
        ];

        $this->connection = DriverManager::getConnection($connectionParams);
    }

    function onMaxRetryReached(Message $message): void
    {

    }
}
