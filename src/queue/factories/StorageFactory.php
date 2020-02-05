<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\Storage\MySql;
use BrighteCapital\QueueClient\Storage\StorageInterface;

class StorageFactory
{
    const TYPE_MYSQL = 'MySql';
    const TYPE_DYNAMODB  = 'DynamoDB';

    /**
     * @param array $config
     * @return StorageInterface
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public static function create(array $config): StorageInterface
    {
        $provider = $config['Database']['type'] ?? "undefined";

        switch ($provider) {
            case self::TYPE_MYSQL:
                $db = new MySql();
                $db->init($config['database']);
                return $db;
        }

        throw new \Exception(sprintf("Failed to create Storage %s", $provider));
    }
}
