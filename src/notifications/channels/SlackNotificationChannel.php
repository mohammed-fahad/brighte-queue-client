<?php

namespace BrighteCapital\QueueClient\notifications\Channels;

use GuzzleHttp\Client;

class SlackNotificationChannel implements NotificationChannelInterface
{
    const DEFAULT_MAX_BODY_CHARS_TO_SEND = 100;
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;
    /**
     * @var string
     */
    private $url;

    public $maxBodyCharsToSend;

    public function __construct(string $url, $maxBodyChars, Client $client = null)
    {
        $this->url = $url;
        $this->maxBodyCharsToSend = $maxBodyChars;
        $this->client = $client;

        if (is_null($client)) {
            $this->client = new Client();
        }
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function send(array $data): bool
    {
        try {
            $this->client->post($this->url, $this->createMessage($data));

            return true;
        } catch (\Exception $e) {
            throw new \Exception(
                sprintf("Failed to send Slack message. %s data= %s", $e->getMessage(), print_r($data, true))
            );
        }

        return false;
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
