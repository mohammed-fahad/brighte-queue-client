<?php

namespace BrighteCapital\QueueClient\Storage;

use BrighteCapital\QueueClient\Utility\StringUtility;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Table;
use Exception;

class MySql implements MessageStorageInterface
{
    /** @var Connection */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->connect();
    }

    public function messageTableExist(): bool
    {
        $scm = $this->connection->getSchemaManager();

        return $scm->tablesExist([MessageEntity::TABLE]);
    }

    /**
     * Check if table exist or create one
     * @throws DBALException
     */
    public function createMessageTable(): void
    {
        $scm = $this->connection->getSchemaManager();

        $table = new Table(MessageEntity::TABLE);
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

    /**
     * @param MessageEntity $entity
     * @throws Exception
     */
    public function store(MessageEntity $entity): void
    {
        $parameters = [];
        $data = $entity->toArray();
        $queryBuilder = $this->connection->createQueryBuilder();

        if (empty($data)) {
            throw new Exception('Data trying to insert in database is empty');
        }

        foreach ($data as $key => $value) {
            $key = StringUtility::camelCaseToSnakeCase($key);

            if ($key === 'id') {
                continue;
            }

            $queryBuilder->setValue($key, ':' . $key);
            $parameters[':' . $key] = $value;
        }

        $queryBuilder->insert(MessageEntity::TABLE)->setParameters($parameters)->execute();
    }

    /**
     * @param MessageEntity $entity
     * @throws Exception
     */
    public function update(MessageEntity $entity): void
    {
        if (empty($entity->getId())) {
            throw new Exception('Update action requires Id to be set');
        }

        $parameters = [];
        $data = $entity->toArray();

        if (empty($data)) {
            throw new Exception('Data trying to insert in database is empty');
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

        $queryBuilder->update(MessageEntity::TABLE)->where('id = :id')->setParameters($parameters)->execute();
    }

    /**
     * @param MessageEntity $entity
     * @return MessageEntity|bool
     */
    public function messageExist(MessageEntity $entity)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $result = $queryBuilder
            ->select('id', 'alert_count')
            ->from(MessageEntity::TABLE)
            ->where('message_id = :message_id')
            ->setParameter(':message_id', $entity->getMessageId())
            ->execute();

        if ($row = $result->fetch()) {
            $entity = new MessageEntity();

            return $entity->patch($row);
        }

        return false;
    }
}
