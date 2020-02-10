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
     * @throws \Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function create(array $config): StorageInterface
    {

        $provider = $config['provider'];

        if (empty($provider)) {
            throw new \Exception(sprintf('Failed to create Storage'));
        }

        if ($provider instanceof StorageInterface) {
            return $config['provider'];
        }

        switch ($provider) {
            case self::TYPE_MYSQL:
                return new MySql($config);
        }

        throw new \Exception(sprintf('Failed to create Storage: %s', $provider));
    }
}
