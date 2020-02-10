<?php

namespace BrighteCapital\QueueClient\Storage;

use BrighteCapital\QueueClient\Utility\StringUtility;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;

class MySql implements StorageInterface
{
    /** @var Connection */
    protected $connection;

    /**
     * @param array $config
     * @throws DBALException
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
     * @param EntityInterface $entity
     * @throws Exception
     */
    public function store(EntityInterface $entity): void
    {
        $parameters = [];
        $data = $entity->toArray();
        $queryBuilder = $this->connection->createQueryBuilder();

        if (empty($data)) {
            throw new Exception('Data trying to insert in database in empty');
        }

        foreach ($data as $key => $value) {
            $key = StringUtility::camelCaseToSnakeCase($key);

            if ($key === 'id') {
                continue;
            }

            $queryBuilder->setValue($key, ':' . $key);
            $parameters[':' . $key] = $value;
        }

        $queryBuilder->insert($entity->getTableName())->setParameters($parameters)->execute();
    }

    /**
     * @param EntityInterface $entity
     * @throws Exception
     */
    public function update(EntityInterface $entity): void
    {
        if (empty($entity->getId())) {
            throw new Exception('Update action requires Id to be set');
        }

        $parameters = [];
        $data = $entity->toArray();

        if (empty($data)) {
            throw new Exception('Data trying to insert in database in empty');
        }
        $queryBuilder = $this->connection->createQueryBuilder();

        foreach ($data as $key => $value) {
            $key = StringUtility::camelCaseToSnakeCase($key);

            if ($key === 'id') {
                $parameters[':' . $key] = $value;
                continue;
            }

            $queryBuilder->set($key, ':' . $key);
            $parameters[':' . $key] = $value;
        }

        $queryBuilder->update($entity->getTableName())->where('id = :id')->setParameters($parameters)->execute();
    }

    /**
     * @param EntityInterface $entity
     * @return EntityInterface|bool
     */
    public function messageExist(EntityInterface $entity)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

         $result = $queryBuilder
             ->select('id', 'alert_count')
             ->from($entity->getTableName())
             ->where('message_id = :message_id')
             ->setParameter(':message_id', $entity->getMessageId())
             ->execute();

        if ($row = $result->fetch()) {
            $entity = new MessageEntity();
            return $entity->toEntity($row);
        }

        return false;
    }
}
