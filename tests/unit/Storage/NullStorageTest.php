<?php

namespace App\Test\Storage;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Storage\MessageEntity;
use BrighteCapital\QueueClient\Storage\NullStorage;

class NullStorageTest extends BaseTestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $messageEntity;
    /** @var  NullStorage*/
    protected $storage;

    protected function setUp()
    {
        parent::setUp();
        $this->messageEntity = $this->getMockBuilder(MessageEntity::class)->getMock();
        $this->storage = new NullStorage();
    }

    public function testSave()
    {
        $this->assertEmpty($this->storage->save($this->messageEntity));
    }

    public function testGet()
    {
        $this->assertNull($this->storage->get('123'));
    }

    public function testDelete()
    {
        $this->assertEmpty($this->storage->delete('123'));
    }

    public function testFindByStatus()
    {
        $this->assertEmpty($this->storage->findByStatus(MessageEntity::STATUS_EDITED));
    }
}
