<?php

namespace BrighteCapital\QueueClient\Notifications\Channels;

use GuzzleHttp\Client;

class SlackNotificationChannel implements NotificationChannelInterface
{
    public const DEFAULT_MAX_BODY_CHARS_TO_SEND = 200;
    /** @var \GuzzleHttp\ClientInterface */
    private $client;
    /** @var string */
    private $url;
    /** @var int */
    private $maxBodyCharsToSend;

    /**
     * SlackNotificationChannel constructor.
     * @param string $url slack webHook url
     * @param \GuzzleHttp\Client $client client
     * @param int $maxBodyChars message body character limit
     */
    public function __construct(
        string $url,
        Client $client = null,
        int $maxBodyChars = self::DEFAULT_MAX_BODY_CHARS_TO_SEND
    ) {
        $this->url = $url;
        $this->client = $client ?? new Client();
        $this->maxBodyCharsToSend = $maxBodyChars;
    }

    /**
     * @param array $data data
     * @return void
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(array $data): void
    {
        $message = $this->createMessage($data);
        $response = $this->postMessage($message);

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
     * @param array $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function postMessage(array $message)
    {
        return $this->client->post($this->url, $this->createMessage($message));
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
