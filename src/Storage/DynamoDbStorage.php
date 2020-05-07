<?php

namespace BrighteCapital\QueueClient\Storage;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use DateTime;
use Psr\Log\LoggerInterface;

class DynamoDbStorage implements MessageStorageInterface
{
    public const KEY_NAME = 'message_id';

    /** @var DynamoDbClient */
    protected $dynamodb;

    /** @var string */
    protected $tableName;

    /** @var Marshaler */
    protected $marshaler;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param string $tableName
     * @param \Aws\DynamoDb\DynamoDbClient $dynamodb
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(string $tableName, DynamoDbClient $dynamodb, LoggerInterface $logger)
    {
        $this->tableName = $tableName;
        $this->dynamodb = $dynamodb;
        $this->logger = $logger;
        $this->marshaler = new Marshaler();
    }

    /**
     * @param \BrighteCapital\QueueClient\Storage\MessageEntity $entity
     * @return void
     */
    public function save(MessageEntity $entity): void
    {
        $entity->setModifed(new DateTime());
        if (!$entity->getCreated()) {
            $entity->setCreated(new DateTime());
        }

        $data = $entity->toArray();

        $item = $this->marshaler->marshalItem($data);

        $result = $this->dynamodb->putItem([
            'TableName' => $this->tableName,
            'Item' => $item
        ]);
    }

    /**
     * @param string $id
     * @return \BrighteCapital\QueueClient\Storage\MessageEntity|null
     */
    public function get(string $id): ?MessageEntity
    {
        $params = [
            'TableName' => $this->tableName,
            'Key' => $this->marshaler->marshalItem([
                self::KEY_NAME => $id,
            ]),
        ];

        $result = $this->dynamodb->getItem($params);

        if (!isset($result['Item'][self::KEY_NAME])) {
            return null;
        }

        $data = $this->marshaler->unmarshalItem($result['Item']);
        return (new MessageEntity())->patch($data);
    }

    /**
     * @param string $id
     * @return void
     */
    public function delete(string $id): void
    {
        $params = [
            'TableName' => $this->tableName,
            'Key' => $this->marshaler->marshalItem([
                self::KEY_NAME => $id,
            ]),
        ];

        $this->dynamodb->deleteItem($params);
    }

    /**
     * @param string $status
     * @param integer $limit
     * @return array
     */
    public function findByStatus(string $status, int $limit = 1): array
    {
        $params = [
            'TableName' => $this->tableName,
            'IndexName' => 'status-index',
            'KeyConditionExpression' => '#status = :status',
            'ExpressionAttributeNames' => [ '#status' => 'status' ],
            'ExpressionAttributeValues' => $this->marshaler->marshalItem([
                ':status' => $status,
            ]),
            'Limit' => $limit,
        ];

        $result = $this->dynamodb->query($params);
        $items = $result['Items'] ?? [];

        if (!$items) {
            return [];
        }

        return array_map(function ($item) {
            $data = $this->marshaler->unmarshalItem($item);
            return (new MessageEntity())->patch($data);
        }, $items);
    }
}
