<?php

namespace BrighteCapital\QueueClient\Storage;

use BrighteCapital\QueueClient\Utility\StringUtility;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Table;
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
     * Check if table exist or create one
     * @throws DBALException
     */
    public function checkAndCreateMessageTable(): void
    {
        $entity = new MessageEntity();
        $scm = $this->connection->getSchemaManager();

        if (!$scm->tablesExist($entity->getTableName())) {
            //TODO: Log table created.

            $table = new Table($entity->getTableName());
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('message_id', 'string', ['customSchemaOptions' => ['unique' => true]]);
            $table->addColumn('group_id', 'string', []);
            $table->addColumn('message', 'text', []);
            $table->addColumn('attributes', 'text', ['notnull' => false]);
            $table->addColumn('alert_count', 'integer', ['notnull' => false]);
            $table->addColumn('last_error_message', 'text', ['notnull' => false]);
            $table->addColumn('message_handle', 'text', ['notnull' => false]);
            $table->addColumn('queue_name', 'text');
            $table->setPrimaryKey(['id']);

            $scm->createTable($table);
        }
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
