<?php

namespace BrighteCapital\QueueClient\notifications\Channels;

use BrighteCapital\QueueClient\notifications\messages\NotificationMessageInterface;
use GuzzleHttp\Client;

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

    public $maxCharactersToSend = 100;

    public function __construct(string $slackEndpoint, Client $client = null)
    {
        $this->client = $client;

        if (is_null($client)) {
            $this->client = new Client();
        }

        $this->slackEndpoint = $slackEndpoint;
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function send(array $data): bool
    {
        try {
            $this->client->post($this->slackEndpoint, $this->createMessage($data));

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
                $value = substr($value, 0, $this->getMaxCharactersToSend());
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
    public function getMaxCharactersToSend(): int
    {
        return $this->maxCharactersToSend;
    }

    /**
     * @param int $maxCharactersToSend
     */
    public function setMaxCharactersToSend(int $maxCharactersToSend): void
    {
        $this->maxCharactersToSend = $maxCharactersToSend;
    }
}
