<?php

namespace BrighteCapital\QueueClient\Notifications\Channels;

use GuzzleHttp\Client;

class SlackNotificationChannel implements NotificationChannelInterface
{
    public const DEFAULT_MAX_BODY_CHARS_TO_SEND = 100;
    /** @var \GuzzleHttp\ClientInterface */
    private $client;
    /** @var string */
    private $url;
    /** @var int */
    private $maxBodyCharsToSend;

    /**
     * SlackNotificationChannel constructor.
     * @param string $url slack webHook url
     * @param int $maxBodyChars message body character limit
     * @param \GuzzleHttp\Client $client client
     */
    public function __construct(string $url, int $maxBodyChars, Client $client)
    {
        $this->url = $url;
        $this->maxBodyCharsToSend = $maxBodyChars;
        $this->client = $client;
    }

    /**
     * @param array $data data
     * @return void
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(array $data): void
    {
        $response = $this->client->post($this->url, $this->createMessage($data));

        if ($response->getStatusCode() !== 200) {
            throw new \Exception(
                sprintf(
                    "Failed to send Slack message. status code = %s body = %s",
                    $response->getStatusCode(),
                    (string)$response->getBody()
                )
            );
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function createMessage(array $data): array
    {
        $text = '';
        foreach ($data as $key => $value) {
            $newKey = strtolower($key);
            if ($newKey == 'body') {
                $value = substr($value, 0, $this->getMaxBodyCharsToSend());
            }
            $text .= $key . ': ' . $value . PHP_EOL;
        }

        return [
            'json' => [
                'text' => $text,
            ]
        ];
    }

    /**
     * @return int
     */
    public function getMaxBodyCharsToSend(): int
    {
        return $this->maxBodyCharsToSend;
    }

    /**
     * @param int $maxBodyCharsToSend
     */
    public function setMaxBodyCharsToSend(int $maxBodyCharsToSend): void
    {
        $this->maxBodyCharsToSend = $maxBodyCharsToSend;
    }
}
