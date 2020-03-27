<?php

namespace App\Test\Queue\Factories;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Notifications\Channels\NullNotificationChannel;
use BrighteCapital\QueueClient\Queue\Factories\BlockerHandlerFactory;
use BrighteCapital\QueueClient\Queue\Sqs\SqsBlockerHandler;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;
use BrighteCapital\QueueClient\Storage\NullStorage;
use Psr\Log\NullLogger;

class BlockerHandlerFactoryTest extends BaseTestCase
{

    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $client = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();
        $storage = new NullStorage();
        $logger = new NullLogger();
        $notification = new NullNotificationChannel();
        $this->factory = new BlockerHandlerFactory($client, $logger, $notification, $storage);
    }

    public function testCreate()
    {
        $this->assertInstanceOf(
            SqsBlockerHandler::class,
            $this->factory->create(['provider' => 'sqs', 'defaultMaxDelay' => 2])
        );
    }

    public function testCreateFailed()
    {
        try {
            $this->factory->create(['defaultMaxDelay' => 2]);
        } catch (\Exception $e) {
            $this->assertStringContainsString('Failed to create blocker handler', $e->getMessage());
        }
    }
}
