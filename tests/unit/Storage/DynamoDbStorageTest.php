<?php

namespace App\Test\Storage;

use App\Test\BaseTestCase;
use Aws\DynamoDb\DynamoDbClient;
use BrighteCapital\QueueClient\Storage\DynamoDbStorage;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use DateTime;
use Psr\Log\LoggerInterface;

class DynamoDbStorageTest extends BaseTestCase
{
    /** @var DynamoDbClient */
    protected $dynamodbClient;

    /** @var LoggerInterface */
    protected $logger;

    /** @var  DynamoDbStorage*/
    protected $storage;

    /** @var string */
    protected $tableName = 'test_table';

    protected function setUp()
    {
        $this->dynamodbClient = $this->createPartialMock(DynamoDbClient::class, [
            'putItem',
            'deleteItem',
            'query',
            'getItem',
        ]);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->storage = new DynamoDbStorage($this->tableName, $this->dynamodbClient, $this->logger);
    }

    public function testSave()
    {
        $data = [
            'message_id' => 'message-id-123',
            'message_handle' => 'abc-123',
            'group_id' => '123',
            'message' => 'this is a message',
            'original_message' => 'this is a message',
            'attributes' => '{"data": 123}',
            'queue_name' => 'test-queue',
            'status' => 'pending',
            'created' => '2020-05-12T03:42:13+0000',
        ];

        $payload = [
            'TableName' => $this->tableName,
            'Item' => [
                'message_id' => ['S' => 'message-id-123'],
                'message_handle' => ['S' => 'abc-123'],
                'group_id' => ['S' => '123'],
                'message' => ['S' => 'this is a message'],
                'original_message' => ['S' => 'this is a message'],
                'attributes' => ['S' => '{"data": 123}'],
                'status' => ['S' => 'pending'],
                'queue_name' => ['S' => 'test-queue'],
                'created' => ['S' => '2020-05-12T03:42:13+0000'],
                'modified' => ['S' => (new DateTime())->format(DateTime::ISO8601)],
                'alert_count' => ['N' => 1],
            ]
        ];

        $this->dynamodbClient
            ->expects($this->once())
            ->method('putItem')
            ->with($payload);

        $entity = (new MessageEntity())->patch($data);
        $this->storage->save($entity);
    }

    public function testDelete()
    {
        $payload = [
            'TableName' => $this->tableName,
            'Key' => [
                'message_id' => ['S' => 'message-id-123'],
            ]
        ];

        $this->dynamodbClient
            ->expects($this->once())
            ->method('deleteItem')
            ->with($payload);

        $this->storage->delete('message-id-123');
    }

    public function testFindByStatus()
    {
        $payload = [
            'TableName' => $this->tableName,
            'IndexName' => 'status-index',
            'KeyConditionExpression' => '#status = :status',
            'ExpressionAttributeNames' => [ '#status' => 'status' ],
            'ExpressionAttributeValues' => [
                ':status' => ['S' => 'pending'],
            ],
            'Limit' => 1,
        ];

        $items = [
            'Items' => [
                [
                    'message_id' => ['S' => 'message-id-123'],
                    'message_handle' => ['S' => 'abc-123'],
                    'group_id' => ['S' => '123'],
                    'message' => ['S' => 'this is a message'],
                    'original_message' => ['S' => 'this is a message'],
                    'attributes' => ['S' => '{"data": 123}'],
                    'status' => ['S' => 'pending'],
                    'queue_name' => ['S' => 'test-queue'],
                    'created' => ['S' => '2020-05-12T03:42:13+0000'],
                    'modified' => ['S' => '2020-05-12T03:42:13+0000'],
                    'alert_count' => ['N' => 1],
                ]
            ]
        ];

        $data = [
            'message_id' => 'message-id-123',
            'message_handle' => 'abc-123',
            'group_id' => '123',
            'message' => 'this is a message',
            'original_message' => 'this is a message',
            'attributes' => '{"data": 123}',
            'queue_name' => 'test-queue',
            'status' => 'pending',
            'alert_count' => 1,
            'created' => '2020-05-12T03:42:13+0000',
            'modified' => '2020-05-12T03:42:13+0000',
        ];

        $this->dynamodbClient
            ->expects($this->once())
            ->method('query')
            ->with($payload)
            ->willReturn($items);

        $messages = $this->storage->findByStatus(MessageEntity::STATUS_PENDING);
        $this->assertCount(1, $messages);
        $this->assertInstanceOf(MessageEntity::class, $messages[0]);
        $this->assertEquals($data, $messages[0]->toArray());
    }

    public function testFindByStatusNoResult()
    {
        $this->dynamodbClient
            ->expects($this->once())
            ->method('query')
            ->willReturn([]);

        $messages = $this->storage->findByStatus(MessageEntity::STATUS_PENDING);
        $this->assertCount(0, $messages);
    }

    public function testGet(): void
    {
        $this->dynamodbClient
            ->expects($this->once())
            ->method('getItem')
            ->with([
                'TableName' => $this->tableName,
                'Key' => [
                    'message_id' => ['S' => 'message-id-123']
                ],
            ])
            ->willReturn([
                'Item' => [
                    'message_id' => ['S' => 'message-id-123'],
                    'message' => ['S' => 'this is a message']
                ]
            ]);

        $entity = $this->storage->get('message-id-123');
        $this->assertNotNull($entity);
        $this->assertEquals([
            'message_id' => 'message-id-123',
            'message' => 'this is a message',
            'alert_count' => 1,
            'status' => 'pending',
        ], $entity->toArray());
    }
}
