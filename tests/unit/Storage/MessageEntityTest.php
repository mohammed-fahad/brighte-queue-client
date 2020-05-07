<?php

namespace App\Test\Storage;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use DateTime;
use Enqueue\Sqs\SqsMessage;

class MessageEntityTest extends BaseTestCase
{
    /** @var MessageEntity */
    protected $messageEntity;

    protected function setUp()
    {
        parent::setUp();
        $message = new SqsMessage('message body', ['type' => 'test']);
        $message->setReceiptHandle('receiptHandle');
        $message->setMessageId('messageId');
        $message->setProperty('MessageGroupId', 'MessageGroupId');
        $this->messageEntity = new MessageEntity($message);
    }

    public function testSetters()
    {
        $this->messageEntity->setMessageHandle('testHandle');
        $this->messageEntity->setQueueName('testQueue');
        $this->messageEntity->setAlertCount(1);
        $this->messageEntity->setLastErrorMessage('lastErrorMessage');
        $this->assertEquals('testHandle', $this->messageEntity->getMessageHandle());
        $this->assertEquals('testQueue', $this->messageEntity->getQueueName());
        $this->assertEquals('1', $this->messageEntity->getAlertCount());
        $this->assertEquals('lastErrorMessage', $this->messageEntity->getLastErrorMessage());
    }

    public function testToArray()
    {
        $data = $this->messageEntity->toArray();
        $this->assertEquals('messageId', $data['message_id']);
        $this->assertEquals('MessageGroupId', $data['group_id']);
        $this->assertEquals('message body', $data['message']);
        $this->assertEquals('{"type":"test","MessageGroupId":"MessageGroupId"}', $data['attributes']);
    }

    public function testPatch()
    {
        $this->messageEntity->patch([
            'message_id' => 'patchedMessageId',
            'group_id' => 'patchedGroupId',
            'attributes' => 'patchedAttributes',
            'message' => 'patchedMessage',
            'created' => '2020-05-12T03:42:13+0000'
        ]);
        $this->assertEquals('patchedMessageId', $this->messageEntity->getMessageId());
        $this->assertEquals('patchedGroupId', $this->messageEntity->getGroupId());
        $this->assertEquals('patchedAttributes', $this->messageEntity->getAttributes());
        $this->assertEquals('patchedMessage', $this->messageEntity->getMessage());
        $this->assertInstanceOf(DateTime::class, $this->messageEntity->getCreated());
        $this->assertEquals('2020-05-12T03:42:13+0000', $this->messageEntity->getCreated()->format(DateTime::ISO8601));
    }
}
