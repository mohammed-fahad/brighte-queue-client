<?php

namespace BrighteCapital\QueueClient\Storage;

use BrighteCapital\QueueClient\Utility\StringUtility;
use Interop\Queue\Message;

class MessageEntity
{

    public const TABLE = 'brighte_queue_messages';

    protected $id;
    protected $messageId;
    protected $messageHandle;
    protected $groupId;
    protected $message;
    protected $attributes;
    protected $alertCount = 1;
    protected $lastErrorMessage = '';
    protected $queueName = '';

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
        $this->attributes = json_encode($message->getAttributes());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), function ($value) {
            return !empty($value);
        });
    }

    /**
     * @param array $data
     * @return MessageEntity
     */
    public function patch(array $data): MessageEntity
    {
        foreach ($data as $key => $value) {
            $key = StringUtility::snakeCaseToCamelCase($key);
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return mixed
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getAttributes(): string
    {
        return $this->attributes;
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
