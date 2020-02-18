<?php

namespace BrighteCapital\QueueClient\queue\factories;

use BrighteCapital\QueueClient\Storage\MySql;
use BrighteCapital\QueueClient\Storage\StorageInterface;
use Doctrine\DBAL\DriverManager;

class StorageFactory
{
    public const TYPE_MYSQL = 'MySql';
    public const TYPE_DYNAMODB  = 'DynamoDB';

    /**
     * @param array $config
     * @return StorageInterface
     * @throws \Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function create(array $config): StorageInterface
    {
        $provider = $config['provider'];

        $factory = new StorageFactory();

        if (empty($provider)) {
            throw new \Exception(sprintf('Failed to create Storage'));
        }

        if (!$factory->configValidated($config)) {
            throw new \Exception('Provide Database configuration');
        }

        if ($provider instanceof StorageInterface) {
            return $config['provider'];
        }

        $connectionParams = [
            'dbname' => $config['dbname'],
            'user' => $config['user'],
            'password' => $config['password'],
            'host' => $config['host'],
        ];

        switch ($provider) {
            case self::TYPE_MYSQL:
                $connectionParams['driver'] = 'pdo_mysql';
                $connection = DriverManager::getConnection($connectionParams);
                return new MySql($connection);
        }

        throw new \Exception(sprintf('Failed to create Storage: %s', $provider));
    }

    private function configValidated($config)
    {
        return (!empty($config['dbname']) && !empty($config['user']) && !empty($config['password']) && !empty($config['host']));
    }
}
