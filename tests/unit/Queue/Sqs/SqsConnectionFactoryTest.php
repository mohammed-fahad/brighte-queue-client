<?php

namespace App\Test\Queue\Sqs;

use App\Test\BaseTestCase;
use BrighteCapital\QueueClient\Queue\Sqs\SqsConnectionFactory;
use Enqueue\Sqs\SqsClient;

class SqsConnectionFactoryTest extends BaseTestCase
{
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $awsSqs = $this->createMock(\Aws\Sqs\SqsClient::class);
        $this->factory = new SqsConnectionFactory($awsSqs);
    }

    public function testConstruct()
    {
        $factory = new SqsConnectionFactory(null);
        $this->assertInstanceOf(SqsConnectionFactory::class, $factory);
        $factory = new SqsConnectionFactory('Sqs:schemeProtocol=Sqs');
        $this->assertInstanceOf(SqsConnectionFactory::class, $factory);
        $factory = new SqsConnectionFactory(['dsn' => 'Sqs:schemeProtocol=Sqs']);
        $this->assertInstanceOf(SqsConnectionFactory::class, $factory);
        try {
            new SqsConnectionFactory(1);
        } catch (\Exception $e) {
            $this->assertStringContainsString(
                'The config must be either an array of options, a DSN string, null or instance of',
                $e->getMessage()
            );
        }
    }

    public function testEstablishConnection()
    {
        $client = $this->invokeHiddenMethod($this->factory, 'establishConnection');
        $this->assertInstanceOf(SqsClient::class, $client);
    }

    public function testEstablishConnectionConfig()
    {
        $factory = new SqsConnectionFactory(
            ['endpoint' => 'test', 'key' => 'key', 'secret' => 'secret', 'token' => 'token', 'lazy' => false]
        );
        $client = $this->invokeHiddenMethod($factory, 'establishConnection');
        $this->assertInstanceOf(SqsClient::class, $client);
    }

    public function testParseDsn()
    {
        try {
            $this->invokeHiddenMethod($this->factory, 'parseDsn', ['testing:test']);
        } catch (\Exception $e) {
            $this->assertStringContainsStringIgnoringCase('The given scheme protocol', $e->getMessage());
        }
    }
}
