<?php

namespace BrighteCapital\QueueClient\notifications\Channels;

use GuzzleHttp\Client;

class SlackNotificationChannel implements NotificationChannelInterface
{
    public const DEFAULT_MAX_BODY_CHARS_TO_SEND = 100;
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;
    /**
     * @var string
     */
    private $url;

    private $maxBodyCharsToSend;

    /**
     * SlackNotificationChannel constructor.
     * @param string $url slack webHook url
     * @param int $maxBodyChars message body character limit
     * @param \GuzzleHttp\Client|null $client client
     */
    public function __construct(string $url, int $maxBodyChars, Client $client)
    {
        $this->url = $url;
        $this->maxBodyCharsToSend = $maxBodyChars;
        $this->client = $client;
    }

    /**
     * @param array $data
     * @return void
     * @throws \Exception
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
