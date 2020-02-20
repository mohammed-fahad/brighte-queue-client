<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\Storage\MySql;
use BrighteCapital\QueueClient\Storage\MessageStorageInterface;
use Doctrine\DBAL\DriverManager;

class StorageFactory
{
    public const ERROR_MISSING_CONFIG_KEY = 'Storage missing key %s';
    public const TYPE_MYSQL = 'MySql';
    public const TYPE_DYNAMODB  = 'DynamoDB';

    /**
     * @param array $config
     * @return MessageStorageInterface
     * @throws \Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function create(array $config): MessageStorageInterface
    {
        if (empty($config($config['provider']))) {
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, "storage.provider"));
        }

        $provider = $config['provider'];

        if ($provider instanceof MessageStorageInterface) {
            return $config['provider'];
        }

        switch ($provider) {
            case self::TYPE_MYSQL:
                return self::getMySqlConnection($config);
        }

        throw new \Exception(sprintf('Failed to create Storage: %s', $provider));
    }

    /**
     * @param $config
     * @return MySql
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    private static function getMySqlConnection($config)
    {

        if (empty($config['dbname'])) {
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, 'storage.dbname'));
        }

        if (empty($config['user'])) {
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, 'storage.user'));
        }

        if (empty($config['password'])) {
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, "storage.password"));
        }

        if (empty($config['host'])) {
            throw new \Exception(sprintf(self::ERROR_MISSING_CONFIG_KEY, "storage.host"));
        }

        $connectionParams = [
            'dbname' => $config['dbname'],
            'user' => $config['user'],
            'password' => $config['password'],
            'host' => $config['host'],
            'driver' => 'pdo_mysql'
        ];
        $connection = DriverManager::getConnection($connectionParams);

        return new MySql($connection);
    }
}
