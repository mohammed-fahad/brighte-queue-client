<?php

namespace App\Test\Queue\Factories;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Queue\Factories\QueueClientFactory;
use BrighteCapital\QueueClient\Queue\Sqs\SqsClient;

class QueueClientFactoryTest extends BaseTestCase
{

    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->factory = new QueueClientFactory();
    }

    public function testCreate()
    {
        $this->assertInstanceOf(SqsClient::class, $this->factory->create(['provider' => 'sqs', 'queue' => 'queue']));
    }

    public function testCreateFailed()
    {
        try {
            $this->assertInstanceOf(
                SqsClient::class,
                $this->factory->create(['provider' => 'test', 'queue' => 'queue'])
            );
        } catch (\Exception $e) {
            $this->assertStringContainsString('Failed to create Queue Client test', $e->getMessage());
        }
    }

    public function testCreateQueueMissing()
    {
        try {
            $this->assertInstanceOf(SqsClient::class, $this->factory->create(['provider' => 'test']));
        } catch (\Exception $e) {
            $this->assertEquals('Please provide Queue name', $e->getMessage());
        }
    }
}
