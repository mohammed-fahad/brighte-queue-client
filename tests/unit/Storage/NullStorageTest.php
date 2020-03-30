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

    public function testStore()
    {
        $this->assertEmpty($this->storage->store($this->messageEntity));
    }

    public function testUpdate()
    {
        $this->assertEmpty($this->storage->update($this->messageEntity));
    }

    public function testMessageExist()
    {
        $this->assertFalse($this->storage->messageExist($this->messageEntity));
    }
}
