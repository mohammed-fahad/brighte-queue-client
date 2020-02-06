<?php

namespace BrighteCapital\QueueClient\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Interop\Queue\Message;

class MySql implements StorageInterface
{
    /** @var Connection */
    protected $connection;

    /** @var Query */
    protected $queryBuilder;

    /**
     * @param array $config
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(array $config)
    {
        $connectionParams = [
            'dbname' => $config['dbname'],
            'user' => $config['user'],
            'password' => $config['password'],
            'host' => $config['host'],
            'driver' => 'pdo_mysql'
        ];
        $this->connection = DriverManager::getConnection($connectionParams);
        $this->connection->connect();
    }

    /**
     * @param Message $message
     * @throws \Doctrine\DBAL\DBALException
     */
    public function store(Message $message): void
    {
        $this->connection->insert('brighte_queue_messages', [
            'message_id' => $message->getMessageId(),
            'message_handle' => $message->getReceiptHandle(),
            'group_id' => $message->getProperty('MessageGroupId'),
            'message' => $message->getBody(),
            'attributes' => json_encode($message->getProperties()),
            'alert_count' => 1,
            'last_error_message' => 'errorMessage',
        ]);
    }

    public function update(Message $message): void
    {
        // TODO: Implement update() method.
    }

    public function messageExist(Message $message): array
    {
        $this->connection->select('id', 'alert_count')->where('email')->setParamter(0, $message->getMessageId());;
        // TODO: Implement messageExist() method.
    }
}