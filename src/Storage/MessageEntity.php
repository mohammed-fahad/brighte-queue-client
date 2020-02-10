<?php

namespace BrighteCapital\QueueClient\Storage;

use BrighteCapital\QueueClient\Utility\StringUtility;
use Interop\Queue\Message;

class MessageEntity implements EntityInterface
{

    protected $tableName = 'brighte_queue_messages';
    protected $id;
    protected $messageId;
    protected $messageHandle;
    protected $groupId;
    protected $message;
    protected $attributes;
    protected $alertCount = 1;
    protected $lastErrorMessage = '';
    protected $queueName = '';
    protected $databaseAttributes = ['id', 'messageId', 'messageHandle', 'groupId', 'message', 'attributes',
        'alertCount', 'lastErrorMessage', 'queueName'] ;

    /**
     * MessageEntity constructor.
     * @param Message $message
     */
    public function __construct(Message $message = null)
    {
        if ($message === null) {
            return;
        }

        $this->messageId = $message->getMessageId();
        $this->messageHandle = $message->getReceiptHandle();
        $this->groupId = $message->getProperty('MessageGroupId');
        $this->message = $message->getBody();
        $this->attributes = json_encode($message->getProperties());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), function ($value, $key) {
            return !empty($value) && in_array($key, $this->databaseAttributes);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param array $data
     * @return EntityInterface
     */
    public function toEntity(array $data): EntityInterface
    {
        foreach ($data as $key => $value) {
            $key = StringUtility::snakeCaseToCamelCase($key);
            if (property_exists($this, $key) and !in_array($key, $this->notDatabaseAttributes)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * @param string $queueName
     */
    public function setQueueName(string $queueName): void
    {
        $this->queueName = $queueName;
    }

    /**
     * @return mixed
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @param mixed $messageId
     */
    public function setMessageId(string $messageId): void
    {
        $this->messageId = $messageId;
    }

    /**
     * @return mixed
     */
    public function getMessageHandle(): string
    {
        return $this->messageHandle;
    }

    /**
     * @param mixed $messageHandle
     */
    public function setMessageHandle(string $messageHandle): void
    {
        $this->messageHandle = $messageHandle;
    }

    /**
     * @return mixed
     */
    public function getGroupId(): string
    {
        return $this->groupId;
    }

    /**
     * @param mixed $groupId
     */
    public function setGroupId(string $groupId): void
    {
        $this->groupId = $groupId;
    }

    /**
     * @return mixed
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getAttributes(): string
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes(string $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return mixed
     */
    public function getAlertCount(): string
    {
        return $this->alertCount;
    }

    /**
     * @param mixed $alertCount
     */
    public function setAlertCount(string $alertCount): void
    {
        $this->alertCount = $alertCount;
    }

    /**
     * @return mixed
     */
    public function getLastErrorMessage()
    {
        return $this->lastErrorMessage;
    }

    /**
     * @param mixed $lastErrorMessage
     */
    public function setLastErrorMessage(string $lastErrorMessage): void
    {
        $this->lastErrorMessage = $lastErrorMessage;
    }
}
