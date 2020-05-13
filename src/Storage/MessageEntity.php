<?php

namespace BrighteCapital\QueueClient\Storage;

use BrighteCapital\QueueClient\Utility\StringUtility;
use DateTime;
use Enqueue\Sqs\SqsMessage;
use Interop\Queue\Message;

class MessageEntity
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_EDITED = 'edited';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PROCESSED = 'processed';

    protected $messageId;
    protected $messageHandle;
    protected $groupId;
    protected $message;
    protected $originalMessage;
    protected $attributes;
    protected $alertCount = 1;
    protected $lastErrorMessage = '';
    protected $queueName = '';
    protected $status = self::STATUS_PENDING;
    protected $created;
    protected $modified;

    /**
     * MessageEntity constructor.
     * @param Message $message
     */
    public function __construct(Message $message = null)
    {
        if ($message === null) {
            return;
        }

        $this->messageId = $message->getMessageId() ?: '__' . uniqid();
        $this->messageHandle = ($message instanceof SqsMessage)
            ? $message->getReceiptHandle()
            : '';
        $this->groupId = $message->getProperty('MessageGroupId');
        $this->message = $message->getBody();
        $this->originalMessage = $this->message;
        $this->attributes = json_encode($message->getProperties());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $formattedData = [];

        $data = array_filter(get_object_vars($this), function ($value) {
            return !empty($value);
        });

        array_walk($data, function ($value, $key) use (&$formattedData) {
            if ($value instanceof DateTime) {
                $value = $value->format(DateTime::ISO8601);
            }

            $formattedData[StringUtility::camelCaseToSnakeCase($key)] = $value;
        });

        return $formattedData;
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
     * @return string
     */
    public function getOriginalMessage(): string
    {
        return $this->originalMessage ?? '';
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    public function getCreated(): ?DateTime
    {
        return is_string($this->created) ? new DateTime($this->created) : $this->created;
    }

    public function setModifed(DateTime $modifed): void
    {
        $this->modified = $modifed;
    }

    public function getModifed(): ?DateTime
    {
        return is_string($this->modified) ? new DateTime($this->modified) : $this->modified;
    }
}
