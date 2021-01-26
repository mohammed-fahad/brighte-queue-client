<?php

namespace App\Test\Notifications\Channels;

use BrighteCapital\QueueClient\Notifications\Channels\SlackNotificationChannel;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class SlackNotificationChannelTest extends TestCase
{
    protected $notification;
    protected $client;

    /**
     * @throws \ReflectionException
     */
    protected function setUp()
    {
        parent::setUp();
        $this->client = $this->getMockBuilder(Client::class)->getMock();
        $this->notification = new SlackNotificationChannel('test', $this->client);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSend()
    {
        $notification = $this->createPartialMock(SlackNotificationChannel::class, ['postMessage']);

        $response = new Response(200, [], 'test');
        $notification->expects($this->once())->method('postMessage')->willReturn($response);
        $notification->send(['test']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSendFail()
    {
        $notification = $this->createPartialMock(SlackNotificationChannel::class, ['postMessage']);

        $response = new Response(401, [], 'test');
        $notification->expects($this->once())->method('postMessage')->willReturn($response);
        try {
            $notification->send(['test']);
        } catch (\Exception $e) {
            $this->assertEquals(
                'Failed to send Slack message. status code = 401 body = test',
                $e->getMessage()
            );
        }
    }

    public function testCreateMessage()
    {
        $message = $this->notification->createMessage(['body' => 'testBody']);
        $expected = [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => "*body*:\n```testBody```",
            ],
        ];
        $this->assertStringContainsString(json_encode($expected), json_encode($message));
    }

    public function testPostMessage()
    {
        $response = new Response(200, [], 'test');
        $this->client->expects($this->once())->method('__call')->willReturn($response);
        $response = $this->notification->postMessage(['test' => 'test']);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
