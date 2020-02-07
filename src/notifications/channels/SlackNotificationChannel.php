<?php

namespace BrighteCapital\QueueClient\notifications\Channels;

use BrighteCapital\QueueClient\notifications\messages\NotificationMessageInterface;
use GuzzleHttp\Client;
use Interop\Queue\Message;

class SlackNotificationChannel implements NotificationChannelInterface
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;
    /**
     * @var string
     */
    private $slackEndpoint;

    public function __construct(string $slackEndpoint, Client $client = null)
    {
        $this->client = $client;

        if (is_null($client)) {
            $this->client = new Client();
        }

        $this->slackEndpoint = $slackEndpoint;
    }

    /**
     * @param \Interop\Queue\Message $message message
     * @return bool
     * @throws \Exception
     */
    public function send(Message $message): bool
    {
        $response = $this->client->post($this->slackEndpoint, $this->createMessage($message));

        if ($response->getStatusCode() != 200) {
            throw new \Exception("Failed to send slack message. Message Body = " . $message->getBody());
        }

        return true;
    }

    private function createMessage(Message $message): array
    {
        return [
            'json' => [
                'text' => $message->getBody(),
            ]
        ];
    }
}
