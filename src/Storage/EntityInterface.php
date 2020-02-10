<?php
namespace BrighteCapital\QueueClient\Storage;

class MessageEntity {

    protected $tableName = 'brighte_queue_messages';
    protected $id;
    protected $messageId;
    protected $messageHandle;
    protected $groupId;
    protected $message;
    protected $attributes;
    protected $alertCount;
    protected $lastErrorMessage;

    public function __construct($data)
    {
        $this->toMessageEntity($data);
    }

    public function toMessageEntity(array $data): MessageEntity
    {
        foreach ($data as $key => $value) {
            $property = $this->convertSnakeToCamel($key);
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), function($value) {
            return !empty($value);
        });
    }

    private function convertSnakeToCamel(String $str): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @param mixed $messageId
     */
    public function setMessageId($messageId): void
    {
        $this->messageId = $messageId;
    }

    /**
     * @return mixed
     */
    public function getMessageHandle()
    {
        return $this->messageHandle;
    }

    /**
     * @param mixed $messageHandle
     */
    public function setMessageHandle($messageHandle): void
    {
        $this->messageHandle = $messageHandle;
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param mixed $groupId
     */
    public function setGroupId($groupId): void
    {
        $this->groupId = $groupId;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes($attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return mixed
     */
    public function getAlertCount()
    {
        return $this->alertCount;
    }

    /**
     * @param mixed $alertCount
     */
    public function setAlertCount($alertCount): void
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
    public function setLastErrorMessage($lastErrorMessage): void
    {
        $this->lastErrorMessage = $lastErrorMessage;
    }
}